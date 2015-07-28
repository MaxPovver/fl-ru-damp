<?php
define('PUBLIC_INDEX', true);
include($_SERVER['DOCUMENT_ROOT'] . '/public/new/index.php');
exit();

$public_project_page = 1;
$stretch_page = true;
$showMainDiv  = true;
$no_banner = 1;
$enter=true;
$header = "../header.php";
$footer = "../footer.html";
$page_title = "Публикация проекта - фриланс, удаленная работа на FL.ru";
$page_keyw = "фрилансер, работодатель, удаленная работа, поиск работы, предложение работы, портфолио фрилансеров, разработка сайтов, программирование, переводы, тексты, дизайн, арт, реклама, маркетинг, прочее, fl.ru";
$page_descr = "Фрилансер. Работодатель.Удаленная работа. Поиск работы. Предложение работы. Портфолио фрилансеров. Разработка сайтов, Программирование, Переводы, Тексты, Дизайн, Арт, Реклама, Маркетинг, Прочее. FL.ru";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
session_start();
$uid = get_uid();

$drafts = new drafts;

$step    = __paramInit('int', 'step', 'step', -1);
$proj_id = __paramInit('int', 'public', 'public', 0);
$kind    = __paramInit('int', 'kind', 'kind');
if ($kind == 7) {
    $page_title = "Публикация конкурса - фриланс, удаленная работа на FL.ru";
}
$back    = __paramInit('string', 'red', 'red', '');
$key     = __paramInit('string', 'pk', 'pk');

$draft_id   = intval(__paramInit('int', 'draft_id', 'draft_id'));
$auto_draft = intval(__paramInit('int', 'auto_draft', 'auto_draft'));

if(!$key) {
  $key = md5(uniqid($uid)); // ключ-идентификатор создаваемого/редактируемого проекта, для хранения в кэше.
  if($step==1 && !$proj_id) { // если с нулевого шага пришли. Не передаем ключ оттуда сразу, т.к. юзер может несколько
                              // несколько проектов сразу открыть, в этом случае ключ должен быть уникальным.
      header("Location: /public/?step=1&kind={$kind}&pk={$key}&".($auto_draft?'auto_draft=1&':'').($draft_id?'draft_id='.$draft_id.'&':'')."red=".urlencode($back));
      exit;
  }
}
$pprm = '&pk='.$key;

$tmpPrj = new tmp_project($key);
if(!($project = $tmpPrj->init($step, $proj_id))) {
    //$error = 'no_emp';
    include("../emp_only.php");
    exit;
}

if($proj_id && projects::isProjectOfficePostedAfterNewSBR($project) && !hasPermissions('projects')) {
    header("Location: /404.php"); 
    exit;
}


if ($proj_id && $tmpPrj->isKonkurs() && !$tmpPrj->isActiveKonkurs() && !hasPermissions('projects')) {
    $error = 'contest_closed';
    $content = "error.php";
    include("../template2.php");
    exit;
}
    
$backLink = $back ? $back : $tmpPrj->pop('backLink');
$tmpPrj->push('backLink', $backLink);
$error    = $tmpPrj->pop('error');

$action  = __paramInit('string', 'action', 'action');

if(!$proj_id) { $draft_prj_id  = __paramInit('int', 'draft_prj_id', 'draft_prj_id'); } else { $draft_prj_id = $proj_id; }

if($PDA) {
    if($_POST['action_prev'] != "") $action = "prev";
    if($_POST['action_next'] != "") $action = "next";
    if($_POST['action_save'] != "") $action = "save";
    if($_POST['action_reload'] != "") $action = "reload";
    if($_POST['action_change'] != "") $action = "change";
    if($_POST['action_change2'] != "") $action = "change_country";
    if($_POST['action_change3'] != "") $action = "change_country2";
}

// черновики. если пользователь сразу публикует, то подставляем данные в массив #_POST
if ( $draft_id && $auto_draft ) {
    
    $draft = $drafts->getDraft($draft_id, $uid, 1);

    $uploader = new uploader(uploader::createResource('project'));
    $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($draft_id, 4);
    if($attachedfiles_tmpdraft_files) {
        $attachedfiles_draft_files = array();
        foreach($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
            $attachedfiles_draft_files[] = $attachedfiles_draft_file;
        }
        $uploader->setFiles($attachedfiles_draft_files, uploader::STATUS_ADDED);
    }

    $_POST = array(
        'kind' => $draft['kind'],
        'descr' => addslashes($draft['descr']),
        'name' => addslashes($draft['name']),
        'cost' => $draft['cost'],
        'currency' => $draft['currency'],
        'priceby' => $draft['priceby'],
        'pro_only' => ($draft['pro_only'] == 't'? 1: 0),
        'verify_only' => ($draft['verify_only'] == 't'? 1: 0),
        'strong_top' => $draft['strong_top'],
        'prefer_sbr' => ($draft['prefer_sbr'] == 't'? 1: 0),
        'draft_id' => $draft_id,
        'auto_draft' => $auto_draft,
        'budget_type' => $draft['budget_type'],
        'IDResource' => array($uploader->resource),
        //'attachedfiles_session' => $attachedfiles->getSession(),
        'attachedfiles_deleteold' => '1',
    );

//echo '<pre>'; var_dump($_POST); echo '</pre>';    exit;
    if ( $draft['kind'] == 4 ) {
        $_POST['country'] = $draft['country'];
        $_POST['city'] = $draft['city'];
    }
    
    if ( $draft['kind'] == 7 ) {
        $_POST['end_date'] = $draft['p_end_date'];
        $_POST['win_date'] = $draft['p_win_date'];
    }
    
    $_POST['categories'] = array();
    $_POST['subcategories'] = array();
    
    if ( !empty($draft['categories']) ) {
        $c = explode(',', $draft['categories']);
        $cats = array();
        foreach ( $c as $v ) {
            $p = explode('|', $v);
            $_POST['categories'][] = $p[0];
            $_POST['subcategories'][] = $p[1];
        }
    }
    
    $action = 'next';

} elseif ($draft_id && !$drafts->getDraft($draft_id, $uid, 1)) { // если дан id черновика, но такой черновик не существует
    header("Location: /403.php"); exit;
}

$account = new account();
$account->GetInfo($uid);
$_SESSION['ac_sum'] = $account->sum;
$_SESSION['ac_sum_rub'] = $account->sum_rub;
$_SESSION['bn_sum'] = $account->bonus_sum;

$konk_price = new_projects::getPriceByCode(( is_pro() ? new_projects::OPCODE_KON : new_projects::OPCODE_KON_NOPRO ));

switch($step)
{
    case 1:

        // Для PDA версии
        if($action == 'prev' && $PDA) {
            $content = "content0.php";
            break;
        }
        
        if($PDA && (!$project['subcategory'] || $action == "change")) {
            $cat = __paramInit('int', 'category', null, 0);
            $subcat = __paramInit('int', 'subcategory', null, 0);
            $tmpPrj->setProjectField('category', $cat);
            $tmpPrj->setProjectField('subcategory', $subcat);
        }
        
        if($PDA && (!$project['city'] || $action == "change_country" || $action == "change_country2")) {
            $tmpPrj->setProjectField('country', __paramInit('int', 'country', 0));
            $tmpPrj->setProjectField('city', __paramInit('int', 'city', 0));
        }
        
        if($action=="change_country2" && $PDA) {
            header("Location: /public/?step={$step}&kind={$project['kind']}&category={$project['category']}&subcategory={$project['subcategory']}{$pprm}");
            exit;
        }
        
        $tmpPrj->setProjectField('kind', $kind ? $kind : $project['kind']);
        // Может быть получен с нулевого шага.
        $project = $tmpPrj->getProject();
        
        if($project['kind'] && !in_array($project['kind'], array(0,1,2,4,7)))
            $error['kind'] = 'Закладка не выбрана';
        
        if($action=='next' || $action=="change" || $action=="change_country") {
            
            $use_draft = ($project['user_id']==get_uid(false));
            
            // сохаряем проект в черновики между первым и вторым шагом, если вдруг что пойдет не так
            $is_tmp_draft = false;
            if ($use_draft) {
                if(!$draft_id) $is_tmp_draft = true;
                $tmp = $_POST;
                $tmp['uid'] = $uid;
                $dmp = $drafts->SaveProject($tmp);
                $draft_id = $dmp['id'];
            }
            
            $tmpPrj->setProjectField('descr', antispam(__paramInit('html', NULL, 'descr', NULL, NULL, TRUE)));
            $tmpPrj->setProjectField('name', substr(antispam(__paramInit('string', NULL, 'name', NULL, 60)),0,512));
            $tmpPrj->setProjectField('cost', __paramInit('float', NULL, 'cost',0));
            $tmpPrj->setProjectField('currency', __paramInit('int', NULL, 'currency',0));
            $tmpPrj->setProjectField('budget_type', __paramInit('int', NULL, 'budget_type',0));
            $tmpPrj->setProjectField('priceby', __paramInit('int', NULL, 'priceby',0));
            $tmpPrj->setProjectField('agreement', __paramInit('int', NULL, 'agreement',0));
            if(!$PDA) {

                $c = __paramInit('array', NULL, 'categories');
                $sc =  __paramInit('array', NULL, 'subcategories');
                if(empty($c) || (sizeof($c)==1 && $c[0] == 0)) {
                    
                    $error['category'] = 'Не выбран раздел';   
                    
                } else {
                
                    $cats = array();
                    foreach ($c as $sKey => $value) { 
                        if($value == 0) continue;
                        $check[] = $value."_".$sc[$sKey];
                    }
                    $uniq = array_unique($check);
                
                    foreach($uniq as $val) {
                        list($cat, $subcat) = explode("_", $val);
                        $check_array[$cat][] = $subcat;
                    }
                
                    foreach($check_array as $k=>$val) {
                        if(count($val) > 1 && (array_search(0, $val) !== false)) {
                            $cats[] = array('category_id' => $k, 'subcategory_id' => 0);
                            unset($check_array[$k]);
                        } else {
                            foreach($val as $m=>$v) {
                                $cats[] = array('category_id' => $k, 'subcategory_id' => $v);    
                            }
                        }
                    }
                
                    $tmpPrj->setCategories($cats);
                    
                }
//                exit(var_dump($cats));
//                $tmpPrj->setProjectField('categories', $cats);
                $tmpPrj->setProjectField('country', __paramInit('int', NULL, 'country'));
                $tmpPrj->setProjectField('city', __paramInit('int', NULL, 'city'));
            }
            $tmpPrj->setProjectField('pro_only', __paramInit('bool', NULL, 'pro_only') ? 't' : 'f');
            $tmpPrj->setProjectField('verify_only', __paramInit('bool', NULL, 'verify_only') ? 't' : 'f');
            $tmpPrj->setProjectField('strong_top', __paramInit('int', NULL, 'strong_top'));
            $tmpPrj->setProjectField('prefer_sbr', __paramInit('bool', NULL, 'prefer_sbr') ? 't' : 'f');

            $project = $tmpPrj->getProject();
            if($project['cost'] < 0)
                $error['cost'] = 'Введите положительную сумму';

            if($project['cost'] > 999999)
                $error['cost'] = 'Слишком большая сумма';

            if($project['cost']>0 && ($project['currency'] < 0 || $project['currency'] > 3))
                $error['currency'] = 'Валюта не определена';

            if(is_empty_html($project['descr']))
                $error['descr'] = 'Поле не заполнено';

            if(is_empty_html($project['name']))
                $error['name'] = 'Поле не заполнено';
            
            $descr_limit = !$PDA ? 5000 : 2500;
            if(strlen_real($project['descr']) > $descr_limit)
                $error['descr'] = "Исчерпан лимит символов ($descr_limit)";
				
			if ($project['kind'] == 7) {
				$tmpPrj->setProjectField('end_date', __paramInit('string', NULL, 'end_date'),0,64);
				$tmpPrj->setProjectField('win_date', __paramInit('string', NULL, 'win_date'),0,64);
				$project = $tmpPrj->getProject();

				if (!preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $project['end_date'], $o1) || !checkdate($o1[2], $o1[1], $o1[3]))
					$error['end_date'] = 'Неправильная дата';

				if (!preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $project['win_date'], $o2) || !checkdate($o2[2], $o2[1], $o2[3]))
					$error['win_date'] = 'Неправильная дата';
				
				// Модераторам аккуратней	
			    if(!hasPermissions('projects')) {
    				if (!$error['end_date'] && mktime(0, 0, 0, $o1[2], $o1[1], $o1[3]) <= mktime(0, 0, 0))
    					$error['end_date'] = 'Дата окончания конкурса не может находиться  в прошлом';
    			
    				if (!$error['win_date'] && mktime(0, 0, 0, $o2[2], $o2[1], $o2[3]) <= mktime(0, 0, 0, $o1[2], $o1[1], $o1[3]))
					$error['win_date'] = 'Дата определения победителя должна быть больше даты окончания конкурса';
				}
				
			}

            if(!$error) {
                if(!$PDA) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
                    $uploader = new uploader(current($_POST['IDResource']));
                    
                    $attachedfiles_files = $uploader->getFiles();
                    $tmpPrj->clearAttaches();
                    $tmpPrj->addAttachedFiles($attachedfiles_files, ($draft_id && !$is_tmp_draft ? true : false));
                    $uploader->clear();
                }

                //if($err = $tmpPrj->addAttach($_FILES['attach'])) $error['attach'] = $err;  
                
                if($PDA) {
                    // Удаление файла для ПДА версии
                    $idDel = __paramInit('int', NULL, 'atch');
                    if($idDel <= 0 && $_FILES['attach']['error'][0] == 4) {
                        $tmpPrj->delAttach(0);
                    }
                    // сохранение файла
                    if (is_array($_FILES['attachedfiles_file']) && !$_FILES['attachedfiles_file']['error']) {
                        $_POST['attachedfiles_action'] = 'add';
                        $_POST['attachedfiles_type'] = 'project';
                        ob_start();
                        include($_SERVER['DOCUMENT_ROOT'] . "/attachedfiles.php");
                        ob_clean();
                        $attachedfiles_files = $attachedfiles->getFiles(array(1,3,4));
                        $tmpPrj->addAttachedFiles($attachedfiles_files);
                        $attachedfiles->clear();
                    }
                }
            }
            
            $tmpPrj->fix();
            // Опять для ПДА
            if($action=="change" && $PDA) {
                header("Location: /public/?step={$step}{$pprm}&kind=".$project['kind']);
                exit;
            }
            if($action=="change_country" && $PDA) {
                header("Location: /public/?step={$step}{$pprm}&kind={$project['kind']}&category={$project['category']}&subcategory={$project['subcategory']}");
                exit;
            }

            $error_type = '';
            if(!$error_type && $error['attach']) {
                $error_type = 'attach';
            }
            if(!$error_type && ($error['win_date'] || $error['end_date'])) {
                $error_type = 'date';
            }

            if(!$error) {
                header('Location: /public/?step='.($error ? 1 : 2)."{$pprm}"."&draft_id=".$draft_id."&draft_prj_id={$draft_prj_id}");
                exit;
            }
        } else if ( $draft_id ) {
    
            // загрузка с черновика
            
            $draft = $drafts->getDraft($draft_id, $uid, 1);
            
            $tmpPrj->setProjectField('kind',       $draft['kind']);
            $tmpPrj->setProjectField('descr',      htmlspecialchars(addslashes($draft['descr']), ENT_QUOTES));
            $tmpPrj->setProjectField('name',       htmlspecialchars(addslashes($draft['name']), ENT_QUOTES));
            $tmpPrj->setProjectField('cost',       $draft['cost']);
            $tmpPrj->setProjectField('currency',   $draft['currency']);
            $tmpPrj->setProjectField('priceby',    $draft['priceby']);
            $tmpPrj->setProjectField('pro_only',   $draft['pro_only']);
            $tmpPrj->setProjectField('verify_only',$draft['verify_only']);
            $tmpPrj->setProjectField('strong_top', $draft['strong_top']);
            $tmpPrj->setProjectField('prefer_sbr', $draft['prefer_sbr']);
            $tmpPrj->setProjectField('budget_type', $draft['budget_type']);
            
            if ( $draft['kind'] == 4 ) {
                $tmpPrj->setProjectField('country', $draft['country']);
                $tmpPrj->setProjectField('city',    $draft['city']);
            }

            if ( $draft['kind'] == 7 ) {
                $tmpPrj->setProjectField('win_date', $draft['p_win_date']);
                $tmpPrj->setProjectField('end_date', $draft['p_end_date']);
            }

            if ( !empty($draft['categories']) ) {
                $c = explode(',', $draft['categories']);
                $cats = array();
                foreach ( $c as $v ) {
                    $p = explode('|', $v);
                    $cats[] = array('category_id' => $p[0], 'subcategory_id' => $p[1]);
                }
                if ( $cats ) {
                    $tmpPrj->setCategories($cats);
                }
            }
    
        }
        if(!$uploader) {
            $uploader = new uploader(uploader::createResource('project'));
        }
        
        if(!$_POST['IDResource'] && $draft_id) {
            $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($draft_id, 4);
            if($attachedfiles_tmpdraft_files) {
                $attachedfiles_draft_files = array();
                foreach($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
                    $attachedfiles_draft_files[] = $attachedfiles_draft_file;
                }
                $uploader->setFiles($attachedfiles_draft_files, uploader::STATUS_ADDED);
            }
        } else {
            $attachedfiles_tmpprj_files = $tmpPrj->getAttach();
            if($attachedfiles_tmpprj_files) {
                $attachedfiles_prj_files = array();
                foreach($attachedfiles_tmpprj_files as $attachedfiles_prj_file) {
                    $attachedfiles_prj_files[] = $attachedfiles_prj_file['file_id'];
                }
                $set = $uploader->setFiles($attachedfiles_prj_files, $draft_id ? uploader::STATUS_ADDED : uploader::STATUS_CREATE);
            }
        }
        $attachedfiles_files = $uploader->getFiles();

        $content = "content1.php";
        break;

    case 2:
        if(!$tmpPrj->isEdit() && ($project['descr']==='' || $project['name']==='')) { // сразу на шаг 2 нельзя, когда публикуется.
            header("Location: /public/?step=".(isset($project['kind']) ? 1 : 0))."{$pprm}";
            exit;
        }

        if($action=='save'||$action=='bill'||$action=='prev'||$action=='reload') {
            $tmpPrj->setProjectField('is_color', __paramInit('bool', NULL, 'is_color') ? 't' : 'f');
            $tmpPrj->setProjectField('is_bold', __paramInit('bool', NULL, 'is_bold') ? 't' : 'f');
            $tmpPrj->setProjectField('link', substr(__paramInit('string', NULL, 'link'),0,100));
            $tmpPrj->setAddedTopDays(__paramInit('bool', NULL, 'top_ok') ? __paramInit('int', NULL, 'top_days',0) : 0);
            
            if(__paramInit('bool', NULL, 'logo_ok')) {
                if(!$_FILES['logo']['size'])
                    $error['logo'] = 'Необходимо выбрать файл';
                elseif($err = $tmpPrj->setLogo(new CFile($_FILES['logo'])))
                    $error['logo'] = $err;
            }
            
            if(!$error && $action!='prev' && $action!='reload') {
            	//для пользователя
                $price = $tmpPrj->getPrice($payedItems);
                $account_sum = $account->sum;
                $account_bonus_sum = $account->bonus_sum;
                //для модератора
                $view_user_login = '';
                if (hasPermissions("projects")) {
                	$uid      = $tmpPrj->getAuthorId();
                	$view_account = new account();
                	$view_account->GetInfo($uid);
                	$account_sum = $view_account->sum; 
                	$account_bonus_sum    = $view_account->bonus_sum;
                	$view_user_login =   $tmpPrj->getAuthorLogin();                	 
                }
                if($action=='save' && ($account_sum >= $price || $account_bonus_sum >= $price)) {
                    if ($PDA && !$tmpPrj->getCategories()) {
                        $cats = array();
                        $cats[] = array('category_id' => $project['category'], 'subcategory_id' => $project['subcategory']);
                        $tmpPrj->setCategories($cats);
                    }
                    if(!($error['buy'] = $tmpPrj->saveProject(hasPermissions('projects') ? $uid : NULL, $proj))) {
                    	if (hasPermissions("projects")) {
                    		$inspect_user_session = new session();                    		
	                		$inspect_user_session->UpdateProEndingDate($view_user_login);
                    	}
                		
                        $back = $backLink ? $backLink : '/';
                        $drafts->DeleteDraft($draft_id, $uid, 1);
                        
                        if($price) {
                            // в PDA без страниц-прокладок
                            if ($PDA) {
                                if($back == '/') $back = false;
                                $_SESSION['bill.GET']['back'] = $back;
                                header("Location: /bill/success/");
                                exit;
                            }
                            // платные опции
                            $payedParams = "";
                            foreach ($payedItems as $name=>$sum) {
                                if ($sum > 0) {
                                    $payedParams .= "&" . $name . "=" . $sum;
                                }
                            }
                            $payedParams .= "&top_days=" . __paramInit('int', NULL, 'top_days',0);
                            $params = "?prj_id=" . $proj['id'] . $payedParams;
                            // если конкурс
                            if ($proj['kind'] == 7) {
                                $contest = "&contest=" . (is_pro() ? 100 : 110);
                                header("Location: /public/contest_published.php/" . $params . $contest . $editPrj);
                            } else {
                                if($proj['kind'] == 4 && !$tmpPrj->isEdit()) {
                                    $inoffice = "&inoffice=".new_projects::getProjectInOfficePrice();
                                }
                                header("Location: /public/payed_project_published.php/" . $params . $inoffice . $editPrj);
                                //header("Location: /bill/success/");
                            }
                        } else {
                            if ($draft_prj_id) {
                                header("Location: {$back}");
                            } else {
                                // в PDA без страниц прокладок
                                if ($PDA) {
                                    header("Location: {$back}");
                                } else {
                                    header("Location: /public/project_published.php?prj_id=" . $proj['id'] . $editPrj);
                                }
                            }
                        }
                        exit;
                    }
                }
                else {
                    if(!$PDA) {
                        $tmpPrj->fix();
                        //print(__paramInit('bool', NULL, 'logo_ok'));
                        header("Location: /bill/?paysum=".ceil($price - $account->sum));
                        exit;
                    } else {
                        $error['buy'] = 'На вашем счету не хватает '.round($price - $account->sum, 2). ' ' . ending(round($price - $account->sum), 'рубль', 'рубля', 'рублей');
                    }
                }
            }
            
            $tmpPrj->push('error', $error);
            $tmpPrj->fix();
            
            // Для обработки в ПДА
            if(!$error && $action == 'reload' && $PDA) {
                $price = $tmpPrj->getPrice();
                header('Location: /public/?step='.($action!='prev' || $error ? 3 : 2)."{$pprm}" );
                exit;   
            }

            //header( 'Location: /public/?step='.($action!='prev' || $error ? 2 : (int)$tmpPrj->isEdit())."{$pprm}" );
            header( 'Location: /public/?step='.($action!='prev' || $error ? 2 : 1)."{$pprm}"."&draft_id={$draft_id}&draft_prj_id={$draft_prj_id}" );
            exit;
        }
        else if($action=='del_logo') {
            $tmpPrj->delLogo();
            $tmpPrj->fix();
            header( "Location: /public/?step=2{$pprm}"."&draft_id={$draft_id}&draft_prj_id={$draft_prj_id}" );
            exit;
        }
        $content = "content2.php";
        break;
    case 3: // Шаг для ПДА версии
        
        if(!$tmpPrj->isEdit() && ($project['descr']==='' || $project['name']==='')) { // сразу на шаг 2 нельзя, когда публикуется.
            header("Location: /public/?step=".(isset($project['kind']) ? 1 : 0)."{$pprm}");
            exit;
        }
        
        if($action=='prev'||$action=='save') {
            if(!$error && $action!='prev' && $action!='reload') {
                $price = $tmpPrj->getPrice();
                if($action=='save' && ($account->sum >= $price || $account->bonus_sum >= $price)) {
                    if(!($error['buy'] = $tmpPrj->saveProject(hasPermissions('projects') ? $uid : NULL))) {
                        $back = $backLink ? $backLink : '/';
                        if($price) {
                            $_SESSION['bill.GET']['back'] = $back;
                            header("Location: /bill/success/");
                        } else {
                            header("Location: {$back}");
                        }
                        exit;
                    }
                }
                else {
                    $tmpPrj->fix();
                    header("Location: /bill/?paysum=".ceil($price - $account->sum));
                    exit;
                }
            }

            $tmpPrj->push('error', $error);
            $tmpPrj->fix();
            //header( 'Location: /public/?step='.($action!='prev' || $error ? 2 : (int)$tmpPrj->isEdit())."{$pprm}" );
            header( 'Location: /public/?step='.($action!='prev' || $error ? 3 : 2)."{$pprm}" );
            exit;
        }

        $content = "content3.php";
        break;    
    case 0:
    default:
        $content = "content0.php";
        break;
}


// Все изменения $tmpPrj->_project переносим в переменную.
$project = $tmpPrj->getProject();
if($step > 0)
    $tmpPrj->fix();
if(!$additional_header) $additional_header = '';
$additional_header .= '<script type="text/javascript" src="/scripts/tawl_bem.js"></script>';
    
if($content == 'content2.php') {
    $additional_header .= '<script type="text/javascript" src="/css/block/b-shadow/b-shadow.js"></script>';
}
else if($content == 'content1.php') {
    $use_draft = ($project['user_id'] == get_uid(false));
    if($use_draft) {
        $additional_header .= '<script type="text/javascript" src="/scripts/drafts.js"></script>';
    }
    $additional_header .= '<script type="text/javascript" src="/scripts/projects.js"></script>';
    if ($project['kind'] == 2 || $project['kind'] == 7) {
        $additional_header .= '<script type="text/javascript" src="/scripts/calendar.js"></script>';
    }
    $additional_header .= '<script type="text/javascript" src="/scripts/attachedfiles.js"></script>';
    $additional_header .= '<script type="text/javascript" src="/scripts/uploader.js"></script>';
}
    
    
include("../template2.php");

?>
