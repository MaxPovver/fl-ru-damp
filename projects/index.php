<?
// так же в /xajax/projects_ci.server.php
define('MAX_WORKS_IN_LIST', 30);
define('MAX_OFFERS_AT_PAGE', 50);

$rpath = "../";
$g_page_id = "0|21";
$stretch_page = true;
$showMainDiv  = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
//require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_stats.php';
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/projects_helper.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/projects_status.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_phone.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoTags.php");


session_start();
$uid = get_uid();
$section = trim($_GET['p']);
$errmsg = '';
$footer_project = true;
$is_pro = payed::CheckPro($_SESSION['login']);
$is_pro2 = payed::CheckPro($_SESSION['login']);
$is_verify = ($_SESSION['is_verify'] == 't');

#if (!$_SESSION['login']) {include ABS_PATH."/403.php"; exit;}

if($_GET['pid'] && !$_GET['newurl'] && !$_POST) {
  $friendly_url = getFriendlyURL('project', $_GET['pid']);
  if($friendly_url) {
    $query_string = preg_replace("/pid={$_GET['pid']}/", "", $_SERVER['QUERY_STRING']);
    $query_string = preg_replace("/^&/", "", $query_string);
    header ('HTTP/1.1 301 Moved Permanently');
    header ('Location: '.$friendly_url.($query_string ? "?{$query_string}" : ""));
    exit;
  } else {
    header ('Location: /404.php');
    exit;
  }
}

// Определяем канонический URL для страницы проекта
if (isset($_GET['pid']) && $_GET['pid'] && isset($_GET['newurl']) && $_GET['newurl']) {
    $canonical_url = $GLOBALS['host'].getFriendlyURL('project', (int) $_GET['pid']);
}

$url_parts = parse_url($_SERVER['REQUEST_URI']);
if($_GET['pid'] && !$_POST['action']) {
  $friendly_url = getFriendlyURL('project', $_GET['pid']);
  if(strtolower($url_parts['path'])!=$friendly_url) {
    header ('HTTP/1.1 301 Moved Permanently');
    header ('Location: '.$friendly_url);
    exit;
  }
}

switch ($section) {
    // смена статуса блока рекомендованных фрилансерова
    case 'setRcmdFrlStatus': {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
        $status = __paramInit('string', 'status') == "true";
        $rcmdFrlStatus = employer::SetRcmdFrlStatus($status);
        exit();
        //$_GET['status']
    }
    case 'changePrjFavState': {
        $pid = (isset($_GET['pid'])) ? intval($_GET['pid']) : 0;
        $uid = (isset($_SESSION['uid'])) ? intval($_SESSION['uid']) : 0;
        if ($pid === 0 || $uid === 0) {
            die('0');
        }
        $obj_project = new projects();
        echo $obj_project->changePrjFavState($uid, $pid);
        exit();
    }
    
//------------------------------------------------------------------------------    
    
    default:

        
        if (isset($_POST['prjid']))
        {
          $prj_id = intvalPgSql((int)trim(str_replace("O","0",$_POST['prjid'])));
        }
        else
        {
          $prj_id = intvalPgSql((int)trim(str_replace("O","0",$_GET['pid'])));
        }
        $action = trim($_REQUEST['action']);
        
        $item_page = intval($_POST['page']);
        if (!$item_page) $item_page = intval($_GET['page']);
        if (!$item_page) $item_page = 1;
        
        #if (!$_SESSION['uid'] && !$pass) { include("../fbd.php"); exit; }
        
        // Проект.
        $obj_project = new projects();
        $project = $obj_project->GetPrjCust($prj_id);
        $projectObject = $obj_project->initData($project);
        
        if (!$project) {
            include("../404.php"); 
            exit;
        }
        $project['descr'] = htmlspecialchars($project['descr'], ENT_QUOTES, 'CP1251', false);
        
        $is_owner = $project['user_id'] == $uid;
        $is_adm = hasPermissions('projects');
        
        
        $project_specs = new_projects::getSpecs($prj_id);
        GaJsHelper::getInstance()->setProjectCategory($project_specs);
        
        
        //Если это перемещенная вакансия то редиректим владельца на редактивание
        //А посетителю 404
        /*
        if ($projectObject->isNotPayedVacancy()) {
            if($is_owner) {
                $popup_param = $is_pro ? '' : '&popup=1';
                header("Location: /public/?step=1&kind=4&public={$project['id']}{$popup_param}");
            }elseif(!$is_adm) {
                include("../404.php"); 
                exit;
            }
        }
        */
        
        
        if ($is_owner && $projectObject->isProject() && !$projectObject->isPreferSbr()) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/SubBarNotificationHelper.php");
            SubBarNotificationHelper::getInstance()->setNowMessage(SubBarNotificationHelper::TYPE_RESERVE_PROMO, array(
                'url' => "/public/?step=1&public={$project['id']}&choose_bs=1"
            ));
        }

        
        
        // Проект только что опубликован, записываем в событие для дальнейшей обработки
        if (isset($project['user_id']) && $project['user_id'] == $uid && strpos($_SERVER['HTTP_REFERER'], '/public/') !== FALSE) {
            Zend_Registry::set('project', $project);
            Zend_Registry::set('action.render_project_page_after_publishing', true);
        }

        SeoTags::getInstance()->initProject($project);
        $page_title = SeoTags::getInstance()->getTitle();
        $page_descr = SeoTags::getInstance()->getDescription();
        $page_keyw = SeoTags::getInstance()->getKeywords();

        if(!get_uid(false) && $project['hide']=='t') {
          $_SESSION['ref_uri2'] = $project['id'];
          header("Location: /registration");
          exit;
        }

        if(trim($project['contacts']) != '') {
            $contacts_employer = unserialize($project['contacts']);
            $empty_contacts_employer = 0;
            foreach($contacts_employer as $name=>$contact) { 
                if(trim($contact['value']) == '') $empty_contacts_employer++;
            }
            $is_contacts_employer_empty = ( count($contacts_employer) == $empty_contacts_employer );
        }
        if ($project['sbr_frl_id'] && $project['sbr_status'] >= sbr::STATUS_CHANGED) {
            $project['exec_id'] = $project['sbr_frl_id'];
        }
        if(hasPermissions('projects')) {
            $project_history = $obj_project->GetPrjHistory($prj_id);
        }

		// Новые конкурсы
		if ($project['kind'] == 7) {
			require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';
			$contest = new contest($project['id'], $uid, is_emp(), ($project['user_id'] == $uid), hasPermissions('projects'), is_pro());
            
            $contest->GetOffers((string) $_GET['filter']);
            
			$project['contest_end'] = (mktime() > strtotime($project['end_date']));
			$project['contest_win'] = (mktime() > strtotime($project['win_date']));
			if ($_GET['offer-edit']) $edit = $contest->GetOffer( $_GET['offer-edit'], true );
		}

        if($project['pro_only']=='t' && !$is_pro && !is_emp() && !hasPermissions('projects')) {
          if($project['kind']==7) {
            if(contest::IsContestOfferExists($project['id'], get_uid(false))) { $is_pro = get_uid(false); }
          } else {
            if(projects_offers::IsPrjOfferExists($project['id'], get_uid(false))) { $is_verify=1; $is_pro = get_uid(false); }
          }
        }
		
        // $from_prm: откуда зашли. Нужен для кнопки [Назад].
        // Пусто, значит с первой страницы, иначе из другого места:
        // 3 -- из фрилансерского меню проекты /proj/?p=list.
        $from_prm   = __paramInit('string', 'f', 'f');
        $from_prm_s = !$from_prm ? '' : ('&f='.htmlspecialchars($from_prm));
        if($from_prm == 3) {
            $back_href="/proj/?p=list";
        }
        else if($from_prm == $project['login']) {
            if($project['user_id'] && $project['login'] == $_SESSION['login'])
                $back_href="/users/{$project['login']}/setup/projects/";
            else
                $back_href="/users/{$project['login']}/";
        }
        else {
            $back_href='/';
            $from_prm_s = '';
        }

        //404 если работодатель забанен
        $usr = new users();
        if ($project['user_id'] && $usr->GetField($project['user_id'], $ban_error, "is_banned") > 0 && !hasPermissions('projects')) { include ABS_PATH."/404.php"; exit; }
		
		//Если не участник персонального проекта
        if ($project['kind']==9 //Персональный проект
			&& (!$uid || !(hasPermissions('projects') // Либо админ
				|| $project['user_id'] == $uid // Либо создатель проекта
				|| $project['exec_id'] == $uid // Либо исполнитель проекта
				|| projects_offers::IsPrjOfferExists($project['id'], get_uid(false)) // Либо отвечал на этот проект
			))) { 
				include ABS_PATH."/404.php"; 
				exit;
			}

        //404 если проект заблокирован
        if ($project['is_blocked'] && $_SESSION['uid'] != $project['user_id'] && !hasPermissions('projects')) { include ABS_PATH."/prj_blocked.php"; exit; }
        
        // Платные ответы
        $answers = new projects_offers_answers;
        $answers->GetInfo($uid);
        
        

        $pr_emp = is_emp($project['role']);
        
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
        
        $stop_words = new stop_words( hasPermissions('projects') );
        
        $title = $project['moderator_status'] === '0' && $project['kind'] != 4 && $project['is_pro'] != 't' ? $stop_words->replace($project['name'], 'plain', false) : $project['name'];
        
        $sTitle = htmlspecialchars($title, ENT_QUOTES, 'CP1251', false);

        if ($project['cost'] != 0) {
            switch ($project['priceby']) {
                case '1':
                    $priceby_str = "/час";
                    break;
                case '2':
                    $priceby_str = "/день";
                    break;
                case '3':
                    $priceby_str = "/месяц";
                    break;
                case '4':
                    $priceby_str = "/проект";
                    break;
                default:
                    $priceby_str = "";
                    break;
            }
            if ($project['cost'] == '' || $project['cost'] == 0) {
                $priceby_str = "";
            }
            $project['price_display'] = CurToChar($project['cost'], $project['currency']) . $priceby_str;
            $project['price_display'] = str_replace(array('&euro;', '&nbsp;'), array('€', ' '), $project['price_display']);
        }
        $price = @$project['price_display'] ? $project['price_display'] : 'по договоренности';
        // OpenGraph данные для шаринга в соц.сети
        $FBShare = array(
            "title" => $sTitle .' - '.$price,
            "description" => '', //smart_trim(strip_tags(nl2br($project['descr'])), 30), // убераем переносы строки и теги в описание
            "image" => $GLOBALS['host'] . "/images/logo_50x50.png"
        );
        
        //if ($project['pro_only'] == 't' && !$is_pro && $project['user_id']!=$_SESSION['uid'] && !hasPermissions('projects')) {include("../proonly.php");exit;}
        
        if(get_uid(false)) $project_attach = $obj_project->GetAllAttach($prj_id);
        
        
        /*
        // Стоимость проекта в разных валютах.
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/exrates.php");
        $exrates = new exrates();
        $exrate = $exrates -> GetField($oldMoneyType . $money_to_type, $error, 'val');
        if ($exrate) $money_to_sum = floor($oldMoneySum * 98 * $exrate)/100;
        */
        
        // Предложения по данному проекту.
        $obj_offer = new projects_offers();
        
        $smail = new smail();
        
        // Диалог по предложениям к данному проекту.
        $obj_dialogue = new projects_offers_dialogue();
        
        
        if (hasPermissions('projects') && $project['login']!=$_SESSION["login"])
        {
          switch ($action)
          {
            case "warn":
        #        if (hasPermissions('projects')) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
                    $usr=new users();
                    $usr->Warn($project["login"]);
                    messages::SendProjectWarn($project["login"], $prj_id);
                    header("Location: ". getFriendlyURL("project", $project['id']) . '?' . $from_prm_s);
                    exit;
        #        }
                break;
            case "deloffer":
              if (isset($_GET['oid']))
              {
                $offer_id = (int)trim($_GET['oid']);
              }
              
                // пишем лог админских действий пока еще само предложение не грохнули...
                if ( $_SESSION['uid'] && hasPermissions('projects') ) {
                    $obj_offer->DelOfferLog( $offer_id, $prj_id, $project['name'], $project['user_id'] );
                    
                    // отправляем уведомление об удалении предложения
                    $obj_offer->DelOfferNotification( $offer_id, $_SESSION['uid'] );
                }
                
            	if (!$obj_offer->DelOffer($offer_id, $prj_id, $uid, hasPermissions('projects'))) {
                header("Location: " . getFriendlyURL("project", $project['id']) . '?' . $from_prm_s);
            	}
              exit;
          }
        }
        
		
		if ((hasPermissions('projects') || $project['user_id'] == $uid) && !$project['is_blocked'] && $project['closed'] != 't') {
			switch ($action) {
			    case "free_prj_up":
			        if(hasPermissions('projects')) {
    			        $prj_id = intval($_POST['pid']);
                	    if(new_projects::FreeUpPublicProject($prj_id, $project['user_id']) == true) {
                	        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/admin_log.php");
                	        $sObjLink  = '/projects/?pid=' . $prj_id;
                	        $log = admin_log::addLog( admin_log::OBJ_CODE_PROJ, 17, $project['user_id'], $prj_id, $project['name'], $sObjLink, 0, '', 0, '');
                	    }
                	    header("Location: " . getFriendlyURL("project", $project['id']));
			        }
            	    break;
				case 'blockuser': {
					if ($project['kind'] == 7 && (hasPermissions('projects') || $project['user_id'] == $uid)) {
						if (!($errmsg = $contest->BlockUser($_GET['uid'], $status))) {
							header("Location: ".getFriendlyURL("project", $project['id']).'?'.($_GET['comm']? "&comm={$_GET['comm']}": "").($_GET['offer']? "&offer={$_GET['offer']}": "").($_GET['filter']? "&filter={$_GET['filter']}" :""));
							exit;
						}
					}
					break;
				}
				case 'change-dates': {
					if ($project['kind'] == 7 && $_POST['ds'] && $_POST['de'] && ($contest->is_owner || $contest->is_moder)) {
						if (preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $_POST['ds'], $ds) && checkdate($ds[2], $ds[1], $ds[3])) {
							if (preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $_POST['de'], $de) && checkdate($de[2], $de[1], $de[3])) {
								$d = mktime(0, 0, 0);
								$ds = mktime(0, 0, 0, $ds[2], $ds[1], $ds[3]);
								$de = mktime(0, 0, 0, $de[2], $de[1], $de[3]);
								if ($ds >= $d) {
									if ($de > $ds) {
										if ($error = $contest->ChangeEndDate($ds)) $dateAlert = $error;
										if ($error = $contest->ChangeWinDate($de)) $dateAlert = $error;
										header("Location: ".getFriendlyURL("project", $project['id']));
										exit;
									} else {
										$dateAlert = 'Дата объявления победителя должна быть больше даты окончания конкурса';
									}
								} else {
									$dateAlert = 'Дата окончания конкурса не может находиться в прошлом';
								}
							} else {
								$dateAlert = 'Неправильно указана дата объявления победителя';
							}
						} else {
							$dateAlert = 'Неправильно указана дата окончания конкурса';
						}
					}
					break;
				}
				case 'winners': {
					if ($project['kind'] == 7 && $project['contest_end'] && !$projects['contest_win'] && (hasPermissions('projects') || $project['user_id'] == $uid)) {
                        $contest->SetWinners(intval($_GET['win-1']), intval($_GET['win-2']), intval($_GET['win-3']));
                        header("Location: ".getFriendlyURL("project", $project['id']));
						exit;
					}
				}
			}
		} else if ($action == 'blockuser' || $action == 'change-dates') {
			$errmsg = 'У вас нет прав для выполнения данной операции';
		}

        
        if (is_emp()) {
            
          switch ($action)
          {
            case "prj_close":
              if ($prj_id) {
                  if(projects::isProjectOfficePostedAfterNewSBR($project) && $project['closed']=='t') {
                    header("Location: /404.php");
                    exit;
                  } else {
                    if (!$obj_project->CheckBlocked($prj_id) || hasPermissions('projects')) {
                        $error .= $obj_project->SwitchStatusPrj($uid, $prj_id);
                        header("Location: ".getFriendlyURL("project", $project['id']));
                        exit;
                    }
                  }
              }
              break;
          }
          
          //Персональный проект 
          //показываем одно единственное предложение
          if($project['kind'] == 9)
          {
                $offers = $obj_offer->GetPrjOffers($num_offers, $prj_id, MAX_OFFERS_AT_PAGE, MAX_OFFERS_AT_PAGE * ($item_page - 1), $uid, TRUE, 'date', 'a');
                $real_offers_count = current($obj_offer->CountPrjOffers($prj_id, "all"));
          }
          else
          {
          
                switch ($_GET['sort']) 
                {
                    default:
                    case 'date':
                        $po_sort = 'date';
                        break;
                    case 'time':
                        $po_sort = 'time';
                        break;
                    case 'cost':
                        $po_sort = 'cost';
                        break;
                    case 'rating':
                        $po_sort = 'rating';
                        break;
                    case 'opinions':
                        $po_sort = 'opinions';
                        break;
                }
                
                if ($uid == $project['user_id']) 
                {
                    switch ($_GET['type']) {
                        default:
                        case 'o':
                            $po_type = 'o';
                            break;
                        case 'c':
                            $po_type = 'c';
                            break;
                        case 'r':
                            $po_type = 'r';
                            break;
                        case 'i':
                            $po_type = 'i';
                            break;
                        case 'fr':
                            $po_type = 'fr';
                            break;
                    }
                    $countAllType = 'all';
                } 
                else 
                {
                    $po_type = 'nor';
                    $countAllType = (hasPermissions('projects')) ? 'all' : 'frl_not_refuse';
                }

                if ($PDA) 
                {
                    $po_type = 'nor';
                }
                
                // Предложения по данному проекту.
                // владелец ? выбранная вкладка : (админ ? все : кроме отказавшихся)
                
                $offers = array();
                $real_offers_count = 0;
                
                
                if (($projectObject->isAllowShowOffers() && $projectObject->isOwner($uid)) || $is_adm) {
                
                    $po_type = ($project['user_id'] == $uid) ? $po_type : (hasPermissions('projects') ? 'a' : 'nor');
                    $offers = $obj_offer->GetPrjOffers($num_offers, $prj_id, MAX_OFFERS_AT_PAGE, MAX_OFFERS_AT_PAGE * ($item_page - 1), $uid, ($project['login'] == $_SESSION["login"] || hasPermissions('projects')), $po_sort, $po_type);
                    $real_offers_count = current($obj_offer->CountPrjOffers($prj_id, "frl_not_refuse"));
                
                }
          }
          
          
          $exec_info = null;
          $op_count_all = 0;
          
          //Показываем ответы если админ или владелец оплатил вакансию или владелец ПРО
          if ($projectObject->isAllowShowOffers()) {
          
                if($offers) {
                    foreach($offers as $row) {
                        if($row['user_id'] == $project['exec_id']) {
                            $exec_info = $row;
                            break;
                        }
                    }
                }
                
                if($project['exec_id'] && !$exec_info) $exec_info = $obj_offer->GetPrjOffer($prj_id, $project['exec_id']);

                list($op_count_all, $op_count_all_new_msgs)             = $obj_offer->CountPrjOffers($prj_id, $countAllType);
                list($op_count_offers, $op_count_offers_new_msgs)       = $obj_offer->CountPrjOffers($prj_id, 'offers');
                list($op_count_candidate, $op_count_candidate_new_msgs) = $obj_offer->CountPrjOffers($prj_id, 'candidate');
                list($op_count_refuse, $op_count_refuse_new_msgs)       = $obj_offer->CountPrjOffers($prj_id, 'refuse');
                list($op_count_executor, $op_count_executor_new_msgs)   = $obj_offer->CountPrjOffers($prj_id, 'executor');
                list($op_count_frl_refuse, $op_count_frl_refuse_new_msgs) = $obj_offer->CountPrjOffers($prj_id, 'frl_refuse');
          
          }
          
        }
        else
        {
            $freelancer = new freelancer();
            $freelancer->GetUserByUID(get_uid(false));

            $contacts_freelancer = array(
                'phone' => array(
                    'name' => 'Телефон',
                    'value' => $freelancer->phone
                ),
                'site' => array(
                    'name' => 'Сайт',
                    'value' => $freelancer->site
                ),
                'icq' => array(
                    'name' => 'ICQ',
                    'value' => $freelancer->icq
                ),
                'skype' => array(
                    'name' => 'Skype',
                    'value' => $freelancer->skype
                ),
                'email' => array(
                    'name' => 'E-mail',
                    'value' => $freelancer->second_email
                )
            );
            
            if ($_POST['ps_cost_from'] >= 1000000 || $_POST['ps_cost_from'] < 0) $_POST['ps_cost_from'] = 0;
            if ($_POST['ps_cost_to'] >= 1000000 || $_POST['ps_cost_to'] < 0) $_POST['ps_cost_to'] = 0;

            switch ($action) {
                case 'add':
                
                $hash = __paramInit('string', null, 'hash');
                if ($hash != md5($project['id'] . $uid . projects_offers::SALT)) {
                    header("Location: " . getFriendlyURL("project", $project['id']));
                    exit;
                }
                
                if ( ( ($project['pro_only'] == 't' && !$is_pro) || ($project['verify_only'] == 't' && !$is_verify) ) && $project['user_id']!=$_SESSION['uid'] && !hasPermissions('projects')) { 
                    header("Location: " . getFriendlyURL("project", $project['id']));
                    exit;
                }

                if ((int)$project['exec_id'] > 0) {
                    header("Location: " . getFriendlyURL("project", $project['id']));
                    exit;
                }

                if (($answers->offers || hasPermissions('projects') || is_emp() || ($is_pro && $_SESSION['is_pro_new']=='f') || $project['kind'] == 2 || $project['kind'] == 7 ) && !$project['is_blocked'] && $project['closed'] != 't' || is_pro()){

                    if ($project['kind'] != 2 && $project['kind'] != 7) {
                        if($PDA) {
                          for($i=1;$i<=3;$i++) {
                              if($_POST["ps_check_work_{$i}"] && $_POST["ps_work_{$i}_id"] > 0) {
                                  $portfolio = new portfolio();
                                  $work      = $portfolio->GetPrj(intval($_POST["ps_work_{$i}_id"]));

                                  if($work) {
                                      $_POST["ps_work_{$i}_link"]      = trim($work['link']);
                                      $_POST["ps_work_{$i}_name"]      = trim($work['name']);
                                      $_POST["ps_work_{$i}_pict"]      = trim($work['pict']);
                                      $_POST["ps_work_{$i}_prev_pict"] = trim($work['prev_pict']); 
                                  }
                                  unset($work);
                              } else {
                                  $_POST["ps_work_{$i}_id"]   = '';	
                                  $_POST["ps_work_{$i}_link"] = '';
                                  $_POST["ps_work_{$i}_name"] = '';
                                  $_POST["ps_work_{$i}_pict"] = '';
                                  $_POST["ps_work_{$i}_prev_pict"] = ''; 
                              }	
                          }
                      }

                        $payed_items = '0';

                        if($_POST['is_color']) {
                          $account = new account;
                          $transaction_id = $account->start_transaction(get_uid());
                          $error_buy = $account->Buy($billing_id, $transaction_id, $answers->color_op_code, get_uid(), "Выделение ответа на проект цветом", "Выделение <a href='". (getFriendlyURL("project", $project['id'])) . "#freelancer_{$_SESSION['uid']}' target='_blank'>ответа на проект</a> цветом", 1, 1);
                          $payed_items = '1'; 
                          if($error_buy) {
                              $_POST['is_color'] = false;
                              $payed_items = '0'; 
                          }

                      }

                        if(isset($_POST['contacts'])) {
                          $error_offer = users::validateContacts($_POST['contacts'], $contacts_freelancer);
                      }
                        
                        if (!$error_offer) {
                            $save_contacts = serialize($contacts_freelancer);
                            $error_offer = $obj_offer->AddOffer($uid, $project['id'], $_POST['ps_cost_from'], $_POST['ps_cost_to'], $_POST['ps_cost_type'],
                            $_POST['ps_time_from'], $_POST['ps_time_to'], $_POST['ps_time_type'],  antispam(stripslashes($_POST['ps_text'])),
                            $_POST['ps_work_1_id'], $_POST['ps_work_2_id'], $_POST['ps_work_3_id'],
                            $_POST['ps_work_1_link'], $_POST['ps_work_2_link'], $_POST['ps_work_3_link'],
                            $_POST['ps_work_1_name'], $_POST['ps_work_2_name'], $_POST['ps_work_3_name'],
                            $_POST['ps_work_1_pict'], $_POST['ps_work_2_pict'], $_POST['ps_work_3_pict'],
                            $_POST['ps_work_1_prev_pict'], $_POST['ps_work_2_prev_pict'], $_POST['ps_work_3_prev_pict'],
                            isset($_POST['ps_for_customer_only']), 0, 0, isset($_POST['prefer_sbr']), $_POST['is_color'], $save_contacts, $payed_items);

                            //Получаем новые данные о количестве ответов
                            $answers->GetInfo($uid);
                            $projectKindIdent = $projectObject->getKindIdent();
                            $obj_offer->sendStatistic($projectKindIdent, $answers->offers, $is_pro, $obj_offer->offer_id);

                            require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/base.php");
                            require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/api.php");
                            require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/mobile.php");
                            externalApi_Mobile::addPushMsg($project['user_id'], 'prj_response', array('from_user_id'=>get_uid(false), 'name'=>$project['name'], 'prj_id'=>$project['id']));

                        }
                        
                      $kind = $project['kind'];
                      if(!$error_offer && !$error_buy && $_POST['is_color'] && $account) {
                          $account->commit_transaction($transaction_id, get_uid(), $billing_id);
                          $is_payed_color = true;
                      }

                      if($is_payed_color) {
                          header("Location: /bill/success/"); 
                          exit();
                      }
                      if(!$error_offer) {
                          header("Location: ".getFriendlyURL("project", $project['id']) . '?' . $from_prm_s);
                      }
                  }
                  else if ($project['kind'] == 7)
                  {
                      if ($_POST['comment'] || $_POST['files']) {
                          $comm_blocked = ($contest->is_pro || $contest->is_moder)? $_POST['comm_blocked']: FALSE;
                          $comment = change_q_x(antispam(substr(stripslashes($_POST['comment']), 0, 30000)), false, true, 'b|br|i|p|ul|li|cut|h[1-6]{1}', false, false);
                          if (!($er = $contest->CreateOffer($comment, $_POST['files'], $comm_blocked))) {

                  require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/base.php");
                  require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/api.php");
                  require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/mobile.php");
                  externalApi_Mobile::addPushMsg($project['user_id'], 'prj_response', array('from_user_id'=>get_uid(false), 'name'=>$project['name'], 'prj_id'=>$project['id']));

                              header("Location: ".getFriendlyURL("project", $project['id'])."?offer={$contest->new_oid}");
                              exit;
                          }
                      }
                  }
                  else
                  {        
                      $obj_offer->AddOfferKon($uid, $project['id'], antispam($_POST['ps_text']), $_POST['ps_work_pict'], $_POST['ps_work_prev_pict'], false);

                      require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/base.php");
                      require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/api.php");
                      require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/mobile.php");
                      externalApi_Mobile::addPushMsg($project['user_id'], 'prj_response', array('from_user_id'=>get_uid(false), 'name'=>$project['name'], 'prj_id'=>$project['id']));

                      header("Location: " . getFriendlyURL("project", $project['id']).'?'.$from_prm_s);
                      exit;
                  }

                }
                break;
            case 'change':
                
                $hash = __paramInit('string', null, 'hash');
                if ($hash != md5($project['id'] . $uid . projects_offers::SALT)) {
                    header("Location: " . getFriendlyURL("project", $project['id']));
                    exit;
                }
                
              if ($project['pro_only'] == 't' && !$is_pro && $project['user_id']!=$_SESSION['uid'] && !hasPermissions('projects')) { 
                header("Location: " . getFriendlyURL("project", $project['id']));
                exit;
              }

              if ($project['kind'] != 2 && $project['kind'] != 7) {
                  
                  $payed_items = @$_POST['ps_payed_items'];
                  $payed_color = ($_POST['ps_payed_items'][0] == '1'); 
                  if($_POST['is_color'] && !$payed_color) {
                        $account = new account;
                        $transaction_id = $account->start_transaction(get_uid());
                        $error_buy = $account->Buy($billing_id, $transaction_id, $answers->color_op_code, get_uid(), "Выделение ответа на проект цветом", "Выделение <a href='". (getFriendlyURL("project", $project['id'])) . "#freelancer_{$_SESSION['uid']}' target='_blank'>ответа на проект</a> цветом", 1, 1);
                        $payed_items = '1';
                        if($error_buy) {
                            $_POST['is_color'] = false;
                            $payed_items = '0';
                        } 
                  }
                  
                        if(isset($_POST['contacts'])) {
                            $error_offer = users::validateContacts($_POST['contacts'], $contacts_freelancer);
                        }
                        
                  if(!$error_offer) {
                      $save_contacts = serialize($contacts_freelancer);
                      $error_offer = $obj_offer->AddOffer($uid, $project['id'], $_POST['ps_cost_from'], $_POST['ps_cost_to'], $_POST['ps_cost_type'],
                      $_POST['ps_time_from'], $_POST['ps_time_to'], $_POST['ps_time_type'],  antispam(stripslashes($_POST['ps_text'])),
                      $_POST['ps_work_1_id'], $_POST['ps_work_2_id'], $_POST['ps_work_3_id'],
                      $_POST['ps_work_1_link'], $_POST['ps_work_2_link'], $_POST['ps_work_3_link'],
                      $_POST['ps_work_1_name'], $_POST['ps_work_2_name'], $_POST['ps_work_3_name'],
                      $_POST['ps_work_1_pict'], $_POST['ps_work_2_pict'], $_POST['ps_work_3_pict'],
                      $_POST['ps_work_1_prev_pict'], $_POST['ps_work_2_prev_pict'], $_POST['ps_work_3_prev_pict'],
                      isset($_POST['ps_for_customer_only']), InGetPost('edit',0), 0, isset($_POST['prefer_sbr']), $_POST['is_color'], $save_contacts, $payed_items);
                  } else {
                      $error = true;
                  }
                
                  if(!$error_offer && !$error_buy && !$payed_color && $account) {
                      $account->commit_transaction($transaction_id, get_uid(), $billing_id);
                      $is_payed_color = true;
                  }
                    
                  if($is_payed_color) {
                      header("Location: /bill/success/"); 
                      exit();
                  }
                
                if($error === 403) {
                    include ABS_PATH."/403.php"; exit;
                }
                $kind = $project['kind'];
                if(!$error) { 
                    header("Location: " . getFriendlyURL("project", $project['id']) .'?'.$from_prm_s);
                    exit;
                }
              }
              else if ($project['kind'] == 7  && !$project['is_blocked'] && $project['closed'] != 't')
			  {
				if ($_POST['comment'] || $_POST['files']) {
					if ($uid == $contest->offer['user_id'] || hasPermissions('projects')) {
						$comm_blocked = ($contest->is_pro || $contest->is_moder)? $_POST['comm_blocked']: ($contest->offer['comm_blocked'] == 't');
						$comment = change_q_x(antispam(substr($_POST['comment'], 0, 30000)), false, true, 'b|br|i|p|ul|li|cut|h[1-6]{1}', false, false);
						if (!$contest->ChangeOffer($contest->offer['id'], $comment, $_POST['files'], $comm_blocked)) {
							header("Location: ".getFriendlyURL("project", $project['id'])."?offer={$contest->offer['id']}");
							exit;
						}
					}
				}
			  }
			  else
              {
                $obj_offer->ChangeOfferKon($uid, $project['id'], $_POST['ps_work_pict'], $_POST['ps_work_prev_pict']);
                header("Location: " .getFriendlyURL("project", $project['id']).'?'.intval($project['id']).$from_prm_s);
              }
              break;
            case 'buy':
              if (!($error = $answers->BuyByFM($_SESSION['uid'], $_POST['ammount'])))
              {
                header("Location: ".getFriendlyURL("project", $project['id']));
                exit;
              }
              break;
            case "payed_is_color":
                $offer_id = __paramInit('int', null, 'id_offers');
                $account = new account;
                $transaction_id = $account->start_transaction(get_uid());
                $project_id  = $obj_offer->getProjectIDByOfferID($offer_id);
                $error_buy   = $account->Buy($billing_id, $transaction_id, $answers->color_op_code, get_uid(), "Выделение ответа на проект цветом", "Выделение <a href='". (getFriendlyURL("project", $project_id)) . "#freelancer_{$_SESSION['uid']}' target='_blank'>ответа на проект</a> цветом", 1, 1);
                $is_color    = 't';
                $payed_items = '1'; 
                if($error_buy) {
                    $is_color    = 'f';
                    $payed_items = '0'; 
                }
                $fields = array("is_color"    => "'{$is_color}'",
                                "payed_items" => "B'{$payed_items}'"
                                );
                $error = $obj_offer->setFieldsOffers($offer_id, $fields);
                if(!$error) {
                    header("Location: /bill/success/"); 
                    exit();
                } else {
                    $error_is_color = "Ошибка обработки запроса";
                }
                break;
          }
          
          $hash = md5($project['id'] . $uid . projects_offers::SALT);
          
          $offers = array();
          $exec_info = null;
          
          // $real_offers_count - реальное количество ответов на проекты (видимые, скрытые, и ответ пользователя не зависимо от статуса ответа)
          $real_offers_count = current($obj_offer->CountPrjOffers($prj_id, "frl_not_refuse"));
                
          //Показываем админу
          if ($is_adm) {
              
                // Предложения по данному проекту.
                $type   = hasPermissions('projects') ? 'a' : 'nor';
                // $offers - массив ответов на проект которые будут отображены (здесь нет скрытых ответов и ответа от пользователя)
                $offers = $obj_offer->GetPrjOffers( $num_offers, $prj_id, MAX_OFFERS_AT_PAGE, MAX_OFFERS_AT_PAGE * ($item_page-1), $uid, hasPermissions('projects'), null, $type );
                //$real_offers_count = current($obj_offer->CountPrjOffers($prj_id, "all"));

                if($offers) {
                    foreach($offers as $row) {
                        if($row['user_id'] == $project['exec_id']) {
                            $exec_info = $row;
                            break;
                        }
                    }
                }

                
                //@todo: Устаревший не используемый функционал
                // если из проекта сформирована сделка
                /*
                if (!$exec_info && $project['sbr_id']) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
                    // сделка должна с согласием исполнителя
                    $offerInfo = $obj_offer->getSbrExecData($project['sbr_id']);
                    if ($offerInfo && $offerInfo['status'] >= sbr::STATUS_CHANGED) {
                        $exec_info = $offerInfo;
                    }
                }
                */
          }          

          
          if ($uid > 0 && $project['exec_id'] > 0 && !$exec_info)  {
              $exec_info = $obj_offer->GetPrjOffer($prj_id, $project['exec_id']);
          }
          
          
            // Наличие предложения данного юзера по данному проекту.
            $user_offer_exist = $obj_offer->OfferExist($prj_id, $uid);
            if ($user_offer_exist) {
                // Предложение данного пользователя по данному проекту.
                $user_offer = $obj_offer->GetPrjOffer($prj_id, $uid);

                if($project['exec_id'] == $uid) $exec_info = $user_offer;
                // Диалог по предложению данного пользователя.
                $user_offer['dialogue'] = $obj_dialogue->GetDialogueForOffer($user_offer['id']);
            } else {
                // Предложение данного пользователя по данному проекту.
                $user_offer = false;
            }
          
          
          
          // Профессии.
          $obj_profession = new professions();
          $professions = $obj_profession->GetSelFilProf($uid);
          if (!$professions)
          {
            $professions = array();
            // Текущая профессия.
            $cur_prof = 0;
          }
          else
          {
            // Текущая профессия.
            $cur_prof = $professions[0]['id'];
          }
          
          // Работы.
          $obj_portfolio = new portfolio();
          if(!($portf_works = $obj_portfolio->GetPortfProf($uid, $cur_prof))) {
            $portf_works = array();
          }
          
          foreach ($portf_works as &$work) {
              $obj_portfolio->GenerateStaticPreview($work, $_SESSION['login']);              
          }
          
          /*if($PDA) {
          	if(!($portf_works = $obj_portfolio->GetPortf($uid))) {
	            $portf_works = array();
	        }	
          }*/
          // Признак того, что работ > MAX_WORKS_IN_LIST
          $portf_more = (count($portf_works) > MAX_WORKS_IN_LIST);
        }
        
        // Диалоги по предложениям к данному проекту.
        if (isset($offers) && is_array($offers))
        {
          foreach ($offers as $key => $value)
          {
            $offers[$key]['dialogue'] = $obj_dialogue->GetDialogueForOffer($value['id']);
          }
        }
        
        $header = "../header.php";
        $footer = "../footer.html";

        
        
        //Валидный пользователь или нет для показа ему дополнительной информации о проекте
        $show_info = ($project['kind'] == 2 || $project['kind'] == 7 || 
                      ($uid > 0 && (($is_pro || $project['exec_id'] == $uid) && $projectObject->isAllowShowOffers() || 
                       hasPermissions('projects') || 
                       $project['user_id'] == $uid))
                      //(projects_offers::IsPrjOfferExists($project['id'], $uid) && $project['pro_only'] === 'f' && $projectObject->isAllowShowOffers())
        );
        
        
        $aNote = false;
        if(get_uid() && $show_info && $_SESSION['uid'] != $project['user_id']) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notes.php"); 
            $oNotes = new notes();
            $aNote  = $oNotes->GetNoteInt( $_SESSION['uid'], $project['user_id'], $error );
        } 
        
        //Помечаем проект как прочитанный (счетчик просмотров)
        $obj_project->SetRead($project, $uid);
        
        if (is_emp())
        {
          if ($project['kind'] == 2)
          {
            /*  
             * todo: неиспользуемый кусок?
            if (($project['user_id'] == $uid) && ($op_count_all > 0)) {
            #close: onload                $onload = 'start_scroll();';
            }
            */
              
            $css_file = array('/css/block/b-free-share/b-free-share.css','/css/block/b-note/b-note.css','/css/block/b-button-multi/b-button-multi.css','/css/block/b-prev/b-prev.css', '/css/nav.css', '/css/projects3.css' );
            $content = "content_kon_emp.php";
            
          }
          else if ($project['kind'] == 7)
          {
                $content = "contest.php";
                $css_file = array('/css/block/b-free-share/b-free-share.css','/css/block/b-note/b-note.css','/css/block/b-button-multi/b-button-multi.css','/css/block/b-prev/b-prev.css', 'contest.css', '/css/nav.css' );
                $js_file = array('ibox.js', 'contest.js', 'banned.js', 'calendar.js', 'projects.js',
                    '/css/block/b-popup/b-popup.js', 'note.js', 'mootools.resizableTextarea.js', 'attachedfiles.js', 'calendar.js',
                    '/css/block/b-pay-answer/b-pay-answer.js', '/css/block/b-shadow/b-shadow.js');

                if (hasPermissions('projects')) {
                    $js_file[] = 'projects-quick-edit.js';
                    $js_file[] = 'projects/projects_adm.js';
                }

                $header = "../header.php";
                $footer = "../footer.html";
                include ("../template2.php");
                exit;
          }
          else
          {
            /*  
             * todo: неиспользуемый кусок?
            if (($project['user_id'] == $uid) && ($op_count_all > 0)) {
            #close: onload                $onload = 'start_scroll();';
            }
            */
              
            $content = "content_emp.php";
            
            if($project['kind'] == 9) 
            {
                $content = "content_personal_emp.php";  
            }
             
            
            $css_file = array('/css/block/b-free-share/b-free-share.css','/css/block/b-note/b-note.css','/css/block/b-button-multi/b-button-multi.css','/css/block/b-prev/b-prev.css','contest.css', '/css/nav.css', '/css/projects3.css' );
            $js_file = array( '/css/block/b-popup/b-popup.js', 'note.js', 'mAttach2.js', 'projects.js', 'attachedfiles.js', 'calendar.js', 'mt_xajax.js', 'projects/massending.js' );
            if (hasPermissions('projects')) {
                $js_file[] = 'banned.js';
                $js_file[] = 'projects-quick-edit.js';
                $js_file[] = 'projects/projects_adm.js';
            }
            

            $pod = new projects_offers_dialogue;
            $pod_mod_mark_ids = array();
            $pod_emp_mark_ids = array();
            foreach($offers as $k=>$v) {
              if( hasPermissions('projects') && $v['mod_new_msg_count']==1 && $v['msg_count']==1 && count($value['dialogue'])==1 ) {
                array_push($pod_mod_mark_ids, $v['id']);
                $offers[$k]['mod_new_msg_count'] = 0;
              } elseif ( $project['login']==$_SESSION["login"] && $v['emp_new_msg_count']>0 && $v['emp_new_msg_count']==1 && $v['msg_count']==1 ) {
                array_push($pod_emp_mark_ids, $v['id']);
                $offers[$k]['emp_new_msg_count'] = 0;
              }
            }
            if($pod_mod_mark_ids) {
              $pod->markReadMod($pod_mod_mark_ids, $uid);
            }
            if($pod_emp_mark_ids) {
              $pod->markReadEmp($pod_emp_mark_ids, $uid);
            }

          }
          
        } else {
            if (!($project['kind'] == 2 || $project['kind'] == 7 
                    || $answers->offers || !$_SESSION['uid'] || is_emp() 
                    || ($is_pro && $_SESSION['is_pro_new']=='f') 
                    || hasPermissions('projects') || $user_offer_exist)) {
                $no_answers = 1;
            } else {
                $no_answers = 0;
            }
          
          
          //if(is_pro()) $no_answers = 1;
          if ($project['kind'] == 2)
          {
            $css_file = array('/css/block/b-free-share/b-free-share.css','/css/block/b-note/b-note.css','/css/block/b-button-multi/b-button-multi.css','/css/block/b-prev/b-prev.css','contest.css', '/css/nav.css', '/css/projects3.css' );
            $js_file = array( 'banned.js' );
			$content = "content_kon_frl.php";
          }
          else if ($project['kind'] == 7)
		  {
                $content = "contest.php";
				$header = "../header.php";
				$footer = "../footer.html";
                $css_file = array('/css/block/b-free-share/b-free-share.css','/css/block/b-note/b-note.css','/css/block/b-button-multi/b-button-multi.css','/css/block/b-prev/b-prev.css','contest.css', '/css/nav.css', '/css/projects3.css' );
				$js_file = array( 'ibox.js', 'contest.js', 'banned.js', 'calendar.js', 'projects.js', 
				    '/css/block/b-popup/b-popup.js', 'note.js', 'mootools.resizableTextarea.js', 'attachedfiles.js', 'calendar.js', '/css/block/b-shadow/b-shadow.js' );
			    
			    if ( hasPermissions('projects') ) {
                	$js_file[] = 'projects-quick-edit.js';
                    $js_file[] = 'projects/projects_adm.js';
                }
                
                if ($project['verify_only'] == 't') {
                    $js_file[] = 'verification.js';
                }
                
				include ("../template2.php");
				exit;
		  }
		  else
          {
            $css_file = array('/css/block/b-free-share/b-free-share.css','/css/block/b-note/b-note.css','/css/block/b-button-multi/b-button-multi.css','/css/block/b-prev/b-prev.css','contest.css', '/css/nav.css', '/css/projects3.css' );
            $js_file = array( 'mootools.resizableTextarea.js', '/css/block/b-popup/b-popup.js', 'note.js', 'mAttach2.js', 
               'projects.js', 'attachedfiles.js', 'calendar.js', '/css/block/b-pay-answer/b-pay-answer.js', '/scripts/b-combo/b-combo-phonecodes.js' );
                
            if ( hasPermissions('projects') ) {
              	$js_file[] = 'projects-quick-edit.js';
                $js_file[] = 'projects/projects_adm.js';
            }

            if (hasPermissions('projects')) {
                $js_file[] = 'banned.js';
            }
            
            if ($project['verify_only'] == 't') {
                $js_file[] = 'verification.js';
            }
            
            $content = "content_frl.php";
            
            $pod_mod_mark_ids = array();
              $pod = new projects_offers_dialogue;
              foreach($offers as $k=>$v) {
                if( hasPermissions('projects') && $v['mod_new_msg_count']==1 && count($value['dialogue'])==1 ) {
                  array_push($pod_mod_mark_ids, $v['id']);
                }
              }
              if($pod_mod_mark_ids) {
                $pod->markReadMod($pod_mod_mark_ids, $uid);
              }

          }
        }
        
        $_is_inner = 1;
/* ======= */
        break;

    case 'list':
      if (!$uid) { header ("Location: /registration/"); exit; }
      
      $no_banner = !!$is_pro;

      if (is_emp()) { header ("Location: /"); exit; }

        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_filter.php");
      
        $po_summary = projects_offers::GetFrlOffersSummary($uid);
        projects_offers::ResetAllEvents($uid);
        $_SESSION['po_count'] = $po_summary['total'];

        $action = __paramInit('string','action','action');
        $folder = __paramInit('int', 'fld', 'fld',0);
        if($kind < 0 || $kind > 6)
            $kind = 0;

        $css_file = array('/css/block/b-free-share/b-free-share.css','/css/block/b-note/b-note.css','/css/block/b-button-multi/b-button-multi.css','/css/block/b-prev/b-prev.css','projects.css', '/css/nav.css', '/css/projects3.css' );
        $header = "../header.php";
        $footer = "../footer.html";
        $content = "content_list.php";
        include ("../template2.php");
        exit;

    case "comment":
        if($PDA) {
            $cid     = intval($_GET['cid']);
            $user_id = get_uid(false);
            $prj_id = intvalPgSql((int)trim(str_replace("O","0",$_GET['pid'])));
            $po_id   = intval($_GET['id']);
            // Проект.
            $action = trim($_REQUEST['action']);
            $obj_project = new projects();
            $project = $obj_project->GetPrjCust($prj_id);
            $pod = new projects_offers_dialogue;   
            if(is_emp()) {
                if($project['user_id'] != get_uid(false)) {
                    $content = "404.php";
                    include ("../template2.php");
                    exit;    
                }
            } else {
                $po  = new projects_offers;
                if(!$project || !$po->GetPrjOffer($prj_id, $user_id) || ($cid && $pod->GetLastDialogueMessage($user_id, $po_id) != $cid)) {
                    $content = "404.php";
                    include ("../template2.php");
                    exit;
                }
            }
            if($cid) {
                $is_edit = true;
                $pod = new projects_offers_dialogue;
                $project_dialogue = $pod->GetDialogueForOffer($po_id);  
                $edit_dialog = $project_dialogue[count($project_dialogue)-1]; 
            }
            if($action == "create") {
                if(!trim($_POST['po_text'])) {
                    $error = "Невозможно отправить пустое сообщение.";
                }
    
                //Не позволяем производить действия с заблокированным проектом
                if (projects::CheckBlocked(intval($_POST['prj_id']))) {
                    return;
                    
                } elseif (intval($_SESSION['uid'])) {
                	$po_id   = intval($_POST['po_id']);
                	$po_text = antispam(trim($_POST['po_text']));
                	$po_commentid = intval($_POST['po_commentid']);
                	$user_id = get_uid(false);
                	$user = new users();
                	$user_name = $user->GetName($user_id, $error);

                
                	
                	$project_dialogue = $pod->GetDialogueForOffer($po_id);
                	$project = $pod->GetProjectFromDialogue($po_id);

                	if (count($project_dialogue)) {
                		for ($i=count($project_dialogue)-1; $i>=0; $i--) {
                			if ($project_dialogue[$i]['user_id'] != $user_id) {
                				$to_user_name = $project_dialogue[$i]['login'];
                				break;
                			}
                		}
                	}

                	if (is_emp()) {
                	    $emp_read = true;
                	    $frl_read = false;
                	}
                	else {
                	    $emp_read = false;
                	    $frl_read = true;
                	}

	               if (!$po_commentid) {
                		$error = $pod->AddDialogueMessage($po_id, $user_id, $po_text, $frl_read, $emp_read);
                		$last_comment = $pod->GetLastDialogueMessage($user_id, $po_id);
                	} else {
                		$error = $pod->SaveDialogueMessage($user_id, $po_text, $po_commentid, $po_id, false);
                        $last_comment = $po_commentid;
                		if ($error == 1) {
                			$error  = "Вы не можете редактировать комментарий, так как на него уже ответили.";
                		}
                	}

                	$po_text = rtrim(ltrim($po_text, "\r\n"));
                	$po_text = substr(change_q_x($po_text, false, true, '', false, false), 0, 1000);
                	$po_text = stripslashes($po_text);
                }
                if(!$error) {
                    //header("Location: ".getFriendlyURL("project", $project['id'])."?id={$po_id}&cid={$last_comment}&p=comment");
                    header("Location: ".getFriendlyURL("project", $project['id']));
                    exit;
                }
            }
            
            $content = "add_comment.php";
            include ("../template2.php");
            exit;        
        }
        break;
    case "executor":
        if($PDA) {
           
            $po_id   =  intval($_GET['id']);
            $prj_id  =  intval($_GET['pid']);
            $user_id =  intval($_GET['uid']);
            
            $user      = new users();
            $prj       = new projects;
            $prj_offer = new projects_offers;
            $user_name  = $user->GetName($user_id, $error);
        
            $emp_id     = get_uid(false);
            $emp_name   = $user->GetName($emp_id, $error);
        
            //Не позволяем производить действия с заблокированным проектом
            if (projects::CheckBlocked(intval($prj_id))) {
                header('Location: /projects/index.php?pid='.intval($prj_id));
                exit;
            }
        
            $project = $prj->GetPrj($emp_id, $prj_id, 1);
            if($error = $prj->SetExecutor($prj_id, $user_id, $emp_id)) {
                $content = "404.php";
                include ("../template2.php");
                exit;
            }
            
            header('Location: /projects/index.php?pid='.intval($prj_id));
            exit;
        }
        break;
    case "candidate":
        if($PDA) {
            $po_id   =  intval($_GET['id']);
            $prj_id  =  intval($_GET['pid']);
            $user_id =  intval($_GET['uid']);
            
        	$user      = new users();
        	$prj       = new projects;
        	$prj_offer = new projects_offers;
        
        	$user_name  = $user->GetName($user_id, $error);
        
        	$emp_id     = get_uid(false);
        	$emp_name   = $user->GetName($emp_id, $error);
            
        	$project = $prj->GetPrj($emp_id, $prj_id, 1);
            if(!$project) {
                $content = "404.php";
                include ("../template2.php");
                exit;    
            }
            //Не позволяем производить действия с заблокированным проектом
            if (projects::CheckBlocked(intval($prj_id))) {
                header('Location: /projects/index.php?pid='.intval($prj_id));
                exit;
            } else {
                $error = '';
                $project = $prj->GetPrjCust($prj_id);
                if ($project['exec_id'] == $user_id) {
                    $error  = $prj->ClearExecutor($prj_id, $emp_id);
                }
                
                if(!$error) {
                    $error = $prj_offer->SetSelected($po_id, $prj_id, $user_id, true);
                    header('Location: /projects/index.php?pid='.intval($prj_id));
                    exit;
                }
            }
        }
        break;
    case "refuse":
        if($PDA) {
            $po_id   =  intval($_GET['id']);
            $prj_id  =  intval($_GET['pid']);
            $user_id =  intval($_GET['uid']);
            $prj     = new projects;
            $project = $prj->GetPrj($emp_id, $prj_id, 1);
            if(!$project) {
                $content = "404.php";
                include ("../template2.php");
                exit;    
            }
            
            if(isset($_GET['refuse'])) {
                $user      = new users();
            	$prj_offer = new projects_offers;
            
            	$po_reason = intval($_GET['refuse']);
            	
            	$emp_id   = get_uid(false);
            	$emp_name = $user->GetName($emp_id, $error);
            
                //Не позволяем производить действия с заблокированным проектом
                if (projects::CheckBlocked(intval($prj_id))) {
                    $objResponse->script("document.location.href='/projects/index.php?pid=".intval($prj_id)."'");
                } else { 
                    $error = '';
                    $project = $prj->GetPrjCust($prj_id);
                    if ($project['exec_id'] == $user_id) {
                        $error  = $prj->ClearExecutor($prj_id, $emp_id);
                    }
                    if (!$error) {
            	       $prj_offer->SetRefused($po_id, $prj_id, $user_id, $po_reason, true); 
            	       header('Location: /projects/index.php?pid='.intval($prj_id));
                       exit;   
                    }
                }
            } else {
                $user      = new users();
                $user_id   = intval($user_id);
                $user->GetUserByUID($user_id);
            }
            
            $content = "emp_refuse.php";
            include ("../template2.php");
            exit;  
        }
        break;
}

$user_phone_block = user_phone::getInstance()->render(user_phone::PLACE_HEADER);
$user_phone_projects = user_phone::getInstance()->render(user_phone::PLACE_PROJECTS);

// Формируем JS внизу страницы
define('JS_BOTTOM', true);

$css_file = array('/css/block/b-frm-filtr/b-frm-filtr.css','/css/block/b-opinion/b-opinion.css','/css/block/b-free-share/b-free-share.css','/css/block/b-note/b-note.css','/css/block/b-button-multi/b-button-multi.css','/css/block/b-prev/b-prev.css', '/css/nav.css', '/css/projects3.css' );

$js_file[] = '/css/block/b-shadow/b-shadow.js';
if(!is_emp() && get_uid(false)) {
    $js_file[] = '/scripts/uploader.js';
    $js_file[] = '/scripts/project_abuse.js';
}


$status_content = null;

if(in_array($project['kind'], array(1,5,9))) 
{
    $feedback_form = '';
    $off_status = false;
    
    if($project['exec_id'] > 0)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');
        
        if(tservices_helper::isAllowOrderReserve())
        {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
            $orderModel = TServiceOrderModel::model();
            $order_id = $orderModel->isExistByType(
                    $project['id'],
                    $project['exec_id'],
                    TServiceOrderModel::TYPE_PROJECT);
             
             $order_url = ($order_id)?tservices_helper::getOrderCardUrl($order_id):null;
             $off_status = ($order_id)?true:false;
        }
    }
    
    if(!$off_status)
    {
        $js_file[] = '/scripts/projects/projects_status.js';
    
        if($project['kind'] == 9 && !isset($user_offer) && isset($offers)) $user_offer = $offers[0];
    
        $status_content = projects_helper::renderStatus($project, ($user_offer)?$user_offer:$exec_info);
        if($status_content) $feedback_form = projects_helper::renderFeedback($project['status']);
    }
}

if(!$status_content) 
{
    $status_content = projects_helper::renderGuestStatus($exec_info);
}


include ("../template.php");