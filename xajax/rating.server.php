<?

$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/rating.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/promotion.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

function GetMoreSBR($uid, $i) {
    session_start();
    $objResponse = new xajaxResponse();
    $html = '';
    $user = new users();
    $user->GetUserByUID($uid);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
    $MONTHA = $GLOBALS['MONTHA'];
    if($i > 5 || $i <=0) $i = 5;
    $sbr_ratings = sbr_meta::getUserRatings($user->uid, is_emp($user->role), 'ALL', 5);
    ob_start();
    include ($_SERVER['DOCUMENT_ROOT'] ."/user/tpl.rating-sbr.php");
    $html = ob_get_contents();
    ob_end_clean();
    
    $objResponse->assign("more_sbr_content", "innerHTML", $html);

    return $objResponse;
}

function GetMorePrj($uid) {
    session_start();
    $objResponse = new xajaxResponse();
    $html = '';
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
    $prjs = projects_offers::GetFrlOffers($uid, 'marked', NULL);
    $i=0;
    
    $uid = get_uid(FALSE);
    $is_adm = hasPermissions('users');
    
    if($prjs) {
        foreach($prjs as $p) { 
            
            $is_link = (($uid > 0) && (in_array($uid, array($p['exec_id'],$p['project_user_id'],$p['offer_user_id'])) || $is_adm));

            $i++;
            $html .= "<li><span class='prj_list_number'>{$i}.</span>";
            
            if($p['kind'] == 9) $html .= ($is_link)?"<a href='".getFriendlyURL("project", $p['project_id'])."'>{$p['project_name']}</a>":"{$p['project_name']}";
            else $html .= "<a href='".getFriendlyURL("project", $p['project_id'])."'>{$p['project_name']}</a>";
            
            if($p['position']>0 && $p['is_executor']=='t') { 
                //$html .= " ({$p['position']}-е место)";
            } 
            if($p['refused']=='t') {
                $html .= "<p>Отказ: <span class='ops-minus'>".$p['rating']."</span></p>";
            } if($p['selected']=='t') {
                $html .= "<p><span>Кандидат: <span class='ops-plus'>+".$p['rating']."</span></p>";
            } if($p['is_executor']=='t' && $p['position'] <= 0) {
                $html .= "<p><span>Исполнитель: <span class='ops-plus'>+".$p['rating']."</span></p>";
            } if($p['position'] > 0) {  
                $html .= "<p>{$p['position']}-е место: <span class='ops-plus'>+{$p['rating']}</span></p>";
            }
            $html .= "</li>";
        }
        $objResponse->assign("prj_list", "innerHTML", $html);
    }
    return $objResponse;
}


function GetRating($type, $login = null, $width = null) {
    session_start();
    $objResponse = new xajaxResponse();

    $login = change_q_x($login, TRUE);
    $user = new users();
    $user->GetUser($login);
    $uid = $user->uid;

    if (!$uid) {
        $uid = get_uid(false);
    }

    if (!$uid) {
        $objResponse->script("this.document.location.reload();");
        return $objResponse;
    }
    $user->GetUserByUID($uid);

    $rating = new rating();

    switch ($type) {
        case 'year':
            $TIME = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $pro_periods_date = date('Y-01-01', $TIME);

            $res = $rating->getRatingByYear($uid, date('Y', $TIME));
            $periods = rating::getMonthParts(date('Y-01-01'));

            $data = array();
            if ($res) {
                foreach($periods as $m => $mm) {
                    if($m < date('m'))
                    foreach($mm as $d) {
                        if($d > time() || $d < strtotime($user->reg_date) ) continue;
                        $data[$m][date('Y-m-d', $d)] = null;

                        if($d >= strtotime($user->reg_date)) {
                            $data[$m][date('Y-m-d', $d)] = 0;
                        }
                    }
                }
            }
            
            $start_r = null;
            if($res) {
                if (date('Y', strtotime($res[0]['_date'])) == date('Y')-1) {
                    $start_r = $res[0]['rating'];
                    if(isset($res[1]) && strtotime($res[1]['_date']) != $periods[0][0]) {
                        $res[0]['_date'] = date('Y-m-d', $periods[0][0]);
                    } else {
                        $res = array_slice($res, 1);
                    }
                }
            } else {
                $res = array(); 
            }
            
            
            $verify_factor = 0;
            $verify_date = rating::GetVerifyDate($uid);
            
            foreach($res as $row) {
                $t = strtotime($row['_date']);
                $m = (int)date('m', $t);

                $verify_factor = 0;
                if ( $row['is_verify'] == 't' ) {
                    if ( $verify_date ) {
                        if ( strtotime($verify_date) < $t ) {
                            $verify_factor = 0.2;
	                    }
                    } else {
                        $verify_factor = 0.2;
                    }
                }
                
                $data[$m-1][date('Y-m-d', $t)] = array(
                    'rating' => floatval($row['rating']),
                    'verify' => floatval($row['rating'] * $verify_factor),
                    'pro' => 0
                );
            }
            
            $lastval = null;
            foreach($data as $i => $mon) {
                foreach($mon as $d => $prt) {
                    $vl = !$prt ? $lastval : $prt;
                    $data[$i][$d] = $vl;
//                    if($prt !== null)
                        $lastval = $vl;
                }
            }

            $pro_periods = promotion::GetUserProPeriods($uid, $pro_periods_date, TRUE);

            if($pro_periods) {
                $pro = array();
                foreach($pro_periods as $p => $period) {
                    if(date('Y', strtotime($period['from_time'])) > date('Y', $TIME)
                        && date('Y', strtotime($period['to_time']) > date('Y', $TIME))) continue;

                    $d1 = (int)date('z', strtotime($period['from_time']));
                    $d2 = (int)date('z', strtotime($period['to_time']));

                    if(date('Y', strtotime($period['from_time'])) < date('Y', $TIME)) $d1 = 0;
                    if(date('Y', strtotime($period['to_time'])) > date('Y', $TIME)) $d2 = (int)date('z',mktime(0,0,0,12,31,date('Y')));

                    $_factor = 0.2;
                    if($period['is_profi'] == 1) {
                        $_factor = 0.4;
                    }
                    
                    foreach($data as $mon => $val) {
                        foreach($val as $per => $r) {
                            $day = (int)date('z', strtotime($per));
                            if($d1 < $day && $d2 >= $day) {
                                $data[$mon][$per]['pro'] = floatval($data[$mon][$per]['rating'] * $_factor);
                            }
                        }
                    }
                    
                    $pro[$p] = $d1 <> $d2 ? array($d1, $d2) : array($d1);
                }
                $config['pro'] = $pro;
            }

            $new_data = array();
            if($data) {
                foreach($data as $mon => $val) {
                    foreach($val as $per => $r) {
                        $new_data[$mon][$per] = $r['rating'] + $r['verify'] + $r['pro'];
                    }
                }
            }
            
            $config['data'] = $new_data;
            $config['cur'] = intval(date('m', $TIME));
            $config['days'] = date('z',mktime(0,0,0,12,31,date('Y')))+1;
            $config['regdate'] = $user->reg_date;

            break;
        case 'prev':
        default:
            $config = array();
            $data = array();

            $config['hilight'] = array();
            
            if($type == 'prev') {
                $TIME = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
                $res = $rating->getRatingByMonth($uid, date('Y-m-d', $TIME));
                if(!$res) $res = array();

                $graphstart = strtotime($user->reg_date);
                if (count($res) && date('Ym', strtotime($res[0]['_date'])) == date('Ym', $TIME)) {
                    $graphstart = strtotime($res[0]['_date']);
                } else if (!count($res)) {
                    $graphstart = time();
                }

                $rating_data = array();
                $verify_date = null;
                $verify_factor = 0;
                $n = 0;
                foreach($res as $d) {
                    
                    if(date('Y-m', strtotime($d['_date'])) != date('Y-m', $TIME)) continue;
                    //$rating_data[intval(date('d', strtotime($d['_date'])))] = $d['rating'];
                    
                    $verify_factor = 0;
                    if ( $verify_date === null ) {
                        $verify_date = rating::GetVerifyDate($d['user_id']);
                    }
                                        
                    if ( $d["is_verify"] == 't' ) {
                        if ( $verify_date ) {
                            if ( strtotime($verify_date) < strtotime($d['_date']) ) {
                                $verify_factor = 0.2;
                            }
                        }else {
                            $verify_factor = 0.2;
                        }
                    }
                    
                    if ($n == 0) {
                        $res[0]['verify_factor'] = $verify_factor;
                    }
                    
                    $rating_data[intval(date('d', strtotime($d['_date'])))] = array(
                        'rating' => floatval($d['rating']),
                        'verify' => floatval($d['rating'] * $verify_factor),
                        'pro' => 0
                    );
                    
                    $n++;
                }

                $last = null;
                for($i = 0; $i < date('t', $TIME); $i++) {
                    
                    if(strtotime(date("Y-m-".($i+1), $TIME)) < $graphstart) {
                        $last = null;
                    } else {
                        $last = $last !== null ? $last : 0;
                        if($i == 0 && !isset($rating_data[$i+1])) {
                            $last = array(
                                'rating' => floatval($res[0]['rating']),
                                'verify' => floatval($res[0]['rating'] * $res[0]['verify_factor']),
                                'pro' => 0
                            );
                        }
                    }
                    
                    if(isset($rating_data[$i+1])) {
                        $last = $rating_data[$i+1];
                    }
                    
                    $data[$i] = $last;
                }
                
            } else {
                
                $TIME = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $res = $rating->getRatingByMonth($uid, date('Y-m-d', $TIME));
                if(!$res) $res = array();

                $graphstart = strtotime($user->reg_date);
                if (count($res) && date('Ym', strtotime($res[0]['_date'])) == date('Ym', $TIME)) {
                    $graphstart = strtotime($res[0]['_date']);
                }

                $rating_data = array();
                $verify_date = null;
                $verify_factor = 0;
                $n = 0;
                foreach($res as $d) {
                    
                    if(date('Y-m', strtotime($d['_date'])) != date('Y-m', $TIME)) continue;
                    
                    $verify_factor = 0;
                    if ( $verify_date === null ) {
                        $verify_date = rating::GetVerifyDate($d['user_id']);
                    }
                                        
                    if ( $d['is_verify'] == 't' ) {
                    	if ( $verify_date ) {
	                        if ( strtotime($verify_date) < strtotime($d['_date']) ) {
	                            $verify_factor = 0.2;
	                        }
                        }else {
                            $verify_factor = 0.2;
                        }
                    }
                    
                    if ($n == 0) {
                        $res[0]['verify_factor'] = $verify_factor;
                    }
                    
                    $rating_data[intval(date('d', strtotime($d['_date'])))] = array(
                        'rating' => floatval($d['rating']),
                        'verify' => floatval($d['rating'] * $verify_factor),
                        'pro' => 0
                    );
                    
                    $n++;
                }

                $config['cur'] = intval(date('d', $TIME));

                $last = 0;
                for($i = 0; $i < date('d', $TIME); $i++) {
                    
                    if(strtotime(date("Y-m-".($i+1), $TIME)) < $graphstart) {
                        $last = null;
                    } else {
                        $last = $last !== null ? $last : 0;
                        
                        if($i == 0 && !isset($rating_data[$i+1])) {
                            $last = array(
                                'rating' => floatval($res[0]['rating']),
                                'verify' => floatval($res[0]['rating'] * $res[0]['verify_factor']),
                                'pro' => 0
                            );
                        }
                    }

                    if(isset($rating_data[$i+1])) {
                        $last = $rating_data[$i+1];
                    }
                    
                    $data[$i] = $last;
                }
            }

            $pro_periods_date = date('Y-01-01', $TIME);

            for($i = 1; $i <= date('t', $TIME); $i++) {
                $t = mktime(0, 0, 0, date('m', $TIME), $i, date('Y', $TIME));
                if(date('w', $t) == 0 || date('w', $t) == 6) $config['hilight'][] = $i;
            }

            $pro_periods = promotion::GetUserProPeriods($uid, $pro_periods_date, TRUE);

            if($pro_periods) {
                $pro = array();
                $tmp = array();
                foreach($pro_periods as $p => $period) {
                    if(date('Ym', strtotime($period['from_time'])) > date('Ym', $TIME)) continue;
                    
                    if (date('Ym', strtotime($period['to_time'])) < date('Ym', $TIME)) {
                        continue;
                    }
                    
                    $d1 = (int)date('d', strtotime($period['from_time']));
                    $d2 = (int)date('d', strtotime($period['to_time']));

                    if(date('Ym', strtotime($period['from_time'])) < date('Ym', $TIME)) $d1 = 1;
                    if(date('Ym', strtotime($period['to_time'])) > date('Ym', $TIME)) $d2 = (int)date('t', $TIME);

                    $_factor = 0.2;//PRO ONLY
                    if($period['is_profi'] == 1) {
                        $_factor = 0.4;//PROFI
                    }
                    
                    foreach ($data as $day => $val) {
                        if(isset($tmp[$day]) || $val === null) continue;
                        
                        if($d1 <= $day+1 && $d2 >= $day+1) {
                            $data[$day]['pro'] = floatval($data[$day]['rating'] * $_factor); //rating::PRO_FACTOR;
                            $tmp[$day] = 1;
                        }
                    }

                    $pro[$p] = $d1 <> $d2 ? array($d1, $d2) : array($d1);
                }
                $config['pro'] = $pro;
            }

            if (strtotime($user->reg_date) > strtotime($pro_periods_date)) {
                $config['regdate'] = date('Y-m-d', strtotime($user->reg_date));
            }

            $new_data = array();
            if($data) {
                foreach($data as $day => $value) {
                    $new_data[$day] = $value['rating'] + $value['verify'] + $value['pro'];
                }
            }
            
            
            // сегодняшний рейтинг берем из $user - тут он актуальный и не зависит от кэша
            if ($type === 'month') {
                array_pop($new_data);
                $new_data[] = floatval($user->rating);
            }
            
            $config['data'] = $new_data;
            $config['days'] = date('t', $TIME);
            $config['startdate'] = date('Y-m-01', $TIME);
            
    }

    if($width) $config['w'] = (int)$width;

    $config = json_encode($config);
    $objResponse->script("loadGraph('$type', $config);");

    return $objResponse;
}

$xajax->processRequest();
