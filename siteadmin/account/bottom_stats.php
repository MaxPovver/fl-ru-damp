<?php define( 'IS_SITE_ADMIN', 1 );
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/maslen.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
	session_start();
	get_uid(false);
if (!(hasPermissions('statsaccounts') || hasPermissions('tmppayments'))) { exit; }
$DB = new DB('master');
$idMonth = date('m'); //дефолтный месяц
$idYear = date('Y'); //дефотлный год

$iBarWidth = (is_numeric(InGet('y')) && !is_numeric(InGet('m'))) ? 30 : 20; //ширина ячейки
if (InGet('y') == 'all') {
    $iBarWidth = 50;
}

$iHeight = 20; //отступ снизу
$sFont = ABS_PATH.'/siteadmin/account/Aricyr.ttf';
$graphStyle = array();
$ignored_uids = "";

// Максимальная высота отдельного блока
$blockMaxHeight = 200;

function getOPProject($from_date = '2000-01-01', $to_date = 'now()', $bYear = false, $bYearAll = false, $ignore_str = '', $addit) {
    global $DB;
    $query = $select = array();
    $type       = $addit['type'];
    $is_konkurs = $addit['is_konkurs'];
    $is_bonus   = $addit['is_bonus'];
    
    if ($ignore_str) {
        //$query[] = "account.uid NOT IN ({$ignore_str})";
        $ignore_str = "INNER JOIN account ON account.id=ac.billing_id AND account.uid NOT IN ($ignore_str)";
    }
    
    if($type !== '') {
        $query[] = "pay_type = {$type}";
        $select[] = "SUM(round(p.ammount,2)) as sum, COUNT(p.*) as ammount ";
        if ($bYear) {
            $to_char = 'MM';
            if ($bYearAll) $to_char = 'YYYY';
            $select[] = "to_char(ac.op_date,'{$to_char}') as _day";
            $group    = " GROUP BY to_char(op_date,'{$to_char}') ORDER BY to_char(op_date,'{$to_char}')";
        } else {
            $select[] = "extract(day from ac.op_date) as _day";
            $group    = " GROUP BY _day ORDER BY  _day";
        }
    } else {
        if($is_bonus) {
            $select[] = " SUM(round(ac.bonus_ammount,2)) as sum, COUNT(ac.*) as ammount";
        } else {
            $select[] = " SUM(round(ac.ammount,2)) as sum, COUNT(*) as ammount";
        }
        if ($bYear) {
            $to_char = 'MM';
            if ($bYearAll) $to_char = 'YYYY';
            $select[] = "to_char(op_date,'{$to_char}') as _day";
            $group    = " GROUP BY to_char(op_date,'{$to_char}') ORDER BY to_char(op_date,'{$to_char}')";
        } else {
            $select[] = "extract(day from op_date) as _day";
            $group    = " GROUP BY _day ORDER BY  _day";
        }
    }
        
    if($is_konkurs) {
        $contestOpCodes = new_projects::getContestOpCodes();
        $contestOpCodesSql = implode(',', $contestOpCodes);
        $query[] = "ac.op_code IN ($contestOpCodesSql) ";
        if($is_bonus) {
            $query[] = "ac.bonus_ammount <> 0";
        } else {
            $query[] = "ac.bonus_ammount = 0";
        }
    } else {
        if($is_bonus) {
            $query[] = "ac.op_code = 54";
        } else {
            $query[] = "ac.op_code IN (8,53)";
         }
    }
    $select_str = implode(", ", $select);    
    $query_str = implode(" AND ", $query);
  
    if($type === '') {
        $sql = "SELECT 
                    {$select_str}
                FROM 
                    account_operations as ac 
                    INNER JOIN account ON account.id=ac.billing_id AND NOT (op_date >= '2011-01-01' AND account.uid IN (SELECT uid FROM users WHERE ignore_in_stats = TRUE))
                WHERE 
                    op_date >= '$from_date'::date AND op_date < '$to_date'::date+'1 day'::interval AND {$query_str} {$group};";   
    } else {
        $sql = "SELECT 
                    {$select_str}
                FROM projects_payments as p 
                    INNER JOIN projects prj ON prj.id = p.project_id
                    INNER JOIN account_operations as ac ON p.opid=ac.id 
                    {$ignore_str}
                    WHERE ac.op_date >= '$from_date'::date AND ac.op_date < '$to_date'::date+'1 day'::interval AND {$query_str} {$group};";
    }
    
    return $DB->rows($sql);
}

function getOP($op, $date_from='2006-10-10', $date_to='now()', $bYear=false, $addit="", $ignore_str = "", $bYearAll = FALSE) {
    global $DB;
    if ($op[0] == 23) {
        $cond = " AND ammount>=0 ";
    } else {
        $cond = "";
    }
    if (in_array($op[0], array('p0', 'p1', 'p2', 'p3'))) {
        $is_prj_addit = true;
        $bonus_str = "ac.op_code IN (8,53) AND";
        $sss = "prj.kind <> 2 AND prj.kind <> 7 AND";
        $prj_op = "pay_type IN (".str_replace('p', '', $op[0]).")";
    }
    
    if (in_array($op[0], array('p4', 'p5', 'p6', 'p7'))) {
        $is_konk = $is_prj_addit = true;
        $sss = "(prj.kind = 2 OR prj.kind = 7) AND";
        $prj_op = "pay_type IN (".(str_replace('p', '', $op[0]) - 4).")";
    }
    
    if (in_array($op[0], array('p8', 'p9', 'p10', 'p11'))) {
        $is_prj_addit = true;
        $bonus_str = "ac.op_code = 54 AND";
        $sss = "prj.kind <> 2 AND prj.kind <> 7 AND";
        if(count($op) > 1) {
            foreach($op as $k=>$v) {
                $ops[] = (str_replace('p', '', $v) - 8);
            }
            $prj_op = "pay_type IN (".(implode(',', $ops)).")";
        } else {
            $prj_op = "pay_type IN (".(str_replace('p', '', $op[0]) - 8).")";
        }
    }
    
    if ($addit)
        $addit = " AND " . $addit;
    
    $sum = $op[0] == 71 ? "SUM(trs_sum)/2" : "SUM(round(ammount,2))";
     
    // расчет по масленичной акции #0016070
    // PRO за 19FM + карусель в каталоге за 1FM
    if (is_array($op) && in_array(48, $op) && in_array(76, $op)) {
        $op[] = maslen::OP_CODE;
        $sum = "SUM(CASE WHEN op_code = ".maslen::OP_CODE." THEN -19 ELSE ammount END)";
    }
    if ($op[0] == 73 || $op[0] == 109) {
        $op[] = maslen::OP_CODE;
        $sum = "SUM(CASE WHEN op_code = ".maslen::OP_CODE." THEN -1 ELSE ammount END)";
    }
    
    $op = (is_array($op)) ? "op_code IN ('" . implode("','", $op) . "')" : "op_code = '$op'";

    if ($ignore_str) {
        $addit .= " AND account.uid NOT IN ({$ignore_str})";
    }
    
    if ($bYear) {
        $to_char = 'MM';
        if ($bYearAll) $to_char = 'YYYY';
        
        if ($is_prj_addit) {
            if ($ignore_str) {
                $ignore_str = "INNER JOIN account ON account.id=ac.billing_id AND account.uid NOT IN ($ignore_str)";
            }
            
            $sql = "SELECT SUM(round(p.ammount, 2)) as sum, COUNT(p.*) as ammount, to_char(ac.op_date,'{$to_char}') as _day FROM
                    projects_payments as p 
                    INNER JOIN projects prj ON prj.id = p.project_id
                    INNER JOIN account_operations as ac ON p.opid=ac.id 
                    {$ignore_str}
                    WHERE {$bonus_str} {$sss} ac.op_date >= '" . $date_from . "' AND ac.op_date < '" . $date_to . "' AND " . $prj_op . "  GROUP BY to_char(ac.op_date,'{$to_char}') ORDER BY to_char(ac.op_date,'{$to_char}')";
        } else {
            $sql = "SELECT " . $sum . " as sum, COUNT(*) as ammount, to_char(op_date,'{$to_char}') as _day FROM
                    account_operations INNER JOIN account ON account.id=account_operations.billing_id INNER JOIN users ON users.uid=account.uid WHERE op_date >= '" . $date_from . "' AND op_date < '" . $date_to . "' AND " . $op . $cond . $addit . "  GROUP BY to_char(op_date,'{$to_char}') ORDER BY to_char(op_date,'{$to_char}')";
        }
    } else {
        if ($is_prj_addit) {
            if ($ignore_str) {
                $ignore_str = "INNER JOIN account ON account.id=ac.billing_id AND account.uid NOT IN ($ignore_str)";
            }
            $sql = "SELECT SUM(round(p.ammount,2)) as sum, COUNT(p.*) as ammount, extract(day from ac.op_date) as _day FROM
                    projects_payments as p 
                    INNER JOIN projects prj ON prj.id = p.project_id
                    INNER JOIN account_operations as ac ON p.opid=ac.id 
                    {$ignore_str} 
                    WHERE {$bonus_str} {$sss} ac.op_date >= '" . $date_from . "' AND ac.op_date < '" . $date_to . "'::date+'1day'::interval  AND " . $prj_op . " GROUP BY _day ORDER BY  _day";
        } else {
            $sql = "SELECT " . $sum . " as sum, COUNT(*) as ammount, extract(day from op_date) as _day FROM
                    account_operations INNER JOIN account ON account.id=account_operations.billing_id INNER JOIN users ON users.uid=account.uid WHERE op_date >= '" . $date_from . "' AND op_date < '" . $date_to . "'::date+'1day'::interval  AND " . $op . $cond . $addit . " GROUP BY _day ORDER BY  _day";
        }
    }

//    echo $sql.'<br>';die();
    $res = $DB->rows($sql);
    return $res;
}

$is_number_project = array(4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28);

$bYear = false;
$bYearAll = false;
if (is_numeric(InGet('y'))) {
    if (is_numeric(InGet('m'))) {
        $date_from = InGet('y') . '-' . InGet('m') . '-1';
        $date_to = InGet('y') . '-' . InGet('m') . '-' . date('t', mktime(0, 0, 0, InGet('m') + 1, null, InGet('y')));

        $iMonth = InGet('m');
        $iYear = InGet('y');
    } else {
        $date_from = InGet('y') . '-1-1';
        $date_to = (InGet('y') + 1) . '-01-01';
        $bYear = true;
        $iMonth = $idMonth;
        $iYear = InGet('y');
    }
} elseif (InGet('y') == 'all') {
    $date_from = '2006-10-10';
    $date_to = date('Y-m-') . date('t');
    $bYearAll = $bYear = TRUE;
    $iMonth = date('m', strtotime($date_from));
    $iYear = date('Y', strtotime($date_from));
} else {
    //echo $idMonth.'<br>';
    //echo date('t',mktime(0,0,0, intval($idMonth), 1, intval($idYear)));
    $date_from = $idYear . '-' . $idMonth . '-1';
    $date_to = $idYear . '-' . $idMonth . '-' . date('t', mktime(0, 0, 0, intval($idMonth), 1, intval($idYear)));
    $iMonth = $idMonth;
    $iYear = $idYear;
}

$iMaxDays = $iMax = ($bYear) ? 12 : date('t',mktime(0,0,0, $iMonth, 1, $iYear)); //Вычисление максимального количества дней\месяцев в текущем месяце\годе
if ($bYearAll) {
    $iMaxDays = $iMax = date('Y') - $iYear +1;
}
$iFMperPX = (!$bYear)?30:(30*30); //масштаб


if (intval($iYear) >= 2011) {
    $users = new users();
    $users_ignore = $users->GetUsers("ignore_in_stats = 't'");

    if ($users_ignore) {
        foreach ($users_ignore as $row) {
            $ignored_uids[] = $row['uid'];
        }
    
        if (count($ignored_uids)) {
            $ignored_uids = implode(", ", $ignored_uids);
        }
    }
}

$op_codes_pro = array(1,2,3,4,5,6,15,48,49,50,51,76,131,132);
/*
При добавлении новых полей и измнении их порядка необходимо обновить переменную $is_number_project соотвествующими значения
*/
$graphStyle[1]['op_codes'] 	= array(23);
$graphStyle[2]['op_codes'] 	= array(72, 88, 104);
$graphStyle[3]['op_codes'] 	= array(7, 87, 103);

$graphStyle[4]['op_codes']  = array('p0');
$graphStyle[5]['op_codes']  = array('p1');
$graphStyle[6]['op_codes']  = array('p2');
$graphStyle[7]['op_codes']  = array('p3');
$graphStyle[8]['op_codes'] 	= array(9, 86);

$graphStyle[9]['op_codes']  = array('p0');
$graphStyle[10]['op_codes']  = array('p1');
$graphStyle[11]['op_codes']  = array('p2');
$graphStyle[12]['op_codes']  = array('p3');
$graphStyle[13]['op_codes'] 	= $contestOpCodes;

$graphStyle[14]['op_codes'] 	= array(21);
$graphStyle[15]['op_codes'] 	= array(20);
$graphStyle[16]['op_codes'] 	= array(19);
$graphStyle[17]['op_codes'] 	= array(10,11);
$graphStyle[18]['op_codes'] 	= array(16,17,18,34,35);

$graphStyle[19]['op_codes']  = array('p0');
$graphStyle[20]['op_codes']  = array('p1');
$graphStyle[21]['op_codes']  = array('p2');
$graphStyle[22]['op_codes']  = array('p3');
$graphStyle[23]['op_codes']  = array(8,53);

$graphStyle[24]['op_codes']  = array('p0');
$graphStyle[25]['op_codes']  = array('p1');
$graphStyle[26]['op_codes']  = array('p2');
$graphStyle[27]['op_codes']  = array('p3');
$graphStyle[28]['op_codes']  = array(54);

$graphStyle[29]['op_codes'] = array(15);
$graphStyle[30]['op_codes'] = $op_codes_pro;
$graphStyle[31]['op_codes'] = array(47);
$graphStyle[32]['op_codes'] = array(94);
$graphStyle[33]['op_codes'] = array(107);
$graphStyle[34]['op_codes'] = array(61,62);
$graphStyle[35]['op_codes'] = array(55,65,69);
$graphStyle[36]['op_codes'] = array(70);
$graphStyle[37]['op_codes'] = array(71);
$graphStyle[38]['op_codes'] = array(73, 109, 111);
$graphStyle[39]['op_codes'] = array(80);
$graphStyle[40]['op_codes'] = array(74);
$graphStyle[41]['op_codes'] = array(75);
$graphStyle[42]['op_codes'] = array(82);
$graphStyle[43]['op_codes'] = '';
//$graphStyle[44]['op_codes'] = '';
//$graphStyle[45]['op_codes'] = '';
//$graphStyle[46]['op_codes'] = '';
$graphStyle[44]['op_codes'] = array(45);
$graphStyle[45]['op_codes'] = array(117);

$graphStyle[1]['addit'] = '';
$graphStyle[2]['addit'] = '';
$graphStyle[3]['addit'] = '';

$graphStyle[4]['addit'] = array('type' => 0, 'is_konkurs' => true, 'is_bonus' => false);
$graphStyle[5]['addit'] = array('type' => 1, 'is_konkurs' => true, 'is_bonus' => false);
$graphStyle[6]['addit'] = array('type' => 2, 'is_konkurs' => true, 'is_bonus' => false);
$graphStyle[7]['addit'] = array('type' => 3, 'is_konkurs' => true, 'is_bonus' => false);
$graphStyle[8]['addit'] = array('type' => '','is_konkurs' => true, 'is_bonus' => false);

$graphStyle[9]['addit'] = array('type' => 0, 'is_konkurs' => true, 'is_bonus' => true);
$graphStyle[10]['addit'] = array('type' => 1, 'is_konkurs' => true, 'is_bonus' => true);
$graphStyle[11]['addit'] = array('type' => 2, 'is_konkurs' => true, 'is_bonus' => true);
$graphStyle[12]['addit'] = array('type' => 3, 'is_konkurs' => true, 'is_bonus' => true);
$graphStyle[13]['addit'] = array('type' => '','is_konkurs' => true, 'is_bonus' => true);

$graphStyle[14]['addit'] = '';
$graphStyle[15]['addit'] = '';
$graphStyle[16]['addit'] = '';
$graphStyle[17]['addit'] = '';
$graphStyle[18]['addit'] = '';

$graphStyle[19]['addit'] = array('type' => 0, 'is_konkurs' => false, 'is_bonus' => false);
$graphStyle[20]['addit'] = array('type' => 1, 'is_konkurs' => false, 'is_bonus' => false);
$graphStyle[21]['addit'] = array('type' => 2, 'is_konkurs' => false, 'is_bonus' => false);
$graphStyle[22]['addit'] = array('type' => 3, 'is_konkurs' => false, 'is_bonus' => false);
$graphStyle[23]['addit'] = array('type' => '', 'is_konkurs' => false, 'is_bonus' => false);

$graphStyle[24]['addit'] = array('type' => 0, 'is_konkurs' => false, 'is_bonus' => true);
$graphStyle[25]['addit'] = array('type' => 1, 'is_konkurs' => false, 'is_bonus' => true);
$graphStyle[26]['addit'] = array('type' => 2, 'is_konkurs' => false, 'is_bonus' => true);
$graphStyle[27]['addit'] = array('type' => 3, 'is_konkurs' => false, 'is_bonus' => true);
$graphStyle[28]['addit'] = array('type' => '', 'is_konkurs' => false, 'is_bonus' => true);

$graphStyle[29]['addit'] = "role&'".$empmask."' = '".$empmask."'";
$graphStyle[30]['addit'] = "role&'".$empmask."' = '".$frlmask."'";
$graphStyle[31]['addit'] = '';
$graphStyle[32]['addit'] = '';
$graphStyle[33]['addit'] = '';
$graphStyle[34]['addit'] = '';
$graphStyle[35]['addit'] = '';
$graphStyle[36]['addit'] 	= '';
$graphStyle[37]['addit'] 	= '';
$graphStyle[38]['addit'] 	= '';
$graphStyle[39]['addit'] 	= '';
$graphStyle[40]['addit'] 	= '';
$graphStyle[41]['addit'] 	= '';
$graphStyle[42]['addit'] 	= '';
$graphStyle[43]['addit'] 	= '';
//$graphStyle[44]['addit'] 	= '';
//$graphStyle[45]['addit'] 	= '';
//$graphStyle[46]['addit'] 	= '';
$graphStyle[44]['addit'] 	= 'account_operations.id IN (SELECT account_op_id FROM mass_sending WHERE is_accepted = TRUE)';
$graphStyle[45]['addit'] 	= '';

// индексы $graphStyle которы не должны учитываться при подсчете общей суммы
// помимо них игнорируются элементы с p0, p1, p2, p3 (см.ниже в коде)
$graphStyleSummIgnor = array(1, 46);

for ($i=1; $i<=count($graphStyle); $i++) {
	for ($j=0; $j<= $iMaxDays; $j++) {
		$graphValues[$i][$j] = 0;
		$graphValues2[$i][$j] = 0;
	}
}

$graphLabels = array();
for ($j = 1; $j <= $iMaxDays; $j++) {
    $graphLabels[] = $j;
}

if ($bYearAll) {
    $graphValues = $graphValues2 = array();

    $graphLabels = array();
    for ($j = 0; $j <= $iMaxDays; $j++) {
        $graphLabels[] = ($iYear + $j);
    }
    
    for ($i = 1; $i <= count($graphStyle); $i++) {
        foreach ($graphLabels as $j => $yr) {
            $graphValues[$i][$j] = 0;
            $graphValues2[$i][$j] = 0;
        }
    }
}

//// для теста
//foreach ($graphValues as $j => $row) {
//    foreach ($row as $i => $row1) {
//        $rr = 5350/ (count($row)-(count($row)-$i-1));
//        $graphValues[$j][$i] = rand($rr, 150)/$iFMperPX;
//    }
//}
//$graphValues2 = $graphValues;
/*
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");

$sbr = new sbr_adm(get_uid(false), $_SESSION['login']);
$filter_fdate = preg_split("/-/", $date_from);
$filter_tdate = preg_split("/-/", $date_to);
$filter = array(
    'from' => array(
        'day'   => 1,
        'month' => intval($filter_fdate[1]),
        'year'  => $filter_fdate[0]
    ),
    'to' => array(
        'day' => ($bYear || $bYearAll)? 31: $iMaxDays,
        'month' => ($bYear || $bYearAll)? 12: intval($filter_tdate[1]),
        'year' => $filter_tdate[0] - (int) ($bYear && !$bYearAll)  // для графиков СБР нужен месяц "включая" последний день
    )
);
$sbr_stats = $sbr->getStatsByDay($filter, TRUE, $bYearAll ? 'year' : ($bYear? 'month': 'day'));
*/
$imgHeight = 0;
// список для СБР тип графика => номер в массиве полученной статистике (из sbr_adm::getStatsByDay)
//$graphTypesMap = array(44 => 4, 45 => 5, 46 => 0);

for ($i=1; $i<=count($graphStyle); $i++) {
    
    // подсчет для СБР (зарезервировано, выплачено работодателям, выплачено фрилансерам)
    /*if (in_array($i, array_keys($graphTypesMap))) {
        $res  = array();
        $res1 = array();
        $sbr_stats_datas = $sbr_stats[$graphTypesMap[$i]]['graphs']? $sbr_stats[$graphTypesMap[$i]]['graphs']: array();
        foreach ( $sbr_stats_datas as $st ) {
            if ( $sbr_stats_datas ) {
                if ( $bYearAll ) {
                    $stl = &$st['days'];
                    $dd  = 'Y';
                } else if ( $bYear ) {
                    $stl = &$st['days'][$filter['from']['year']];
                    $dd  = 'm';
                } else {
                    $stl = &$st['days'][$filter['from']['year']][$filter['from']['month']];
                    $dd  = 'd';
                }
                foreach ( $stl as $k => $v ) {
                    if( $v['fm_sum'] != 0 && $v['cnt'] != 0 ) {
                        $day = intval(date($dd, strtotime($v['day'])));
                        $k = array_search($day, $graphLabels);
                        if ( !isset($res1[$k]) ) {
                            $res1[$k] = array('sum'=>round($v['fm_sum'], 2), 'ammount'=>$v['cnt'], '_day'=>$day);
                        } else {
                            $res1[$k]['sum'] = $res1[$k]['sum'] + round($v['fm_sum'],2); 
                            $res1[$k]['ammount'] = $res1[$k]['ammount'] + $v['cnt'];
                        }
                    }
                }
            }
        }
        if ( $res1 ) {
            ksort($res1);
            foreach ( $res1 as $d ) {
                array_push($res, $d);
            }
        }
    }*/
    
    if($i == 43) {
        $res = pf::getOp($date_from, $date_to, $bYear, $bYearAll);
        foreach ($res as $kRes=>$iRes) {
            $res[$kRes]['sum'] = round($iRes['sum'] / EXCH_TR, 2); 
        } 
    }
     
    if($i!=43 && !in_array($i, $is_number_project)) {
	    $res = getOP($graphStyle[$i]['op_codes'], $date_from, $date_to, $bYear, $graphStyle[$i]['addit'], $ignored_uids, $bYearAll);
    }
    if(in_array($i, $is_number_project)) {
        $res = getOPProject($date_from, $date_to, $bYear, $bYearAll, $ignored_uids, $graphStyle[$i]['addit']);
    }
	$aTemp = $res;

 if ($bYearAll) {
     for ($ii = 0; $ii < count($aTemp); $ii++) {
         $aTemp[$ii]['_day'] = array_search($aTemp[$ii]['_day'], $graphLabels)+1;
     }
 }

    if (isset($aTemp[0]['_day'])) {
        
		for ($j=0; $j<count($aTemp); $j++) {
			$iAmount = abs($aTemp[$j]['sum']/$iFMperPX);
            
            $graphValues[$i][$aTemp[$j]['_day']-1] = $iAmount;
			$graphValues2[$i][$aTemp[$j]['_day']-1] = $aTemp[$j]['ammount'];
            if($aTemp[$j]['sum']<0) {
			    $graphValues3[$i][$aTemp[$j]['_day']-1] += -1*$aTemp[$j]['sum'];
            } else {
			    $graphValues3[$i][$aTemp[$j]['_day']-1] += $aTemp[$j]['sum'];
            }
        }
    }
}

$k = 0; $graphStyle[0]['max'] = 0;
for ($i=0; $i<=$iMaxDays; $i++) {
	$iSumm = 0; $iSumm2 = 0;
	for ($j=2; $j<count($graphValues); $j++) { //2 потому что переводы не считаем
		if (isset($graphValues[$j][$i]) && !in_array($graphStyle[$j]['op_codes'][0], array('p0','p1','p2','p3')) && !in_array($j, $graphStyleSummIgnor)) { // Резерв заключенных не суммируем #0014833
			$iSumm += $graphValues[$j][$i];
        }

		if (isset($graphValues2[$j][$i])) {
			$iSumm2 += $graphValues2[$j][$i];
        }
    }

	for ($j=2; $j<count($graphValues2); $j++) {
		if (isset($graphValues2[$j][$i])) {
			$iSumm2 += $graphValues2[$j][$i];
        }
    }

    $graphValues[0][$k] = $iSumm;
	$graphValues2[0][$k] = $iSumm*$iFMperPX;
    if ($iSumm > $graphStyle[0]['max'])
        $graphStyle[0]['max'] = $iSumm;
    $k++;
}

// максимальное значение в строке
foreach ($graphValues as $row => $cols) {
    $graphStyle[$row]['max'] = $cols[0];
    foreach ($cols as $col) {
        if ($col > $graphStyle[$row]['max']) {
            $graphStyle[$row]['max'] = $col;
        }
    }
}

$tmp = array();
$mpl = 1;
foreach ($graphStyle as $k => $v) {
    if($k == 0 && $bYear) continue;
    $tmp[] = $v['max'];
}
if (count($tmp)) {
    rsort($tmp);
    $max_h = $tmp[0];
    $mpl = $blockMaxHeight/$max_h;
}
$mpl = $mpl > 1 ? 1 : $mpl;
if($bYear) {
    $bmpl = $blockMaxHeight/$graphStyle[0]['max'];
    $bmpl = $bmpl > 1 ? 1: $bmpl;
} else {
    $bmpl = $mpl;
}

foreach ($graphStyle as $k => $v) {
    if($k == 0) {
        $graphStyle[$k]['max'] = $graphStyle[$k]['max'] * $bmpl;    
    } else {
        $graphStyle[$k]['max'] = $graphStyle[$k]['max'] * $mpl;
    }
    $imgHeight += $graphStyle[$k]['max'];
}
//echo '<pre>'; print_r($graphValues); echo '</pre>';
$imgHeight += count($graphValues)*27; //прибавляем промежутки к максимальной высоте графика
$imgWidth = $iMax*$iBarWidth+100;


$image=imagecreate($imgWidth, $imgHeight); //создаем график с учетом максимальной высоты и ширины.
imagecolorallocate($image, 255, 255, 255);

$graphStyle[0]['color'] = imagecolorallocate($image, 0, 0, 0); //Сумма
$graphStyle[1]['color'] = imagecolorallocate($image, 103, 135, 179); //Перевели денег
$graphStyle[2]['color'] = imagecolorallocate($image, 111, 177, 92); //Подняли конкурс
$graphStyle[3]['color'] = imagecolorallocate($image, 111, 177, 92); //Подняли проект

$graphStyle[4]['color'] = imagecolorallocate($image, 111, 177, 92); //Конкурсы, логотип
$graphStyle[5]['color'] = imagecolorallocate($image, 111, 177, 92); //Конкурсы, подсветка фоном
$graphStyle[6]['color'] = imagecolorallocate($image, 111, 177, 92); //Конкурсы, жирный шрифт
$graphStyle[7]['color'] = imagecolorallocate($image, 111, 177, 92); //Конкурсы, закрепление наверху
$graphStyle[8]['color'] = imagecolorallocate($image, 111, 177, 92); //Конкурсы

$graphStyle[9]['color'] = imagecolorallocate($image, 111, 177, 92); //Конкурсы, логотип
$graphStyle[10]['color'] = imagecolorallocate($image, 111, 177, 92); //Конкурсы, подсветка фоном
$graphStyle[11]['color'] = imagecolorallocate($image, 111, 177, 92); //Конкурсы, жирный шрифт
$graphStyle[12]['color'] = imagecolorallocate($image, 111, 177, 92); //Конкурсы, закрепление наверху
$graphStyle[13]['color'] = imagecolorallocate($image, 111, 177, 92); //Конкурсы

$graphStyle[14]['color'] = imagecolorallocate($image, 140, 140, 140); //Перемешения
$graphStyle[15]['color'] = imagecolorallocate($image, 140, 140, 140); //Места внутри кат.
$graphStyle[16]['color'] = imagecolorallocate($image, 140, 140, 140); //Места в каталоге
$graphStyle[17]['color'] = imagecolorallocate($image, 140, 140, 140); //Места на первой
$graphStyle[18]['color'] = imagecolorallocate($image, 103, 135, 179); //Подарки
$graphStyle[19]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, логотип
$graphStyle[20]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, подсветка фоном
$graphStyle[21]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, жирный шрифт
$graphStyle[22]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, закрепление наверху
$graphStyle[23]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты
$graphStyle[24]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, логотип
$graphStyle[25]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, подсветка фоном
$graphStyle[26]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, жирный шрифт
$graphStyle[27]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, закрепление наверху
$graphStyle[28]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты
$graphStyle[29]['color'] = imagecolorallocate($image, 0, 103, 56); //PRO работодатели
$graphStyle[30]['color'] = imagecolorallocate($image, 179, 36, 36); //PRO
$graphStyle[31]['color'] = imagecolorallocate($image, 247, 128, 90); //PRO тестовое
$graphStyle[32]['color'] = imagecolorallocate($image, 111, 177, 92); //Сервис "Сделаю"
$graphStyle[33]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные рекомендации
$graphStyle[34]['color'] = imagecolorallocate($image, 147, 128, 90); //Ответы на проекты
$graphStyle[35]['color'] = imagecolorallocate($image, 90, 60, 90); //Карусел
$graphStyle[36]['color'] = imagecolorallocate($image, 60, 90, 60); //Смена логина
$graphStyle[37]['color'] = imagecolorallocate($image, 147, 128, 90); //Восстановление пароля
$graphStyle[38]['color'] = imagecolorallocate($image, 90, 60, 90); //Карусел в каталоге
$graphStyle[39]['color'] = imagecolorallocate($image, 90, 60, 90); //Дополнительные специализации
$graphStyle[40]['color'] = imagecolorallocate($image, 90, 60, 90); //Платная разблокировка
$graphStyle[41]['color'] = imagecolorallocate($image, 90, 60, 90); // Поднятие рейтинга
$graphStyle[42]['color'] = imagecolorallocate($image, 90, 60, 90); // Поднятие рейтинга
$graphStyle[43]['color'] = imagecolorallocate($image, 90, 60, 90); // Комиссия СБР фрилансер
//$graphStyle[44]['color'] = imagecolorallocate($image, 90, 60, 90); // Комиссия СБР работадатель
//$graphStyle[45]['color'] = imagecolorallocate($image, 90, 60, 90); // Комиссия СБР
//$graphStyle[46]['color'] = imagecolorallocate($image, 90, 60, 90); // Комиссия СБР
$graphStyle[44]['color'] = imagecolorallocate($image, 111, 177, 92); // Платные рассылки
$graphStyle[45]['color'] = imagecolorallocate($image, 111, 177, 92); // Верификация FF

$graphStyle[0]['text'] 	= 'Сумма';
$graphStyle[1]['text'] 	= 'Перевели денег';
$graphStyle[2]['text']  = 'Подняли конкурс';
$graphStyle[3]['text'] 	= 'Подняли проект';

$graphStyle[4]['text'] 	= '- логотип';
$graphStyle[5]['text'] = '- фон';
$graphStyle[6]['text'] = '- шрифт';
$graphStyle[7]['text'] = '- закрепление';
$graphStyle[8]['text'] 	= 'Конкурсы';

$graphStyle[9]['text'] 	= '- логотип (б)';
$graphStyle[10]['text'] = '- фон (б)';
$graphStyle[11]['text'] = '- шрифт (б)';
$graphStyle[12]['text'] = '- закрепление (б)';
$graphStyle[13]['text'] 	= 'Конкурсы (б)';

$graphStyle[14]['text'] 	= 'Перемешения';
$graphStyle[15]['text'] 	= 'Места внутри кат.';
$graphStyle[16]['text'] 	= 'Места в каталоге';
$graphStyle[17]['text'] 	= 'Места на первой';
$graphStyle[18]['text'] 	= 'Подарки';
$graphStyle[19]['text'] 	= '- логотип';
$graphStyle[20]['text'] = '- фон';
$graphStyle[21]['text'] = '- шрифт';
$graphStyle[22]['text'] = '- закрепление';
$graphStyle[23]['text'] 	= 'Платные проекты';
$graphStyle[24]['text'] 	= '- логотип (б)';
$graphStyle[25]['text'] = '- фон (б)';
$graphStyle[26]['text'] = '- шрифт (б)';
$graphStyle[27]['text'] = '- закрепление (б)';
$graphStyle[28]['text'] 	= 'Платные пр-ты (б)';
$graphStyle[29]['text'] = 'PRO р-тель';
$graphStyle[30]['text'] = 'PRO фрилансер';
$graphStyle[31]['text'] = 'PRO тест';
$graphStyle[32]['text'] = 'Сервис «Сделаю»';// Платные рекомендации
$graphStyle[33]['text'] = 'Рекомендации';// Платные рекомендации
$graphStyle[34]['text'] = 'Ответы на проекты'; 
$graphStyle[35]['text'] = 'Карусель';
$graphStyle[36]['text'] = 'Смена логина';
$graphStyle[37]['text'] = 'Восстанов. пароля';
$graphStyle[38]['text'] = 'Карусел кат';
$graphStyle[39]['text'] = 'Доп. спец.';
$graphStyle[40]['text'] = 'Пл. разблокировка';
$graphStyle[41]['text'] = 'Подн. рейтинга';
$graphStyle[42]['text'] = 'Лич. менеджер';
$graphStyle[43]['text'] = 'Подбор фр-ов(бн)';
//$graphStyle[44]['text'] = 'Комиссия фри-л.';
//$graphStyle[45]['text'] = 'Комиссия раб-тели';
//$graphStyle[46]['text'] = 'Резерв заключенн.';
$graphStyle[44]['text'] = 'Платн. рассылки';
$graphStyle[45]['text'] = 'Верификация FF';



$colorWhite=imagecolorallocate($image, 255, 255, 255);
$colorGrey=imagecolorallocate($image, 192, 192, 192);
$colorDarkBlue=imagecolorallocate($image, 153, 153, 153);


for ($i=0; $i<count($graphValues); $i++) {
	//вычисляем откуда начать прорисовку графика
	if ($i) {
		$iMaxHeight = $graphValues[$i-1][0];
		for ($k=1; $k<count($graphValues[$i-1]); $k++) {
			$iMaxHeight = ($graphValues[$i-1][$k] > $iMaxHeight)?$graphValues[$i-1][$k]:$iMaxHeight;
		}
		$iHeight += $iMaxHeight*($i==1?$bmpl:$mpl)+25; // +15 - расстояние между строчками
		
	}
	for ($j=0; $j<count($graphValues[$i]); $j++) {

		imageline($image, $j*$iBarWidth+2 + 100, $imgHeight-$iHeight, $j*$iBarWidth+$iBarWidth + 100, $imgHeight-$iHeight, $colorGrey);
		if (!$i) {
			$iz = ($j+1 > 9)?3.7:2.5;
			imagefttext($image, '7', 0, $j*$iBarWidth+round($iBarWidth/$iz) + 100, $imgHeight - 5, $colorDarkBlue, $sFont, $graphLabels[$j]);
		}
        
        if ($graphValues2[$i][$j]) {
			imagefilledrectangle($image, $j*$iBarWidth+2 + 100, ($imgHeight-$iHeight-round($graphValues[$i][$j]*($i==0?$bmpl:$mpl))), ($j+1)*$iBarWidth + 100, $imgHeight-$iHeight, $graphStyle[$i]['color']);
			//надпись количества FM
			$addD = ($i == 18)?2:1; ///Если подарки, то результат делим на 2
            $color = (!$i)?$graphStyle[$i]['color']:$colorDarkBlue;
            if($i!=0) {
                imagefttext($image, '7', 0, $j*$iBarWidth + 100+2, $imgHeight-$iHeight-($graphValues[$i][$j]*($i==0?$bmpl:$mpl))-12, $color, $sFont, round($graphValues2[$i][$j]/$addD)."\n".$graphValues3[$i][$j]);
            } else {
                $iCount = 0;
                for($k=1; $k<count($graphValues2); $k++) {
                    $addD = ($k == 18)?2:1;
                    if(!in_array($graphStyle[$k]['op_codes'][0],array('p0','p1','p2','p3')) && !in_array($k, $graphStyleSummIgnor)) {
                        $iCount += round($graphValues2[$k][$j]/$addD, 1);
                    }
                }
                imagefttext($image, '7', 0, $j*$iBarWidth + 100+2, $imgHeight-$iHeight-($graphValues[$i][$j]*($i==0?$bmpl:$mpl))-12, $color, $sFont, round($graphValues2[$i][$j]/$addD, 1)."\n".$iCount);
            }
		}
	}
	$iFontSizeTitle = 8;
	$aBox = imageftbbox($iFontSizeTitle, 0, $sFont,$graphStyle[$i]['text']);
	$width = abs($aBox[0]) + abs($aBox[2]);
    imagefttext($image, $iFontSizeTitle, 0, 92-$width, $imgHeight-$iHeight, $graphStyle[$i]['color'], $sFont, $graphStyle[$i]['text']);
}


$aMonthes[1] = 'Январь';
$aMonthes[2] = 'Февраль';
$aMonthes[3] = 'Март';
$aMonthes[4] = 'Апрель';
$aMonthes[5] = 'Май';
$aMonthes[6] = 'Июнь';
$aMonthes[7] = 'Июль';
$aMonthes[8] = 'Август';
$aMonthes[9] = 'Сентябрь';
$aMonthes[10] = 'Октябрь';
$aMonthes[11] = 'Ноябрь';
$aMonthes[12] = 'Декабрь';

$sString = 'Продажи сервисов';
imagefttext($image, '18', 0, 100, 20, $colorGrey, $sFont, $sString);

/*header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sun, 1 Jan 1995 01:00:00 GMT"); // Это какая-нибудь давно прошедшая дата
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // это строчка говорит, что наш скрипт всегда изменен
header("Content-type: image/png");*/
imagepng($image);
imagedestroy($image);

?>

