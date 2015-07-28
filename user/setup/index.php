<?
$rpath = "../../";
$g_page_id = "0|22";
$stretch_page = true;
$showMainDiv  = true;
$footer_setup = true;
//стоимость часа работы
$max_cost_hour[0] = 300;	//usd
$max_cost_hour[1] = 250;	//euro
$max_cost_hour[2] = 7500;	//rur
$max_cost_hour[3] = 400;	//fm

//стоимость месяца работы
$max_cost_month[0] = 10000;	//usd
$max_cost_month[1] = 8000;      //euro
$max_cost_month[2] = 250000;    //rur
$max_cost_month[3] = 13000;     //fm

//стоимость работы из портфолио
$max_portf_cost[0] = 100000;	//usd
$max_portf_cost[1] = 100000;     //euro
$max_portf_cost[2] = 5000000;    //rur
$max_portf_cost[3] = 100000;     //fm

$max_exp_years = 100;
$max_time_value = 100;

$ab_text_max_length = 500;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/temp_email.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_langs.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_phone.php");
session_start();

$css_file = array( 'settings.css','profile.css', 'nav.css',  '/css/block/b-voting/b-voting.css' );
$js_file  = array( 'warning.js', 'note.js', 'status.js', 'banned.js', 'tawl_bem.js', 'raphael-min.js', 'svg.js', 
    'paid_advices.js', '/css/block/b-filter/b-filter.js', '/css/block/b-fon/b-fon.js', '/css/block/b-layout/b-layout.js', 
    'del_acc.js', 'kwords.js', 'sbr.js', 'specadd.js', 'drafts.js', 'polls.js', 'mAttach.js', 'blogs_cnt.js', 'blogs.js', 
    'opinions.js', '/kword_js.php', 'finance.js', 'user_langs.js', 'user_setup.js' );

$DB = new DB('master');

$uid = get_uid();
$login = $_SESSION['login'];
$role = $_SESSION['role'];
$page = trim($_GET['p']);
$user = trim($_GET['user']);
if (!$page) {
    header_location_exit('/users/' . $user . '/setup/main/');
}
$template = in_array($page, array('main', 'mailer'))   ? "template2.php" : "template.php";
if (strtolower($user) != strtolower($login)) {
    if($page=='finance' && hasPermissions('users')) {
        $u = new users();
        $u->GetUser($user);
        $uid = $u->uid;
        $login = $u->login;
        $role = $u->role;
    }
    else {
       include ABS_PATH."/403.php";
       exit;
    }
}
$_in_setup = 1;

$fpath = '';
$inner = '';

if ($page != 'main') {
	$user_phone_block = user_phone::getInstance()->render(user_phone::PLACE_HEADER);
}

// Сообщение сверху страницы
$alert_message = '';

if (is_emp($role)) { $fpath = "../employer/setup/"; include ($fpath."index.php"); exit;};

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
$is_pro = payed::CheckPro($login);
$no_banner = !!$is_pro;
$action = trim($_POST['action']);

if(strtolower($user)==strtolower($login) && is_pro(true)) { $no_adv = true; } else { $no_adv = false; }

switch ($page){
	case "info": $inner = "inform_inner.php"; $activ_tab = 3; break;
	case "mailer": $inner = "mailer_inner.php"; $activ_tab = 0; break;
	case "foto": $inner = "foto_inner.php"; $activ_tab = 0; break;
	//case "delete": $inner = "delete_inner.php"; $activ_tab = 0; break;
	case "portfolio": $inner = "tpl.portfolio.php"; $activ_tab = 1; break;
	case "portfsetup": $css_file[] = 'profile.css'; $inner = "portfolio_in_setup.php"; $activ_tab = 1; break;
	case "services": $inner = "services_inner.php"; $activ_tab = 2; break;
	case "servsetup": $inner = "services_in_setup.php"; $activ_tab = 2; break;
	case "specsetup": $inner = "services_in_spsetup.php"; $activ_tab = 2; break;
	case "specaddsetup": $inner = "services_in_spsetup_add.php"; $activ_tab = 2; $template = "template2.php"; break;
    
	case "finance":
            $bIsYdVerified = freelancer::isYdVerified($uid);
        $js_file[] = 'attachedfiles2.js';
        include('finance_action.php');
        $prefix = (isset($is_finance_deleted) && $is_finance_deleted)?'_deleted':'';
        $inner = "finance{$prefix}_inner.php";
        $activ_tab = 5;
        $template = "template2.php"; 
        break;
    
	case "tabssetup": $inner = "list_inner.php"; $activ_tab = 0; break;
	case "pwd":  $inner = "pwd_inner.php"; $activ_tab = 0; break;
    
	case "safety":
        include('safety_action.php');
        $inner = "safety_inner.php"; 
        $activ_tab = 0; 
        break;
    
    case "delete": $inner = "tpl.delete.php"; $activ_tab = 0; break;
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
        $inner = "main_inner.php"; 
        $activ_tab = 0;
        break;
	}
	
$content = "content.php";
	
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");

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
                    header_location_exit("/users/" . $_SESSION["login"] . "/setup/main/");
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
                header_location_exit("/users/" . $_SESSION["login"] . "/setup/main/");
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
            $frl->UpdateSafetyInformation($uid, $ip_addresses, $bind_ip);
            header_location_exit('/users/'. $_SESSION['login'] .'/setup/safety/');
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
		$portf = trim($_POST['portf']);
		$serv = trim($_POST['serv']);
		$info = 1;// trim($_POST['info']);
		$jornal = trim($_POST['jornal']);
		$rtng = trim($_POST['rating']);
        $tu = trim($_POST['tu']);
        
		$frl = new freelancer;
        $error .= $frl->UpdateTabs($uid,$portf,$serv,$info,$jornal,$rtng,0,0,$tu);
		if (!$error) $info_msg = "Изменения внесены";
		break;
		
	case "update_subscr":
		$newmsgs = trim($_POST['newmsgs']);
		$comments = trim($_POST['comments']);
		$prcomments = trim($_POST['prcomments']);
		$commune_subscr = __paramInit('bool', NULL, 'commune');                 //флаг уведомления о новых действиях в сообществах
		$commune_top_subscr = __paramInit('bool', NULL, 'commune_topics');      //флаг уведомления о новых постах в сообществах
		$articlescomments = trim($_POST['articlescomments']);
		$massending = trim($_POST['massending']);

		$daily_news = trim($_POST['daily_news']);
		$opin = trim($_POST['opin']);
//		$free = $_POST['free'];
//		if ($free) foreach($free as $ikey => $val)
//			$vacan += $val*pow(2,$ikey);
//		else $vacan = 0;

                $vacan = array();
                if(is_array($_POST['cats'])) foreach ($_POST['cats'] as $key => $value) {
                    $vacan[] = array('category_id' => (int)$value, 'subcategory_id' => (!empty($_POST['subcats'][$key]) ? (int)$_POST['subcats'][$key] : 0));
                }

		// сообщества
		$comm = !empty($_POST['comm']) ? array_map('intvalPgSql', $_POST['comm']) : false;
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
                if(!empty($_POST['commune_topics'])){
                    $cm = new commune();
                    $cm->clearSubscription($uid);
                    $cm->setCommunesSubscription($comm,$uid,true);
                } else {
                    $cm = new commune();
                    $cm->clearSubscription($uid);
                }
		// сообщества

		$frl = new freelancer;
        $error .= $frl->UpdateSubscr($uid, $newmsgs, $vacan, $comments,$opin, $prcomments, $commune_subscr, $commune_top_subscr, __paramInit('bool', NULL, 'adm_subscr'), __paramInit('bool', NULL, 'contest'), __paramInit('bool', NULL, 'team'),0, $articlescomments, $massending, 0, $daily_news, __paramInit('bool', NULL, 'vacan'), __paramInit('bool', NULL, 'payment'));
		if (!$error) {
      $membuff = new memBuff();
      $membuff->flushGroup('massending_calc');
      
      $info_msg = "Изменения внесены";
  }
		break;
	case "inform_change":
	    foreach($_POST as $key=>$value) {
	        if(!is_array($value)) $_POST[$key] = stripslashes($value);
	    }
		$frl = new freelancer();
        if (($datey=trim($_POST['datey'])) && ($dated=trim($_POST['dated'])))
		{
            if (!is_numeric($datey) || !is_numeric($dated) || !checkdate(intval(trim($_POST['datem'])), intval($dated), intval($datey)))
            {$error_flag = 1; $alert[1] = "Поле заполнено некорректно";}
        else
            $frl->birthday = dateFormat("Y-m-d", $datey."-".trim($_POST['datem'])."-".$dated);
		} else {
			//$frl->birthday = "1910-01-01";
            $error_flag = 1; $alert[1] = "Поле заполнено некорректно";
		}
    //www.x.ru" onclick="alert(12345)

		if (!$alert[1] && $frl->birthday && (date("Y", strtotimeEx($frl->birthday)) >= date("Y"))) {$error_flag = 1; $alert[1] = "Поле заполнено некорректно";}

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
        $frl->site = change_q(substr(trimhttp(trim($_POST['site'])), 0, 96), true);
        $frl->icq = change_q(substr(trim($_POST['icq']), 0, 96), true);
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
        
        
        	if($frl->icq = change_q(substr(strip_tags(trim($_POST['icq'])),0,96), true))
		  if (!preg_match("/^[-0-9\s]*$/", $frl->icq) && !is_email($frl->icq)) { $error_flag = 1; $alert[2] = "Поле заполнено некорректно"; }
		if($frl->icq_1 = change_q(substr(strip_tags(trim($_POST['icq_1'])),0,96), true))
		  if (!preg_match("/^[-0-9\s]*$/", $frl->icq_1) && !is_email($frl->icq_1)) { $error_flag = 1; $alert[31] = "Поле заполнено некорректно"; }
		if($frl->icq_2 = change_q(substr(strip_tags(trim($_POST['icq_2'])),0,96), true))
		  if (!preg_match("/^[-0-9\s]*$/", $frl->icq_2) && !is_email($frl->icq_2)) { $error_flag = 1; $alert[32] = "Поле заполнено некорректно"; }
		if($frl->icq_3 = change_q(substr(strip_tags(trim($_POST['icq_3'])),0,96), true))
		  if (!preg_match("/^[-0-9\s]*$/", $frl->icq_3) && !is_email($frl->icq_3)) { $error_flag = 1; $alert[32] = "Поле заполнено некорректно"; }


        
//        $icq_regexp = '/^([A-z0-9_\\.-]+[@][A-z0-9_-]+([.][A-z0-9_-]+)*[.][A-z]{2,6}|[-0-9]*)$/';
//        if($frl->icq = change_q(substr(trim($_POST['icq']), 0, 16), true))
//        	  if (!preg_match($icq_regexp, $frl->icq)) { $error_flag = 1; $alert[2] = "Поле заполнено некорректно"; }
//
//        // more icq
//        if($frl->icq_1 = change_q(substr(trim($_POST['icq_1']), 0, 16), true))
//        	  if (!preg_match($icq_regexp, $frl->icq_1)) { $error_flag = 1; $alert[31] = "Поле заполнено некорректно"; }
//        if($frl->icq_2 = change_q(substr(trim($_POST['icq_2']), 0, 16), true))
//        	  if (!preg_match($icq_regexp, $frl->icq_2)) { $error_flag = 1; $alert[32] = "Поле заполнено некорректно"; }
//        if($frl->icq_3 = change_q(substr(trim($_POST['icq_3']), 0, 16), true))
//        	  if (!preg_match($icq_regexp, $frl->icq_3)) { $error_flag = 1; $alert[33] = "Поле заполнено некорректно"; }
        // more icq
        
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
        $frl->sex = (int)$_POST['sex'] == 1 ? 't' : 'f';
        if($frl->phone = change_q(substr(trim($_POST['phone']), 0, 24), true))
        	  if (!preg_match("/^[-+0-9)( #]*$/", $frl->phone)) { $error_flag = 1; $alert[3] = "Поле заполнено некорректно"; }

        // more phone
        if($frl->phone_1 = change_q(substr(trim($_POST['phone_1']), 0, 24), true))
        	  if (!preg_match("/^[-+0-9)( #]*$/", $frl->phone_1)) { $error_flag = 1; $alert[61] = "Поле заполнено некорректно"; }
        if($frl->phone_2 = change_q(substr(trim($_POST['phone_2']), 0, 24), true))
        	  if (!preg_match("/^[-+0-9)( #]*$/", $frl->phone_2)) { $error_flag = 1; $alert[62] = "Поле заполнено некорректно"; }
        if($frl->phone_3 = change_q(substr(trim($_POST['phone_3']), 0, 24), true))
        	  if (!preg_match("/^[-+0-9)( #]*$/", $frl->phone_3)) { $error_flag = 1; $alert[63] = "Поле заполнено некорректно"; }
        // more phone


		$resume = new CFile($_FILES['resume']);
        $resumetxt = str_replace("\r\n", "\n", $_POST['resumetxt']);
        if (strlen($resumetxt) > 4000)
		{
			$error_flag = 1; $alert[8] = "Количество знаков превышает допустимое значение";
		}
		else
		{
            /*$dS = '@;;,,@;;@;__-=-=@~~~~'.mt_rand(8, 10000);
            $resumetxt = str_replace("\n", $dS, $resumetxt);
			$resumetxt = change_q(substr(trim($resumetxt),0,4000), false, 90);
            $resumetxt = str_replace($dS, "\n", $resumetxt);*/
            $resumetxt = antispam(__paramInit('html', null, 'resumetxt', ''));
            $frl->resume = $resumetxt;
		}

		if (strlen($_POST['konk']) > 4000)
		{
			$error_flag = 1; $alert[9] = "Количество знаков превышает допустимое значение";
		}
		else
		{
			//$frl->konk = change_q(substr(trim($_POST['konk']),0,4000), false, 90);
			$frl->konk = (string) antispam(__paramInit('html', '', 'konk'));
		}

		$showcls = trim($_POST['showcls']);
		$frl->clients = strip_tags(trim($_POST['clients']));
		$del_resume = trim($_POST['del_resume']);
		$frl->ljuser = change_q(substr(trim($_POST['ljuser']), 0, 64), true);
		$frl->skype = change_q(substr(trim($_POST['skype']),0,64), true);
        //$frl->skype_as_link = $frl->skype && $_POST['skype_as_link'];
		$frl->skype_1 = change_q(substr(trim($_POST['skype_1']),0,64), true);
        //$frl->skype_1_as_link = $frl->skype_1 && $_POST['skype_1_as_link'];
		$frl->skype_2 = change_q(substr(trim($_POST['skype_2']),0,64), true);
        //$frl->skype_2_as_link = $frl->skype_2 && $_POST['skype_2_as_link'];
		$frl->skype_3 = change_q(substr(trim($_POST['skype_3']),0,64), true);
        //$frl->skype_3_as_link = $frl->skype_3 && $_POST['skype_3_as_link'];
		$frl->info_for_reg = serialize($_POST['info_for_reg']);

        if (!preg_match("/^[a-zA-Z0-9_-]*$/", $frl->ljuser)) { $error_flag = 1; $alert[12] = "Поле заполнено некорректно"; }

        // more lj
		$frl->lj_1 = change_q(substr(trim($_POST['lj_1']), 0, 64), true);
        if (!preg_match("/^[a-zA-Z0-9_-]*$/", $frl->lj_1)) { $error_flag = 1; $alert[71] = "Поле заполнено некорректно"; }

		$frl->lj_2 = change_q(substr(trim($_POST['lj_2']), 0, 64), true);

        if (!preg_match("/^[a-zA-Z0-9_-]*$/", $frl->lj_2)) { $error_flag = 1; $alert[72] = "Поле заполнено некорректно"; }
		$frl->lj_3 = change_q(substr(trim($_POST['lj_3']), 0, 64), true);
        if (!preg_match("/^[a-zA-Z0-9_-]*$/", $frl->lj_3)) { $error_flag = 1; $alert[73] = "Поле заполнено некорректно"; }
        // more lj

		$ext = $resume->getext();
		
		$frl->blocks = '1'.intval(trim($_POST['showkonk'])).
		intval(trim($_POST['showcls'])).intval(trim($_POST['showempl'])).
        intval(trim($_POST['showfrl'])).intval(trim($_POST['showmyrec'])).
        intval(trim($_POST['showcommune'])).intval(trim($_POST['showjoincommune']));
		
        //данные об языках пользователя
        $user_langs = array();
        if ( is_array( $_POST["langs"] ) ) {
        	foreach ( $_POST["langs"] as $key => $item ) {
                $user_lang["id"] = (int)$item;
                if ( $user_lang["id"] == 0 ) {
                    continue;
                }
        	    if ( is_array( $_POST["lang-q"] ) ) {
                    $user_lang["quality"] = ( (int)$_POST["lang-q"][$key] > 0 ? (int)$_POST["lang-q"][$key] : 2);
                }
                $user_langs[ $user_lang["id"] ] = $user_lang;
        	}
        }
        if (!$error_flag && $resume->size == 0 && strlen($resume->tmp_name) != 0) {
            $error_flag = 1;
            $alert[4] = "Файл не удовлетворяет условиям загрузки";
        }
		if (!$error_flag) {
		    user_langs::updateUserLangs($_SESSION["uid"], $user_langs);
            $error .= $frl->UpdateInform($uid, $resume, $del_resume, $file_error);
            // Доступ пользователя к функциям сайта
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
            $reg    = new registration();
            $reg->checkUserAccess($uid, true);
            $selected = $_POST['id'];
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
            //var_dump($selected);
            $error .= teams::teamsDelFavoritesExcept($uid, $selected);
            if ($file_error) {
    			$error_flag = 1;
    			$alert[4] = "Файл не удовлетворяет условиям загрузки";
    		}
		}
		if (!$error && !$error_flag) {
		    $info = "Изменения внесены";
		}
		break;
	case "main_change":
        $name = change_q(substr(trim($_POST['name']),0,21), true);
        $pname = change_q(substr(trim(stripslashes($_POST['pname'])),0,100), true);
        $surname = change_q(substr(trim($_POST['surname']),0,21), true);
        $email = change_q(substr(trim($_POST['email']),0,64), true);
        $oldpwd = trim($_POST['oldpwd']);
        $question_button_hide = intval($_POST['consultant_show']);
        $promo_block_hide = intval($_POST['promo_show']);
        $direct_links = intval($_POST['direct_links']);
        setlocale(LC_ALL, 'ru_RU.CP1251');
        if (!preg_match("/^[-a-zA-Zа-яёА-ЯЁ]+$/", $name)) { $error_flag = 1; $alert[1] = "Поле заполнено некорректно"; }
        if (!preg_match("/^[-a-zA-Zа-яёА-ЯЁ]+$/", $surname)) { $error_flag = 1; $alert[2] = "Поле заполнено некорректно"; }
        if (!is_email($email)) { $error_flag = 1; $alert[3] = "Поле заполнено некорректно"; }
	$user = new freelancer();
        $user->GetUser($login);
        
        $sOldName    = $user->uname;
        $sOldSurname = $user->usurname;
        
        if ($email != $user->email) {$ch_mail = 1;}
        if ($user->CheckEmail($email)) { $error_flag = 1; $alert[3] = "Извините, такой электронный ящик уже существует"; }
        if (temp_email::isTempEmail($email)) {
            $error_flag = 1;
            $alert[3] = "Извините, но почтовые адреса с этого домена запрещены к регистрации";
        }
        $frl = new freelancer;
	$err = $frl->UpdateMain($uid, $name, $surname, $user->email, $oldpwd, $pname, $error_flag);
        
        if ( !$err && !$error_flag ) { // все что нужно после успешного обновления:
            if ( users::isSuspiciousUser($uid, $login, $login, $name, $sOldName, $surname, $sOldSurname) ) {
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
            $code = activate_mail::Create($uid, $email, $err);
			if ($code){
				require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
				$smail = new smail();
				$smail->ConfirmNewEmail($login, $email, $code);
				$inner = "chmail_inner.php";
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
            $alert[2] = "Пароль содержит недопустимые символы.<br>Пожалуйста, используйте только латинские буквы, "
                . "цифры и следующие спецсимволы: !@#$%^&*()_+-=;,./?[]{}";
        }
        elseif (strcmp($pwd,$pwd2)) { $error_flag = 1; $alert[3] = "Введенные пароли не совпадают"; }
        elseif ((strlen($pwd) < 6))  { $error_flag = 1; $alert[2] = "Слишком короткий пароль (минимум — 6 символов)"; }
        elseif ((strlen($pwd) > 24)) { $error_flag = 1; $alert[2] = "Слишком длинный пароль (максимум — 24 символа)";}
        

        if(!$error_flag) {
    		$frl = new users;
            $alert[1] = $frl->UpdatePwd($uid, $oldpwd, $pwd, 0);
    		if (!$alert[1] && !$error_flag) {
    			$info = "Изменения внесены";
    			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
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
		$frl = new freelancer;
		if ($foto->name || $del == 1){
			$error .= $frl->UpdateFoto($uid,$foto, $del);
			/*if (!$error) $info_msg = "Изменения внесены";
			else $error = "Файл не удовлетворяет условиям загрузки";*/
            $nParam = (!$error) ? 1 : 2;
            if(!$error) {
                $_SESSION['photo'] = $frl->photo;
            }
            header_location_exit( '/users/'. $_SESSION['login'] .'/setup/foto/?msg='.$nParam.($_REQUEST['pfrom'] ? '&pfrom=toppayed' : '') );
		}
		break;
	//Удаление аккаунта убрано! не раскоменчивать!
	/*case "delete":
		$passwd = trim($_POST['passwd']);
		if ($passwd){
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
			$frl = new freelancer;
 			if ($frl->DeleteUser($uid, $passwd, $error)){
				logout();
				$content = $rpath."deleted_inner.php";
			} else $error = "Поле заполнено некорректно";
		} else $error = "Поле заполнено некорректно";
		break;*/
	case "serv_change":
        $error_serv = '';
		$tab_name_id = (($_POST['tab_name_id'] == 1) ? 1 : 0);
		$exp = intval($_POST['exp'] );
		$cost_hour = intval(str_replace(" ", "", $_POST['cost_hour']) * 100) / 100;
		$cost_month = intval(str_replace(" ", "", $_POST['cost_month']) * 100) / 100;
		$cost_type_hour = intval($_POST['cost_type_hour']);
		$cost_type_month = intval($_POST['cost_type_month']);
		
		$in_office = (intval($_POST['in_office'])==1)?'t':'f';
		$prefer_sbr = (intval($_POST['prefer_sbr'])==1)?'t':'f';
    // Разбиваем длинные слова.
		setlocale(LC_ALL, 'ru_RU.CP1251');
        $text = stripslashes(trim($_POST['ab_text']));
#    $text = preg_replace("|[\s]+|", " ", $text);
        $text = preg_replace("|[\t]+|", " ", $text);
        $text = preg_replace("|[ ]+|", " ", $text);
        $original_text = $text;
        $cat_show =  !empty($_POST['cat_show']) && (int)$_POST['cat_show'] > 0;
        // Обрезаем.
        $newlines = intval(substr_count($text, "\r"));
        $text = antispam(change_q_x_a(substr($text, 0, $ab_text_max_length+$newlines), false, false, "b|i|p|ul|li{1}"));
		/**
		 * Проверка значений.
		 */
		
		if (strlen($original_text) > $ab_text_max_length+$newlines)
		{
            $error_serv .= (($error_serv == '') ? '' : '<br />') . 'Количество знаков превышает допустимое значение. Допустимо максимум '.$ab_text_max_length.' знаков для поля "Уточнения к услугам в портфолио"';
		}

		if (($exp < 0) || ($exp > $max_exp_years))
		{
		  $error_serv .= (($error_serv == '') ? '' : '<br />') . 'Недопустимое значение. Опыт работы должен быть в пределе от 0 до ' . $max_exp_years . '.';
		}
		if (($cost_hour < 0) || ($cost_hour > $max_cost_hour[$_POST['cost_type_hour']]))
		{
		  $error_serv .= (($error_serv == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость часа работы должна быть в пределе '. view_range_cost2(0, $max_cost_hour[$_POST['cost_type_hour']], '', '', false, $_POST['cost_type_hour'].'.');
		}
		if (($cost_month < 0) || ($cost_month > $max_cost_month[$_POST['cost_type_month']]))
		{
		  $error_serv .= (($error_serv == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость месяца работы должна быть в пределе '. view_range_cost2(0, $max_cost_month[$_POST['cost_type_month']], '', '', false, $_POST['cost_type_month']).'.';
		}
		/**
		 * Сохраняем.
		 */
		$frl = new freelancer;
		if ($error_serv == '')
		{
        $error_db = $frl->UpdateServ($uid, $exp, $text, $tab_name_id, $cost_hour, $cost_month, $cost_type_hour, $cost_type_month, $in_office,$cat_show, $prefer_sbr);
		}
        if ($error_serv != '' || $error_db)
        {
          $error_serv = 'Данные не сохранены<br /><br />' . $error_serv;
        }
		if (!$error_serv)
		{
		  $info_serv = "Изменения внесены";
    header_location_exit("/users/{$login}");
		}
		break;
	case "prof_change":
	    // Ключевые слова !!! старые удаляются, новые добавляются не зависимо от результата обновления профессии UpdateProfDesc
	    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
	    $kwords = new kwords();
	    
	    if(count($_POST['prof_keys']) > 0) {
                
	        foreach($_POST['prof_keys'] as $prof_id => $keys) {
                $aOldIds = array_keys( $kwords->getUserKeys($uid, $prof_id) );
                $ids     = array();
                
	            $kwords->delUserKeys($uid, $prof_id);
	            if ( trim($keys) ) {
                    $ukey = explode(",", $keys);
                    if ( count($ukey) > 0 ) {
                        $ids  = $kwords->add($ukey, true);
                        $kwords->addUserKeys($uid, $ids, $prof_id);
                    }
                }
                
                $kwords->moderUserKeys( $uid, $prof_id, $aOldIds, $ids, $uid, $keys );
	        }
	    }
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        $obj_prof = new professions();
        $error_prof .= $obj_prof->UpdateProfDesc($uid, $_POST['prof_id'], str_replace(" ", "", $_POST['prof_cost_from']), str_replace(" ", "", $_POST['prof_cost_to']), str_replace(" ", "", $_POST['prof_cost_hour']), str_replace(" ", "", $_POST['prof_cost_1000']), $_POST['prof_cost_type'], $_POST['prof_cost_type_hour'], $_POST['prof_time_type'], $_POST['prof_time_from'], $_POST['prof_time_to'], $_POST['prof_text'], $errorProfText);
        if (!$error_prof) $info_prof = "Изменения внесены";
        $saved_prof_id = intval($_POST['prof_id']);
        break;
	case "spec_change":
        $spec = trim($_POST['spec']);
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        $or_spec=professions::GetProfessionOrigin($spec);
        $frl = new freelancer;
        $frl->spec = $spec;
        $frl->spec_orig = $or_spec;
        professions::setLastModifiedSpec($uid, $spec);
        $error .= $frl->Update($uid,$res);
        $_SESSION['specs'] = $frl->GetAllSpecs($uid);
        break;
	case "save_spec_add":
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        if(professions::UpdateProfsAddSpec( $uid, __paramInit('int', NULL, 'oldprof_id'), __paramInit('int', NULL, 'prof_id'), __paramInit('int', NULL, 'paid_id') )) {
        	$_SESSION['specs'] = freelancer::GetAllSpecs($uid);
            header("Location: /users/{$login}/setup/specaddsetup/");
            exit;
        }
        break;
	case "portf_choise":
	    unset($_SESSION['text_spec']);
        $params = $_POST['prof'];
        if(is_array($params)) $firstProf = "#prof".$params[0];
        else $firstProf = "";
        if(!($params && is_array($params))) $params = array(-3);
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        $prof = new professions();
		if ($params && is_array($params)){
        	$error .= $prof->UpdatePortfChoise($uid,$params);
		}
        unset($prof);
        header_location_exit("/users/{$login}");
        break;
	case "portf_change":
        ini_set('memory_limit', '200M');
     
        if($_POST['is_video'] === '1') {
            // Добавление видео
    	    // Удаляем повторные пробелы.
    	    //$name = substr(strip_tags(trim($_POST['v_pname'])),0,80);
            $name = __paramInit('html', null, 'v_pname', '', 80, true);
            if(!$name) $name='';
    	    $sm_img = new CFile($_FILES['v_sm_img']);

    	    // Разбиваем длинные слова.
    	    //$descr = substr(change_q_new(stripslashes($_POST['v_descr'])),0,1500);
            $descr = __paramInit('html_save_ul_li_b_p_i', null, 'v_descr', '', 1500, true);
            if(!$descr) $descr='';
    	    $prof = (int)trim($_POST['v_prof']);
    	    $new_prof = (int)trim($_POST['v_new_prof']);
    	    $prj_id = (int)trim($_POST['v_prjid']);
    	    $cost = intval(str_replace(" ", "", $_POST['v_pcost']) * 100) / 100;
    	    $time_type = (int)trim($_POST['v_ptimeei']);
    	    $time_value = (int)trim($_POST['v_ptime']);
    	    $prev_type = (int)trim($_POST['v_prev_type']);
    	    $cost_type = (int)trim($_POST['v_pcosttype']);
            
            $is_video = 't';
            $video_link = stripslashes(trim($_POST['v_video_link']));
            $make_position = $_POST['v_make_position'];
            $make_position_num = trim($_POST['v_make_position_num']);
        } else {
            // Добавление работы
    	    // Удаляем повторные пробелы.
            $name = __paramInit('html', null, 'pname', '', 120, true);
            if(!$name) $name='';
    	    $img = new CFile($_FILES['img']);
    	    $sm_img = new CFile($_FILES['sm_img']);
            
            $link = addhttp(trim(stripslashes(__paramInit('string', NULL,'link',null, 150))));
            if(!$link) $link='';
//            $link = stripslashes(trim($_POST['link']));

            //echo $link;
    	    // Разбиваем длинные слова.
    	    //$descr = tidy_repair_string($_POST['descr'], array("show-body-only" => true), 'raw'); // Приводим теги впорядок
    	    //$descr = substr(change_q_new(trim(stripslashes($_POST['descr']))),0,1500);
    	    $descr = __paramInit('html_save_ul_li_b_p_i', null, 'descr', '', 1500, true);
            if(!$descr) $descr='';
    	    $prof = (int)trim($_POST['prof']);
    	    $new_prof = (int)trim($_POST['new_prof']);
    	    $prj_id = (int)trim($_POST['prjid']);
    	    $cost = intval(str_replace(" ", "", $_POST['pcost']) * 100) / 100;
    	    $time_type = (int)trim($_POST['ptimeei']);
    	    $time_value = (int)trim($_POST['ptime']);
    	    $prev_type = (int)trim($_POST['prev_type']);
    	    $cost_type = (int)trim($_POST['pcosttype']);
            
            $is_video = 'f';
            $video_link = '';
            $make_position = $_POST['make_position'];
            $make_position_num = trim($_POST['make_position_num']);
        }
        
        $update_prev = intval($_POST['upd_prev']);
	
        $new_position = NULL;
        if($new_prof!=$prof || !$prj_id)
            $new_position = 0;
        if (isset($make_position)) {
            switch ($make_position) {
                case 'first': $new_position = 1; break;
                case 'last': $new_position = 0;break;
                case 'num':
                default:
                    $new_position = intval($make_position_num);
                    if ($new_position <= 0)
                        $new_position = 1;
                    break;
            }
        } 

	    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
	    $portf = new portfolio();
	    if ($prj_id)
	    {
	      if ($img->name != '')
	      {
	        $prj_pict = $img->name;
	      }
	      else
	      {
	        $prj_pict = $portf->GetField($prj_id, 'pict');
	      }
	    }
	    else
	    {
	      $prj_pict = $img->name;
	    }
			/**
			 * Проверка значений.
			 */

	    if ($img->error[0] && $img->name) { $error_flag = 1; $alert[3] = "Слишком большой файл";}

        // --

	    if (!$name || ( strlen(trim(stripslashes($_POST['pname']))) > 80) ) { $error_flag = 1; if($is_video=='f') { $enum = 1; } else { $enum = 201; } $alert[$enum] = "Поле заполнено некорректно"; }
	    //if (!($link || $prj_pict || $descr)) { $error_flag = 1; $alert[2] = "Поле заполнено некорректно"; }
      //if (($link != '') && (!eregi("^((http|https|ftp)://){0,1}((([a-zа-я0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6}))|(([0-9]{1,3}\.){3}([0-9]{1,3})))((/|\?)[a-z0-9~#%&'_\+=:\?\.-]*)*)$", $link)))
      //{
      //  $error_flag = 1;
      //  $alert[6] = 'Поле заполнено некорректно. Неверный формат ссылки.';
      //}

        //$link = preg_replace("/^http:\/\//","",$link);
		if ($link!='' && !url_validate($link, true)) {
            $error_flag = 1; $alert[6] = "Поле заполнено некорректно";
        }

        if($is_video=='t') {
            $v_video_link = video_validate($video_link);
            if(!$v_video_link) {
                $error_flag = 1; $alert[206] = "Поле заполнено некорректно";
            } else {
                $video_link = preg_replace("/^http:\/\//","",$v_video_link);
            }
        }


			if (($cost < 0) || ($cost > $max_portf_cost[$cost_type]))
			{
			  $error_flag = 1;
              if($is_video=='f') { $enum = 4; } else { $enum = 204; }
			  $alert[$enum] = 'Поле заполнено некорректно. Стоимость должна быть в пределе от 0 ' . view_range_cost2(0, $max_portf_cost[$cost_type], '', '', false, $cost_type) . ($cost_type != 2 ? '.' : '');
			}
			if (($time_value < 0) || ($time_value > $max_time_value))
			{
			  $error_flag = 1;
              if($is_video=='f') { $enum = 5; } else { $enum = 205; }
			  $alert[$enum] = 'Поле заполнено некорректно. Временные затраты должны быть в пределе от 0 до ' . $max_time_value . '.';
			}

              if ($sm_img->size > 102400) {
                  if($is_video=='f') { $enum = 7; } else { $enum = 207; }
                  $alert[$enum] = "Слишком большой файл превью. Загрузите превью меньшего объема.";
                  $error_flag = 1;
              }

	
			if (!$error_flag)
	    {
	      if($new_prof > 0 ||
	         ( ($new_prof==professions::CLIENTS_PROF_ID || $new_prof==professions::BEST_PROF_ID) &&
	           (($prj_id && $new_prof==$prof) || portfolio::CountAll($uid, $new_prof, true) < portfolio::MAX_BEST_WORKS ) )
  	      )
	      {
  	      if (!$prj_id)
  	      {
            $err = $portf->AddPortf($uid, $name, $img, $sm_img, $link, $descr, $new_prof, $cost, $cost_type, $time_type, $time_value, $prev_type, $file_error, $preview_error,$new_position,0,$is_video,$video_link);

            if ($preview_error)
            {
              $error_flag = 1;
              if ($err == 'Слишком большой файл превью.') {
                  if($is_video=='f') { $enum = 7; } else { $enum = 207; }
                  $alert[$enum] = "Слишком большой файл превью. Загрузите превью меньшего объема.";
              }
              else {
                  if($is_video=='f') { $enum = 7; } else { $enum = 207; }
                  $alert[$enum] = "Невозможно уменьшить изображение превью. Загрузите другое изображение для превью или пересохраните данное.";
              }
            }
  	      }
  	      else
  	      {
            $err = $portf->EditPortf($uid, $name, $img, $sm_img, $link, $descr, $new_prof, $cost, $cost_type, $time_type, $time_value, $prev_type, $prj_id, $file_error, $preview_error,$new_position,0,$video_link, $update_prev);

  	        if ($preview_error)
  	        {
  	          $error_flag = 1;
  	          if ($err == 'Слишком большой файл превью.') {
                  if($is_video=='f') { $enum = 7; } else { $enum = 207; }
  	              $alert[$enum] = "Слишком большой файл превью. Загрузите превью меньшего объема.";
  	          }
  	          else {
                  if($is_video=='f') { $enum = 7; } else { $enum = 207; }
  	              $alert[$enum] = "Невозможно уменьшить изображение превью. Загрузите другое изображение для превью или пересохраните данное.";
  	          }
  	        }
  	      }
  	    }
	      if (($err === 1) || ($err === true));// { $error_flag = 1; $alert[2] = "Поле заполнено некорректно"; }
	      else { $error .= $err; }
	    }
	//    if ($file_error) { $error_flag = 1; $alert[3] = $err; }
	//    if ($preview_error) { $error_flag = 1; $alert[7] = "Поле заполнено некорректно"; }
        
	    
	    
	
        // защита от F5
        if (!$alert) {
            header("Location: /users/{$login}/setup/portfolio/");
            exit;
        }
	    
        
        break;
	case "poschange":
        $prj_ids = $_POST['profid'];
        $poss = $_POST['pos'];
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
        $portf = new portfolio();
        $error .= $portf->UpdatePositions($uid, $prj_ids, $poss);
        break;
	case "portf_del":
        if($_POST['is_video'] === '1') {
            $prj_id = (int)trim($_POST['v_prjid']);
        } else {
            $prj_id = (int)trim($_POST['prjid']);
        }
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
        $portf = new portfolio();
        if ($prj_id) $error .= $portf->DelPortf($uid,$prj_id);
        
        break;
	case "portf_del_all":
		if (sizeof($_SESSION['w_select'][$_POST['w_delete_prof']]))
		{
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
			$portf = new portfolio();

			foreach($_SESSION['w_select'][$_POST['w_delete_prof']] as $wkey => $wvalue)
			{
				$prj_id = (int)trim($wkey);
                if ($prj_id) $error .= $portf->DelPortf($uid,$prj_id);
			}
		}
		break;
	case "portf_move_all":
		if (sizeof($_SESSION['w_select'][$_POST['w_move_prof_from']]))
		{
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
			$portf = new portfolio();

			$prof_id = (int)trim($_POST['w_move_prof_to']);
			$count_add = count($_SESSION['w_select'][$_POST['w_move_prof_from']]);
			
            if($prof_id>0 || $prof_id==professions::CLIENTS_PROF_ID || $prof_id==professions::BEST_PROF_ID && (portfolio::CountAll($uid, $prof_id, true)+$count_add) <= portfolio::MAX_BEST_WORKS)
			{
				foreach($_SESSION['w_select'][$_POST['w_move_prof_from']] as $wkey => $wvalue)
				{
					$prj_id = (int)trim($wkey);
                    if ($prj_id) $error .= $portf->ChangeProjectProf($uid, $prof_id, $prj_id);
				}
			} 
		}
		break;
	case "diz_ch":
		$stddiz = $_POST['stddiz'];
		$frl = new freelancer();
		$frl->design = isset($stddiz)?0:1;
        $error .= $frl->Update($uid,$res);
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
            admin_log::addLog( admin_log::OBJ_CODE_USER, admin_log::ACT_ID_DEL_ACC, $sUid, $sUid, $sObjName, $sObjLink, 1, '', null, $sReason);
            header("Location: /users/$user");
        }            
        break;
    case 'real_name_splash': // сохранение имени, фамилии и юзерпика с соответствующего сплэш-скрина
        require_once (ABS_PATH . "/classes/users.php");
        require_once (ABS_PATH . "/classes/attachedfiles.php");
        require_once (ABS_PATH . "/classes/CFile.php");
        $userObj = new users();
        $userID = get_uid();
        
        $aFiles = new attachedfiles($_POST['attachedfiles_session']);
        $userpics = $aFiles->getFiles(array(1));
        if (is_array($userpics) && !empty($userpics)) {
            $userpic = array_pop($userpics);
            $aFiles->setStatusTo3($userpic['id']);
            $foto = new CFile($userpic['id']);
            $to = $foto->path . 'sm_' . $foto->name;
            $foto->resizeImage($to, 50, 50, 'auto', true);
            $userObj->photo = $userpic['name'];
        }
        
        $name = change_q(substr(trim($_POST['name']),0,21), true);
        $surname = change_q(substr(trim($_POST['surname']),0,21), true);
        if ($name && !preg_match("/^[-a-zA-Zа-яёА-ЯЁ]+$/", $name)) {
            $error_flag = 1; $alert[1] = "Поле заполнено некорректно";
        }
        if ($surname && !preg_match("/^[-a-zA-Zа-яёА-ЯЁ]+$/", $surname)) {
            $error_flag = 1; $alert[2] = "Поле заполнено некорректно";
        }
        
        if (!$error_flag) {
            if ($name) {
                $userObj->uname = $name;
            }
            if ($surname) {
                $userObj->usurname = $surname;
            }
            if ($name || $surname || $userpic) {
                $userObj->Update($userID, $res);
            }
            $info = "Изменения внесены";
        }
        // делаем так как будто сохранение было не из сплэша а из обычной формы в профиле пользователя
        $action = 'main_change';
        $userObj->GetUserByUID($userID);
        $email = $userObj->email;
        $pname = $userObj->pname;
        $promo_block_hide = (bool)$_COOKIE['nfastpromo_open'];
        $direct_links = $_SESSION['direct_external_links'];
        break;
}

$body_class = ($is_pro ? 'p-pro' : 'p-nopro');

$header = $rpath."header.php";
$footer = $rpath."footer.html";


include ($rpath.$template);