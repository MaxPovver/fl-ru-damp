<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/temp_email.php");
if(!$uid) {
    session_start();
    $uid = get_uid();
}
$g_page_id = "0|23";
$stretch_page = true;
$showMainDiv  = true;
$template = "template.php";

$action = __paramInit('string', 'action');

if ( $action != 'prj_up' && $action != 'prj_top' ) {
    $action = __paramInit('string', NULL, 'action');
}

if(strtolower(trim($_GET['user']))==strtolower($_SESSION['login']) && is_pro(true)) { $no_adv = true; } else { $no_adv = false; }

$js_file  = array( 'warning.js', 'note.js', 'status.js', 'banned.js', 'tawl.js', 'raphael-min.js', 'svg.js', 
    'paid_advices.js', '/css/block/b-filter/b-filter.js', '/css/block/b-fon/b-fon.js', '/css/block/b-layout/b-layout.js', 
    'del_acc.js', 'kwords.js', 'sbr.js', 'specadd.js', 'drafts.js', 'polls.js', 'mAttach.js', 'blogs_cnt.js', 'blogs.js', 
    'opinions.js', '/kword_js.php' );

switch ($page){
	case "info": $inner = "inform_inner.php"; $activ_tab = 2; break;
	case "mailer": $inner = "mailer_inner.php"; $activ_tab = 0; break;
	case "foto": $inner = "../../setup/foto_inner.php"; $activ_tab = 0; break;
	case "delete": $inner = "../../setup/tpl.delete.php"; $activ_tab = 0; break;
	case "projects": $inner = "projects_inner.php"; $activ_tab = 1; break;
	case "pwd":  $inner = "../../setup/pwd_inner.php"; $activ_tab = 0; break;
	case "tabssetup": $inner = "list_inner.php"; $activ_tab = 0; break;
	case "safety":
        include('safety_action.php');
        $inner = "../../setup/safety_inner.php"; 
        $activ_tab = 0; 
        break;
    
	case "finance":
            $bIsYdVerified = employer::isYdVerified($uid);
        $js_file[] = 'attachedfiles2.js';
        include('finance_action.php');
        $prefix = (isset($is_finance_deleted) && $is_finance_deleted)?'_deleted':'';
        $inner = "../../setup/finance{$prefix}_inner.php";
        $activ_tab = 5;  
        break;
    
    case "main":
    default:        
        require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sms_gate_a1.php");
        $u = new users;
        $o_only_phone = $u->GetField($uid,$ee,'safety_only_phone');
        
        $reqv = sbr_meta::getUserReqvs($uid);
        $ureqv = $reqv[$reqv['form_type']];
        
        $social_bind_error = isset($_SESSION['opauth_error']) ? $_SESSION['opauth_error'] : '';
        unset($_SESSION['opauth_error']);
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opauth/OpauthModel.php");
        $opauthModel = new OpauthModel();
        $social_links = $opauthModel->getUserLinks($uid);

        $js_file[] = '/scripts/b-combo/b-combo-phonecodes.js';
        $inner = "../../setup/main_inner.php"; 
        $activ_tab = 0; 
        break;
}

require_once (ABS_PATH."/classes/op_codes.php");
$op_codes = new op_codes();
$pProjCost = $op_codes->GetField(8,$error,"sum");

$content = "content.php";


switch ($action){
    case "save_phone":
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        
        $reqv = sbr_meta::getUserReqvs(get_uid(false));
        $ureqv = $reqv[$reqv['form_type']];
        
        $uid   = get_uid(false);
        $code  = __paramInit('int', null, 'smscode');
        $phone = __paramInit('string', null, 'mob_phone');
        $type  = __paramInit('string', null, 'type');
        if($type == 'unbind') {
            $phone = $ureqv['mob_phone']; 
        }
       
        if ( $code == $_SESSION['send_sms_code'] && ( $phone == $_SESSION['send_sms_phone'] ) )  {
            unset($_SESSION['send_sms_code'], $_SESSION['send_sms_phone']);
            $user = new users();
            
            $ureqv['mob_phone']     = $phone;
            $save_reqv['mob_phone'] = $phone;
            
            if($type == 'bind') {
                if( !($text_error = sbr_meta::setUserReqv($uid, $reqv['rez_type'], $reqv['form_type'], $save_reqv)) ) {
                    sbr_meta::authMobPhone($uid, true);
                    $reqv['is_activate_mob'] = 't';
                    $error_phone = false;
                    unset($_SESSION["unbind_phone_action"]);
                } else {
                    $error_phone['phone'] = current($text_error); // Телефон забит
                }
            } else if($type == 'unbind') { // Сбрасываем все
                $phone = '';
                $save_reqv['mob_phone'] = '';
                $ureqv['mob_phone']     = '';
                sbr_meta::setUserReqv($uid, $reqv['rez_type'], $reqv['form_type'], $save_reqv); // Удаляем телефон
                sbr_meta::authMobPhone($uid, false);
                sbr_meta::safetyMobPhone($uid, false);
                $user->updateSafetyPhone($uid, false);
                $reqv['is_activate_mob'] = 'f';
                $_SESSION["unbind_phone_action"] = true;
            }
        } else {
            $error_phone['code'] = true;
        }
        
        break;
    case "save_safety":
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        $post_safety_phone = true;
        
        $uid   = get_uid(false);
        $code  = __paramInit('int', null, 'smscode');
        $phone = $ureqv['mob_phone']; 
       
        $user = new users();
        
        $only_phone           = __paramInit('string', NULL, 'only_phone', 'f'); 
        $finance_safety_phone = __paramInit('string', NULL, 'finance_safety_phone', 'f');
        
        if( ($o_only_phone == 't' || $reqv['is_safety_mob'] == 't') && $code == $_SESSION['send_sms_code'] && ( $phone == $_SESSION['send_sms_phone'] ) ) {
            $safety_complete       = true;
            $o_only_phone          = $only_phone;
            $reqv['is_safety_mob'] = $finance_safety_phone;
            $user->updateSafetyPhone($uid, $only_phone == 't' ? true : false);
            sbr_meta::safetyMobPhone($uid, $finance_safety_phone == 't' ? true : false);
            unset($_SESSION['send_sms_code'], $_SESSION['send_sms_phone']);
        } elseif( ( ($o_only_phone == 't' && $o_only_phone != $only_phone) || ( $reqv['is_safety_mob'] == 't' && $reqv['is_safety_mob'] != $finance_safety_phone ) ) ) {
            $error_phone['code'] = true;
        }
        
        // Включение, без проверки СМС
        if($o_only_phone == 'f' && $only_phone == 't' && !$safety_complete) {
            $o_only_phone = 't';
            $user->updateSafetyPhone($uid, true);
        }
        if($reqv['is_safety_mob'] == 'f' && $finance_safety_phone == 't' && !$safety_complete) {
            $reqv['is_safety_mob'] = 't';
            sbr_meta::safetyMobPhone($uid, true);
        }
        
        
        break;
    case "safety_update":
        require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
        $reqv = sbr_meta::getUserReqvs(get_uid(false));
        $ureqv = $reqv[$reqv['form_type']];

        $ip_addresses = change_q(trim(stripslashes($_POST['ip_addresses'])),true);
        $bind_ip = $_POST['bind_ip'];
        if($bind_ip!='t') $bind_ip = 'f';
        $password = trim(stripslashes($_POST['password']));
        
        $error_flag = 0;
        
        $frl = new users();
        
        // Проверям IP
        $c_ip = $frl->CheckSafetyIP($ip_addresses);
        $ip_addresses = $c_ip['ip_addresses'];
        $alert[1] = $c_ip['alert'][1];
        $error_flag = $c_ip['error_flag'];
        
        // Проверям правильность пароля
        $current_password = $frl->GetField($uid,$ee,'passwd');
        if(users::hashPasswd($password)!=$current_password) {
            $error_flag = 1;
            $alert[3] = "Вы ввели неправильный пароль";
        }        
        $password = '';
		if (!$error_flag) {
            $info_msg = "Изменения внесены";
            $_SESSION['info_msg'] = $info_msg;
            $frl->UpdateSafetyInformation($uid,$ip_addresses,$bind_ip);
            header_location_exit("/users/{$_SESSION['login']}/setup/safety/");
        } else {
            $_SESSION['alert'] = $alert;
        }
        $ip_addresses = change_q(trim(stripslashes($_POST['ip_addresses'])),true);
        break;
    
    case "safety_social":
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opauth/OpauthModel.php");
        $opauthModel = new OpauthModel();
            
        $status = __paramInit('string', null, 'status');
        if ($status == 'off') {
            $opauthModel->removeMultilevel($uid);
            header_location_exit("/users/{$_SESSION['login']}/setup/safety/");
        } else {
            $provider_type = __paramInit('string', null, 'type');
            $password = __paramInit('string', null, 'oldpwd');
            $error_flag = 0;
            
            if (!$provider_type) {
                $error_flag = 1;
                $alert[4] = "Вы не указали соцсеть";
            }
            
            $user = new users();
            // Проверям правильность пароля
            if (users::hashPasswd($password) != $user->GetField($uid, $ee, 'passwd')) {
                $error_flag = 1;
                $alert[5] = "Вы ввели неправильный пароль";
            }

            if (!$error_flag) {
                $info_msg = "Изменения внесены";
                $_SESSION['info_msg'] = $info_msg;
                $opauthModel->addMultilevel($uid, $provider_type);
                header_location_exit('/users/'. $_SESSION['login'] .'/setup/safety/');
            } else {
                $_SESSION['alert'] = $alert;                
            }
        }
        
        break;
        
	case "tabs_change":
		$prjs = trim($_POST['prjs']);
		$info = trim($_POST['info']);
		$jornal = trim($_POST['jornal']);
		$emp = new employer;
        $error .= $emp->UpdateTabs($uid, $prjs, $info, $jornal, 0, 0);
		if (!$error) $info_msg = "Изменения внесены";
		break;

	case "update_subscr":
		$newmsgs = trim($_POST['newmsgs']);
		$vacan = trim($_POST['vacan']);
		$comments = trim($_POST['comments']);
		$opin = trim($_POST['opin']);
		$commune_subscr = __paramInit('bool', NULL, 'commune');
		$commune_top_subscr = __paramInit('bool', NULL, 'commune_topics');
		$articlescomments = trim($_POST['articlescomments']);
		$paid_advice = 0;//trim($_POST['paid_advice']);
        
        // сообщества
		$comm = !empty($_POST['comm']) ? array_map('intvalPgSql', $_POST['comm']) : false;
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
        if (!empty($_POST['commune_topics'])){
            $cm = new commune();
            $cm->clearSubscription($uid);
            $cm->setCommunesSubscription($comm,$uid,true);
        } else {
            $cm = new commune();
            $cm->clearSubscription($uid);
        }
 
		$frl = new users;
		$error .= $frl->UpdateSubscr2(
                        get_uid(),array(
                        $newmsgs,
                        $vacan,
                        $comments,
                        $opin,
                        __paramInit('bool', NULL, 'prj_comments'), 
                        $commune_subscr, 
                        $commune_top_subscr, 
                        __paramInit('bool', NULL, 'adm_subscr'), 
                        __paramInit('bool', NULL, 'contest'), 
                        __paramInit('bool', NULL, 'team'),
                        0, 
                        $articlescomments, 
                        __paramInit('bool', NULL, 'spm'),
                        0, 
                        $paid_advice,
                        __paramInit('bool', NULL, 'payment')));
		
                if (!$error) $info_msg = "Изменения внесены";
		
                break;
	case "inform_change":
        foreach($_POST as $key=>$value) {
	        if(!is_array($value)) $_POST[$key] = stripslashes($value);
	    }
		$frl = new employer();
		if (trim($_POST['datey']) && trim($_POST['dated']))
		{
			if (checkdate(intval(trim($_POST['datem'])), intval(trim($_POST['dated'])), intval(trim($_POST['datey']))))
			$frl->birthday = date("Y-m-d", strtotimeEx(trim($_POST['datey'])."-".trim($_POST['datem'])."-".trim($_POST['dated'])));
			else {$error_flag = 1; $alert[1] = "Поле заполнено некорректно";}
		}
		else
		{
			//$frl->birthday = "1910-01-01";
            $error_flag = 1; $alert[1] = "Поле заполнено некорректно";
		}
		if (!$alert[1] && $frl->birthday && (date("Y", strtotime($frl->birthday)) >= date("Y"))) {$error_flag = 1; $alert[1] = "Поле заполнено некорректно";}
		$frl->country = intval(trim($_POST['country']));
		$frl->city = intval(trim($_POST['pf_city']));
        if($frl->country <= 0) {
            $error_flag = 1;
            $alert['country'] = 'Выберите страну';
        }
        if($frl->city <= 0) {
            $error_flag = 1;
            $alert['city'] = 'Выберите город';
        }
        if($frl->site = change_q(substr(addhttp(trim($_POST['site'])), 0, 96), true))
            if ( !url_validate($frl->site, true)) {
                $error_flag = 1; $alert[11] = "Поле заполнено некорректно";
            }
        // more site
        if($frl->site_1 = change_q(substr(addhttp(trim($_POST['site_1'])), 0, 96), true))
            if ( !url_validate($frl->site_1, true)) {
                $error_flag = 1; $alert[41] = "Поле заполнено некорректно";
            }
        if($frl->site_2 = change_q(substr(addhttp(trim($_POST['site_2'])), 0, 96), true))
            if ( !url_validate($frl->site_2, true)) {
                $error_flag = 1; $alert[42] = "Поле заполнено некорректно";
            }
        if($frl->site_3 = change_q(substr(addhttp(trim($_POST['site_3'])), 0, 96), true))
            if ( !url_validate($frl->site_3, true)) {
                $error_flag = 1; $alert[43] = "Поле заполнено некорректно";
            }
        // more site


		$frl->icq = substr(strip_tags(trim($_POST['icq'])),0,96);
    if(strlen($frl->jabber = __paramInit('string', NULL, 'jabber','')) > 3071) {
      $error_flag = 1;
      $alert['jabber'] = "Количество знаков превышает допустимое значение";
    }
        // more jabber
        if(strlen($frl->jabber_1 = __paramInit('string', NULL, 'jabber_1','')) > 3071) {
          $error_flag = 1;
          $alert['51'] = "Количество знаков превышает допустимое значение";
        }
        if(strlen($frl->jabber_2 = __paramInit('string', NULL, 'jabber_2','')) > 3071) {
          $error_flag = 1;
          $alert['52'] = "Количество знаков превышает допустимое значение";
        }
        if(strlen($frl->jabber_3 = __paramInit('string', NULL, 'jabber_3','')) > 3071) {
          $error_flag = 1;
          $alert['53'] = "Количество знаков превышает допустимое значение";
        }
        // more jabber

        $frl->sex = (int)$_POST['sex'] == 1 ? 't' : 'f'; 
    
        if($frl->second_email = change_q(substr(trim($_POST['second_email']), 0, 96), true)) {
            if (!is_email($frl->second_email)) {
                $error_flag = 1; $alert[10] = "Поле заполнено некорректно"; 
            }
            //$frl->email_as_link = $_POST['email_as_link'];
        }

        if($frl->email_1 = change_q(substr(trim($_POST['email_1']), 0, 96), true)) {
            if (!is_email($frl->email_1)) {
                $error_flag = 1; $alert[21] = "Поле заполнено некорректно"; 
            }
            //$frl->email_1_as_link = $_POST['email_1_as_link'];
        }
        if($frl->email_2 = change_q(substr(trim($_POST['email_2']), 0, 96), true)) {
            if (!is_email($frl->email_2)) {
                $error_flag = 1; $alert[22] = "Поле заполнено некорректно"; 
            }
            //$frl->email_2_as_link = $_POST['email_2_as_link'];
        }
        if($frl->email_3 = change_q(substr(trim($_POST['email_3']), 0, 96), true)) {
            if (!is_email($frl->email_3)) {
                $error_flag = 1; $alert[23] = "Поле заполнено некорректно"; 
            }
            //$frl->email_3_as_link = $_POST['email_3_as_link'];
        }


		if($frl->icq = change_q(substr(strip_tags(trim($_POST['icq'])),0,96),true))
		  if (!preg_match("/^[-0-9\s]*$/", $frl->icq) && !is_email($frl->icq)) { $error_flag = 1; $alert[2] = "Поле заполнено некорректно"; }
		if($frl->icq_1 = change_q(substr(strip_tags(trim($_POST['icq_1'])),0,96), true))
		  if (!preg_match("/^[-0-9\s]*$/", $frl->icq_1) && !is_email($frl->icq_1)) { $error_flag = 1; $alert[31] = "Поле заполнено некорректно"; }
		if($frl->icq_2 = change_q(substr(strip_tags(trim($_POST['icq_2'])),0,96), true))
		  if (!preg_match("/^[-0-9\s]*$/", $frl->icq_2) && !is_email($frl->icq_2)) { $error_flag = 1; $alert[32] = "Поле заполнено некорректно"; }
		if($frl->icq_3 = change_q(substr(strip_tags(trim($_POST['icq_3'])),0,96), true))
		  if (!preg_match("/^[-0-9\s]*$/", $frl->icq_3) && !is_email($frl->icq_3)) { $error_flag = 1; $alert[32] = "Поле заполнено некорректно"; }


		if($frl->phone = change_q(substr(strip_tags(trim($_POST['phone'])),0,24), true))
		  if (!preg_match("/^[-+0-9)( #]*$/", $frl->phone)) { $error_flag = 1; $alert[3] = "Поле заполнено некорректно"; }
        // more phone
        if($frl->phone_1 = change_q(substr(trim($_POST['phone_1']), 0, 24), true))
        	  if (!preg_match("/^[-+0-9)( #]*$/", $frl->phone_1)) { $error_flag = 1; $alert[61] = "Поле заполнено некорректно"; }
        if($frl->phone_2 = change_q(substr(trim($_POST['phone_2']), 0, 24), true))
        	  if (!preg_match("/^[-+0-9)( #]*$/", $frl->phone_2)) { $error_flag = 1; $alert[62] = "Поле заполнено некорректно"; }
        if($frl->phone_3 = change_q(substr(trim($_POST['phone_3']), 0, 24), true))
        	  if (!preg_match("/^[-+0-9)( #]*$/", $frl->phone_3)) { $error_flag = 1; $alert[63] = "Поле заполнено некорректно"; }
        // more phone


		if (strlen($_POST['resumetxt']) > 4000)
		{
			$error_flag = 1; $alert[5] = "Количество знаков превышает допустимое значение";
		}
		else
		{
                    $frl->resume = antispam(__paramInit('html', null, 'resumetxt', '', 4000));
		}

		if (strlen($_POST['companytxt']) > 500)
		{
			$error_flag = 1; $alert[6] = "Количество знаков в тексте о компании превышает допустимое значение";
		}
		else
		{
                    $frl->company = antispam(__paramInit('html', null, 'companytxt', '', 500));
		}



		$frl->blocks = '1'.intval(trim($_POST['showteam'])).intval(trim($_POST['showcommune']))
		.intval(trim($_POST['showjoincommune'])).intval(trim($_POST['showempl']))
		.intval(trim($_POST['showfrl']));
		
		
		$frl->compname =  __paramInit('string', NULL, 'compname','');
		$frl->ljuser = change_q(substr(strip_tags(trim($_POST['ljuser'])),0,64), true);
        // more lj
		$frl->lj_1 = change_q(substr(trim($_POST['lj_1']), 0, 64), true);
        if (!preg_match("/^[a-zA-Z0-9_-]*$/", $frl->lj_1)) { $error_flag = 1; $alert[71] = "Поле заполнено некорректно"; }
		$frl->lj_2 = change_q(substr(trim($_POST['lj_2']), 0, 64), true);
        if (!preg_match("/^[a-zA-Z0-9_-]*$/", $frl->lj_2)) { $error_flag = 1; $alert[72] = "Поле заполнено некорректно"; }
		$frl->lj_3 = change_q(substr(trim($_POST['lj_3']), 0, 64), true);
        if (!preg_match("/^[a-zA-Z0-9_-]*$/", $frl->lj_3)) { $error_flag = 1; $alert[73] = "Поле заполнено некорректно"; }
        // more lj


		$frl->skype = change_q(substr(trim($_POST['skype']),0,64), true);
		//$frl->skype_as_link = $frl->skype && $_POST['skype_as_link'];
		$frl->skype_1 = change_q(substr(trim($_POST['skype_1']),0,64), true);
        //$frl->skype_1_as_link = $frl->skype_1 && $_POST['skype_1_as_link'];
		$frl->skype_2 = change_q(substr(trim($_POST['skype_2']),0,64), true);
       // $frl->skype_2_as_link = $frl->skype_2 && $_POST['skype_2_as_link'];
		$frl->skype_3 = change_q(substr(trim($_POST['skype_3']),0,64), true);
       // $frl->skype_3_as_link = $frl->skype_3 && $_POST['skype_3_as_link'];

		$frl->info_for_reg = serialize($_POST['info_for_reg']);

        if (!preg_match("/^[a-zA-Z0-9_-]*$/", $frl->ljuser)) { $error_flag = 1; $alert[12] = "Поле заполнено некорректно"; }


		if ((isset($_FILES['logo']) || isset($_POST['del']) && $_POST['del'] == 1) && !$error_flag ) {
			$logo = new CFile($_FILES['logo']);
			$del = intval($_POST['del']);
			if ($logo->name || $del == 1){
				$error .= $frl->UpdateLogo(get_uid(),$logo, $del);
				if (!$error) $info_msg = "Изменения внесены";
				else $error .= "Файл не удовлетворяет условиям загрузки. ";
			}
		}

		if (!$error_flag) {
			//print_r($frl);
			$error .= $frl->UpdateInform(get_uid());
            // Доступ пользователя к функциям сайта
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
            $reg    = new registration();
            $reg->checkUserAccess(get_uid(), true);
			$selected = $_POST['id'];
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
			$error .= teams::teamsDelFavoritesExcept(get_uid(), $selected);
		}
		if (!$error && !$error_flag) {
            //$info = "Изменения внесены";
            header_location_exit( '/users/'. $_SESSION['login'] .'/setup/info/?msg=1' );
        }
		break;
	case "main_change":
		$name = change_q(substr(trim($_POST['name']),0,21), true);
        $surname = change_q(substr(trim($_POST['surname']),0,21), true);
        $email = change_q(substr(trim($_POST['email']),0,64), true);
        $pname = change_q(substr(trim(stripslashes($_POST['pname'])),0,100), true);
        $oldpwd = trim($_POST['oldpwd']);
        $question_button_hide = intval($_POST['consultant_show']);
        $promo_block_hide = intval($_POST['promo_show']);
        $direct_links = intval($_POST['direct_links']);
        setlocale(LC_ALL, 'ru_RU.CP1251');
        if (!preg_match("/^[-a-zA-Zа-яёА-ЯЁ]+$/", $name)) { $error_flag = 1; $alert[1] = "Поле заполнено некорректно"; }
        if (!preg_match("/^[-a-zA-Zа-яёА-ЯЁ]+$/", $surname)) { $error_flag = 1; $alert[2] = "Поле заполнено некорректно"; }
        if (!is_email($email)) { $error_flag = 1; $alert[3] = "Поле заполнено некорректно"; }
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
        $user = new employer();
        $user->GetUser($_SESSION['login']);
        
        $sOldName    = $user->uname;
        $sOldSurname = $user->usurname;
        
        if ($email != $user->email) {$ch_mail = 1;}
	if ($user->CheckEmail($email)) { $error_flag = 1; $alert[3] = "Извините, такой электронный ящик уже существует"; }
    if (temp_email::isTempEmail($email)) {
        $error_flag = 1;
        $alert[3] = "Извините, но почтовые адреса с этого домена запрещены к регистрации";
    }
        $frl = new employer;
        $err = $frl->UpdateMain(get_uid(), $name, $surname, $user->email, $oldpwd, $pname, $error_flag);
        
        if ( !$err && !$error_flag ) { // все что нужно после успешного обновления:
            if ( users::isSuspiciousUser(get_uid(), $_SESSION['login'], $_SESSION['login'], $name, $sOldName, $surname, $sOldSurname) ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php' );
                $smail = new smail();
                $smail->adminNewSuspectUser( $_SESSION['login'], $name, $surname );
            }
            
            // галки поля settings
            $frl->updateMainSettings( $uid, $question_button_hide, $promo_block_hide, $direct_links );
            $frl->setPromoBlockShowCookie( $uid, $promo_block_hide );
        }
        
        if ($err == 1) $alert[4] = "Поле заполнено некорректно"; 
        if (!$err && !$error_flag) $info = "Изменения внесены";
        setlocale(LC_ALL, 'en_US.UTF-8');
        if ($ch_mail && !$err && !$error_flag) { 
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/activate_mail.php");
			$code = activate_mail::Create(get_uid(), $email, $err);
			if ($code){
				$smail = new smail();
				$smail->ConfirmNewEmail($_SESSION['login'], $email, $code);
				$inner = "../../setup/chmail_inner.php";
			}
		if ($err) { $alert[3] = "Извините, такой электронный ящик уже существует"; $info = "";}
			$activ_tab = 0;
		}
		break;

	case "pwd_change":
		$oldpwd = trim($_POST['oldpwd']);
        $pwd  = trim(stripslashes($_POST['pwd']));
		$pwd2 = trim(stripslashes($_POST['pwd2']));
        
        if (!preg_match('/^[a-zA-Z\d\!\@\#\$\%\^\&\*\(\)\_\+\-\=\;\,\.\/\?\[\]\{\}]+$/', $pwd)) {
            $error_flag = 1; 
            $alert[3] = "Пароль содержит недопустимые символы.<br>Пожалуйста, используйте только латинские буквы, "
                . "цифры и следующие спецсимволы: !@#$%^&*()_+-=;,./?[]{}";
        }
        if ((strlen($pwd) < 6)) { $error_flag = 1; $alert[2] = "Слишком короткий пароль (минимум — 6 символов)";}
        if ((strlen($pwd) > 24)) { $error_flag = 1; $alert[2] = "Слишком длинный пароль (максимум — 24 символа)";}
        if ($pwd != $pwd2 || $pwd2 == '') {$error_flag = 1; $alert[3] = "Поле заполнено некорректно";}
        
		if (!$error_flag){
			$frl = new employer;
			$alert[1] = $frl->UpdatePwd(get_uid(), $oldpwd, $pwd, 0);
			if (!$alert[1]) {
				$info = "Изменения внесены";
				$smail = new smail();
				$smail->ChangePwd($uid, $pwd);
                
                // Пишем в лог смены паролей
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/restorepass_log.php");
                restorepass_log::SaveToLog($uid, getRemoteIP(), 3);                
			}
		}
		break;
	case "foto_change":
		$foto = new CFile($_FILES['foto']);
		$del = trim($_POST['del']);
		$frl = new employer;
		if ($foto->name || $del == 1){
			$error .= $frl->UpdateFoto(get_uid(),$foto, $del);
			/*if (!$error) $info_msg = "Изменения внесены";
			else $error = "Файл не удовлетворяет условиям загрузки";*/
            if(!$error) {
                $_SESSION['photo'] = $frl->photo;
            }
            $nParam = (!$error) ? 1 : 2;
            header_location_exit( '/users/'. $_SESSION['login'] .'/setup/foto/?msg='.$nParam );
		}
		break;
	//Удаление аккаунта убрано! не раскоменчивать!
	/*case "delete":
		$passwd = trim($_POST['passwd']);
		if ($passwd){
			$frl = new employer;
			if ($frl->DeleteUser(get_uid(), $passwd, $error)){
				logout();
				$content = $rpath."deleted_inner.php";
			} else $error = "Поле заполнено некорректно";
		} else $error = "Поле заполнено некорректно";
		break;*/
	case "prj_change":
		include("newproj.php");
		break;
	case "prj_delete":
    $prj_id = (int)trim($_POST['prjid']);
    $prj = new projects();
    $prj->DeletePublicProject($prj_id,get_uid());
		break;
	case "prj_close":
		$prj_id = (int)trim($_POST['prjid']);
		$portf = new projects();
		if ($prj_id) $error .= $portf->SwitchStatusPrj(get_uid(), $prj_id);
                    
		$location  = "/users/{$_SESSION['login']}/setup/projects/?p=list&fld={$_POST['fld']}&open={$_POST['open']}";
//		$location .= ($_POST['openclose']==1) ? '?open=1' : ( ($_POST['openclose']==2) ? '?closed=1' : '' ) ;
		header("Location: $location"); //перекидываем на текущую страницу, чтобы нельзя было повторить POST по F5
		exit;
		break;
	case "prj_up":
        $prj_id = __paramInit('int', 'prjid', 'prjid');
        $tr_id = __paramInit('int', 'tid', 'transaction_id');
        $rand = __paramInit('string', 'r', 'r');
        if($rand != $_SESSION['rand']) {
            header('Location: /404.php');
            exit;
        }
        $prj = new projects();
        $project = $prj->GetPrjCust($prj_id);
        if(projects::isProjectOfficePostedAfterNewSBR($project)) {
            header('Location: /404.php');
            exit;
        } else {
            if(!new_projects::UpPublicProject($prj_id, get_uid(), $tr_id, $error)) {
                if($error['nomoney']) {
                    header("Location: /bill/?paysum={$error['nomoney']}");
                    exit;
                }
            } else {
                projects::setFirstProjectsList($prj_id);
                header('Location: /bill/success/');
                exit;
            }
        }
        break;
	case 'prj_top':
	    $nProject = __paramInit('int', 'pid', 'pid');
	    $oProject = new projects();
	    $aProject = $oProject->GetPrjCust($nProject);
	    
	    $_SESSION['bill.GET']['back'] = ( $_SERVER["HTTP_REFERER"] ) ? $_SERVER["HTTP_REFERER"] : '/';
	    
	    if ( $uid && !new_projects::isKonkurs($aProject["kind"]) && $aProject["user_id"] == $uid && $aProject['closed'] != "t" && $aProject['is_blocked'] != 't' && projects::checkShowTop($aProject) ) {
	    	if ( !new_projects::topPublicProject($nProject, $uid, $error) ) {
                if ( $error['nomoney'] ) {
                    header( "Location: /bill/?paysum={$error['nomoney']}" );
                    exit;
                }
            }
            else {
                header('Location: /bill/success/');
                exit;
            }
	    }
	    else {
	        header('Location: /404.php');
            exit;
	    }
	    break;
    case "delete": // удаление аккаунта
        require_once (ABS_PATH . "/classes/users.php");
        require_once (ABS_PATH . '/classes/admin_log.php');
        $user_obj = new users();
        $sUid = get_uid();
        $user_obj->GetUserByUID($sUid);
        $sObjName = $user_obj->uname. ' ' . $user_obj->usurname . '[' . $user_obj->login . ']';
        $sObjLink = '/users/' . $user_obj->login;
        $sReason = "Аккаунт удален самостоятельно";
        if ($user_obj->setUserBan(get_uid(), 0, $sReason, 4, '', 1, true)) { // если удалось заблокировать аккаунт
            // пишем лог
            admin_log::addLog( admin_log::OBJ_CODE_USER, admin_log::ACT_ID_DEL_ACC, $sUid, $sUid, $sObjName, $sObjLink, 1, '', 18, $sReason);
            header("Location: /users/$user");
        }            
        break;
	case "delmrec":
		$selected = $_POST['id'];
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
		$error .= teams::teamsDelFavoritesExcept(get_uid(), $selected);
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
$is_pro = payed::CheckPro($_SESSION['login']);
$no_banner = !!$is_pro;

$header = $rpath."header.php";
$footer = $rpath."footer.html";

if($css_file) {
    $css_file = array($css_file);
}

$css_file = array( 'settings.css', '/css/nav.css',  '/css/block/b-voting/b-voting.css' );
$body_class = ($is_pro ? 'p-pro' : 'p-nopro');

include ($rpath.$template);

?>