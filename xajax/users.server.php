<?php

$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");


/**
 * Вернуть контактные данные фрилансера если есть доступ их смотреть
 * 
 * @param type $login
 * @param type $hash
 * @return \xajaxResponse
 */
function getContactsInfo($login, $hash)
{
    $objResponse = new xajaxResponse();
    
    if ($hash === paramsHash(array($login))) {

        $freelancer = new freelancer;
        $freelancer->GetUser($login);
        
        if ($freelancer->uid > 0 && 
            !is_emp($freelancer->role) && 
            is_view_contacts($freelancer->uid) && 
            is_contacts_not_empty($freelancer)) {
         
            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');
            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/country.php');
            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/city.php');
            require_once(ABS_PATH . '/classes/statistic/StatisticFactory.php');
            require_once(ABS_PATH . '/classes/users.php');
            
            $html = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/user/contacts_info.php',
                array('user' => $freelancer)
            );            
            
            $ga = StatisticFactory::getInstance('GA');
            $ga->queue('event', array(
                'uid' => isset($_SESSION['uid'])?$_SESSION['uid']:0,
                'cid' => users::getCid(),
                'category' => 'Freelancer',
                'action' => 'show_contacts',
                'label' => "{'login': '{$freelancer->login}'}"
            ));

            $objResponse->assign('contacts_info_block', 'innerHTML', $html);
        }
    }

    return $objResponse;
}


function getUserPhoto() {
    $objResponse = new xajaxResponse();
    $udata = users::getUserShortInfoFinInfo(get_uid(false));
    if(!$udata['no_foto']) {
        $ufoto = WDCPREFIX.'/users/'.$_SESSION['login'].'/foto/'.get_unanimated_gif($_SESSION['login'], $udata['record']['photo']);
    } else {
        $ufoto = WDCPREFIX.'/images/no_foto_b.png';
    }

    //@todo: top_payed.php более неиспользуется
    //$objResponse->script('TopPayed.adImgPath = "'.$ufoto.'";');
    $objResponse->script('$("payfoto").set("src","'.$ufoto.'")');
    return $objResponse;
}

function PopVote($rand,$login,$vote,$cur_val){
	session_start();
	$objResponse = new xajaxResponse();

	$voter_login = $_SESSION['login'];
	$voter_id = $_SESSION['uid'];
	
	if(!$rand || $rand != $_SESSION['rand'] || !$login || !$voter_login || $login==$voter_login)
	  return $objResponse;

	$user = new users();
	$user->GetUser($login);
	$r = $user->PopVote($voter_id, $vote);
	$user->pop = $cur_val+$r*$vote;

  $objResponse->assign('idPVoteBx','innerHTML', $user->PrintPopBtnNew($voter_id,$voter_login));
  $objResponse->script('window.lockPop=0;');
 
	return $objResponse;
}

function CheckUser($login, $a=false) {
	$objResponse = new xajaxResponse();
	
	$err = 'null';
  if (!preg_match("/^[a-zA-Z0-9]+[-a-zA-Z0-9_]{2,}$/", $login))
    $err = "'Поле заполнено некорректно'";
  else {
    $user = new users();
    $user->GetUser($login);
    if($user->uid > 0)
      $err = "'Извините, этот логин занят. Придумайте другой. <a class=\"b-form__close\" href=\"#\"></a>'";
	}

    if(in_array(strtolower($login), $GLOBALS['disallowUserLogins'])) {
        $err = "'Извините, такой логин использовать нельзя <a class=\"b-form__close\" href=\"#\"></a>'";
    }

    if($a) {
        if($err != 'null') {
            $err = strip_tags($err);
            $objResponse->script("show_error('login', {$err});");
        }
    } else {
        $objResponse->script("regs.prnErr('login', {$err})");
        $objResponse->script("$$(\".b-form__close\").addEvent(\"click\", function() {
                regs.prnErr('login', '');
                $('login_block').getElement('input').set('value', '');
            });");
    }
	return $objResponse;
}


function GetFreeLogin($email) {
    $objResponse = new xajaxResponse();

    $login = '';
    
    if (isset($_SESSION['login_generated']) && $_SESSION['login_generated']) {
        $login = $_SESSION['login_generated'];
    } else {
        $reg = new registration();

        $reg->fillData(array('email' => $email));

        if ($reg->login) {
            $_SESSION['login_generated'] = $login = $reg->login;
        }
    }
    
    if ($login) {
        $objResponse->script("$('reg_login').set('value', '".$login."'); clear_error('reg_login')");
    }
    
    return $objResponse;
}

/**
 * Включаем/выключаем подписку на проекты
 * 
 * @param int $status
 * @return \xajaxResponse
 */
function togglePrj($status = 1)
{
    session_start();
    $objResponse = new xajaxResponse();
    if(!$uid = get_uid(false)) return $objResponse;
    $user = new freelancer();
    $user->GetUserByUID($uid);
    
    $status = (int)$status;
    $user->mailer = $status;
    $user->subscr[1] = $status;  
    
    $res = array();
    $user->Update($uid, $res);  
    
    return $objResponse;
}


/**
 * Добавляет категорию в подписку на проекты
 * 
 * @param int $category_id
 * @param int $subcategory_id
 * @param string $exists
 * @return \xajaxResponse
 */
function AddSubscFilter($category_id, $subcategory_id, $exists = ''){
    session_start();
    $objResponse = new xajaxResponse();
    if(!$uid = get_uid(false)) return $objResponse;
    $user = new freelancer();
    $user->GetUserByUID($uid);
    $user->mailer_str = $exists.':c'.$category_id.'s'.$subcategory_id;
    $user->mailer = 1;
    $user->subscr[1] = 1;
    
    $res = array();
    if($user->Update($uid, $res)) return $objResponse;
    
    $no_reset_filter = true;
    ob_start();
    include_once $_SERVER['DOCUMENT_ROOT'].'/user/setup/subscr_filter.php';
    $html = ob_get_clean();
    

    $objResponse->script('exists_pars[exists_pars.length] = new Array('.(int)$category_id.','.(int)$subcategory_id.');');
    $objResponse->assign('filter_body_p', 'innerHTML', $html);
    return $objResponse;
}

/**
 * Удаляет категорию из подписки на проекты
 * 
 * @param int $category_id
 * @param int $subcategory_id
 * @return \xajaxResponse
 */
function removeSubscFilter($category_id, $subcategory_id)
{
    session_start();
    $objResponse = new xajaxResponse();
    if(!$uid = get_uid(false)) return $objResponse;
    
    $user = new freelancer();
    $user->GetUserByUID($uid);   
    
    $regex = '/:?c'.$category_id.(($subcategory_id > 0)?'s'.$subcategory_id:'').'/';
    $user->mailer_str = preg_replace($regex, '', $user->mailer_str);
  
    $res = array();
    $user->Update($uid, $res);

    return $objResponse;
}



function SetSex(){
    session_start();
    $resp = array('status' => 'ok', 'alert' => 'Ok');
    if(!isset($_POST['sex'])) {
        exit('{"status":"error","alert":"Укажите ваш пол!"}');
    } else {
        $obj = new users();
        $obj->SetSex(get_uid(false),(int)$_POST['sex']);
    }
    exit (json_encode($resp));
}

 /**
 * Делает отметку о получении подарка(просмотре подарка) по id подарка и UID
 *
 * @param integer $uid
 */
function SetGiftResv($gid) {
    session_start();

    $uid = get_uid(false);

    $gid = intval($gid);
    $uid = intval($uid);

    if (!$gid || !$uid) {
        $resp['success'] = false;
        echo json_encode($resp);
        return;
    }

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/present.php");
    present::SetGiftResv($gid, $uid);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
    $is_pro = payed::CheckPro($_SESSION['login']);

    $pro_last = payed::ProLast($_SESSION['login']);
    if($pro_last['freeze_to']) {
        $_SESSION['freeze_from'] = $pro_last['freeze_from'];
        $_SESSION['freeze_to'] = $pro_last['freeze_to'];
        $_SESSION['is_freezed'] = $pro_last['is_freezed'];
        $_SESSION['payed_to'] = $pro_last['cnt'];
    }
    $_SESSION['pro_last'] = $pro_last['is_freezed'] ? false : $pro_last['cnt'];

    $resp['id'] = $gid;
    $resp['success'] = true;
    echo json_encode($resp);
    return;
}


function SetPromoBlockClosed() {
    session_start();
    $uid = get_uid(false);
    
    if (!$uid) {
        return;
    }
    
    $user = new users();
    $user->setPromoBlockShow($uid, 0);
    $user->setPromoBlockShowCookie($uid, 0);
    return;
}

/**
 * Не показывать страницу "Переход по внешней ссылке" a.php
 *
 * @param  int $uid UID пользователя.
 * @param  string $new новое значение
 * @return object xajaxResponse
 */
function setDirectExternalLinks( $uid = 0, $new = false ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( $uid == get_uid(false) ) {
    	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    	
    	$user = new users();
    	$user->setDirectExternalLinks( $uid, ($new ? 1 : 0) );
    	
    	$objResponse->script("$('a-rem').set('disabled', false);");
    }
    
    return $objResponse;
}

function getsms($phone) {
    require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate_a1.php";
    
    if($_SESSION['send_sms_time']  > time()) return;
    $_SESSION['send_sms_time'] = strtotime("+" . sms_gate::TIMEOUT_SEND); // таймаут до следующей отсылки СМС
    
    if(!preg_match("/^\+[0-9]{10,15}/mi", $phone)) {
        $sms = new sms_gate_a1($phone);
        $msg = $sms->getTextMessage(sms_gate::TYPE_ACTIVATE, $sms->generateCode());
        $success = $sms->sendSMS($msg);
        if ($success) {
            $message = $sms->getLimitMessage($count);
        } else {
            $message = sms_gate::LIMIT_EXCEED_LINK_TEXT;
            $count = sms_gate::SMS_ON_NUMBER_PER_24_HOURS;
        }
        $_SESSION['send_sms_phone'] = $phone;
        $_SESSION['send_sms_code']  = $sms->getAuthCode();
        
        $result = array(
            'success' => true,
            'message' => iconv("Windows-1251", "UTF-8//IGNORE", $message),
            'count' => $count
        );
        
        if(SMS_GATE_DEBUG) {
            $result['c'] = $sms->getAuthCode();
        }
        echo json_encode($result);
        return;
    }
    
    return;
}

function checkCode($phone, $code, $type = "bind") {
	$objResponse = new xajaxResponse();
	$success = 0;
	$reqv = sbr_meta::getUserReqvs(get_uid(false));
	$ureqv = $reqv[$reqv['form_type']];
	
	$uid   = get_uid(false);
	if($type == 'unbind') {
		$phone = $ureqv['mob_phone']; 
	}
	
	$error = false;
	
	if ( $code == $_SESSION['send_sms_code'] && ( $phone == $_SESSION['send_sms_phone'] ) )  {
		unset($_SESSION['send_sms_code'], $_SESSION['send_sms_phone']);
		$user = new users();
		
		$ureqv['mob_phone']     = $phone;
		$save_reqv['mob_phone'] = $phone;
		
		if($type == 'bind') {
			if( !($text_error = sbr_meta::setUserReqv($uid, $reqv['rez_type'], $reqv['form_type'], $save_reqv)) ) {
				sbr_meta::authMobPhone($uid, true);
				$reqv['is_activate_mob'] = 't';
				unset($_SESSION["unbind_phone_action"]);
				unset($_SESSION['send_sms_time']);
				$success = 2;
			} else {
                $error = current($text_error); // Телефон забит
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
            $success = 3;
        }
    } else {
		$error = 'Неправильный код';
	}
	
	switch ($success) {
		case 2: //Прицепили телефон
			$objResponse->script("$('safety_status')"
				. ".set('text', 'включена')"
				. ".removeClass('b-layout__txt_color_c10600')"
				. ".addClass('b-layout__txt_color_6db335');");
			$objResponse->script("$('mob_phone_text').set('html', "
				. "'<a href=\"javascript:void(0)\" onclick=\"User_Phone.unbindStart();\" "
				. "class=\"b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8\">Отвязать</a>')");
			$objResponse->script("$('mob_phone_text').removeClass('b-layout__txt_hide')");
			$objResponse->script("$('mob_code_block').addClass('b-layout__txt_hide');");
			$objResponse->script("$('buttons_step1').addClass('b-layout__txt_hide');");
			$objResponse->script("$('buttons_step2').removeClass('b-layout__txt_hide');");
			break;
	
		case 3: //Отцепили телефон
			$objResponse->script("$('safety_status')"
				. ".set('text', 'выключена')"
				. ".removeClass('b-layout__txt_color_6db335')"
				. ".addClass('b-layout__txt_color_c10600');");
			$objResponse->script("$('mob_phone_text').set('text', 'без пробелов и дефиса')");
			$objResponse->script("$('sms_sent_ok').addClass('b-layout__txt_hide');");
			$objResponse->script("$('smscode').set('value', '');");
			$objResponse->script("$('buttons_step1').removeClass('b-layout__txt_hide');");
			$objResponse->script("$('buttons_step3').addClass('b-layout__txt_hide');");			
			break;
		
		case 0:
		default :
			$objResponse->script("$('smscode').getParent().addClass('b-combo__input_error');");
	}
	
	if ($error) {
		$objResponse->script("$('sms_error').set('html', '{$error}').removeClass('b-layout__txt_hide');");
	} else {
		$objResponse->script("$('sms_error').addClass('b-layout__txt_hide');");
	}
	$objResponse->script("shadow_center();");
	
	return $objResponse; 
}

/**
  * @desc Пересчитать рейтинг за работы в портфолио
  * @param String login
 **/
function recalcUserPortfolioRating($login) {
    require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
    $objResponse = new xajaxResponse();
    if (!$login) {
        $login = '';
        $err = "Необходимо ввести логин";
    }
    if(!(hasPermissions('adm') && hasPermissions('users'))) {
        $err = "Нет достаточно прав - 2";
    }
    if ($err == '') {
        global $DB;
        $query = "SELECT recalc_portfolio_user_rating(?)";
        $val = $DB->val($query, $login);
        if ($val == -1) {
            $objResponse->script("alert('Пользователь с логином \'{$login}\' не найден.');");
            return $objResponse;
        }
        $objResponse->script("alert('Рейтинг пользователя {$login} за работы в портфолио пересчитан и составляет {$val} баллов');");
    } else {
        $objResponse->script("alert('$err');");
    }
    return $objResponse; 
}
$xajax->processRequest();
?>
