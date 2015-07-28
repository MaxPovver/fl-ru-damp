<?php

define('IS_OPAUTH', true);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opauth/OpauthHelper.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opauth/OpauthModel.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard.php"); 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/SubBarNotificationHelper.php");

$uid = get_uid(false);

if ($uid) {
    $type = OpauthHelper::ACTION_BIND;
    $multilevel = OpauthHelper::getMultilevel();
    $uri_part = $multilevel ? 'safety' : 'main';
    $back_url = '/users/' . $_SESSION['login'] . '/setup/' . $uri_part . '/';
} else {
    $type = OpauthHelper::ACTION_REGISTER;
    $back_url = '/registration/';
}

$Opauth = new Opauth(OpauthHelper::getConfig(), false);

$response = $_SESSION['opauth'];
unset($_SESSION['opauth_error']);

$is_valid = $Opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason);
$opauth_error = OpauthHelper::getError($is_valid, $response);

if ($opauth_error) {
    $_SESSION['opauth_error'] = $opauth_error;
    header_location_exit($back_url);
}

$opauthModel = new OpauthModel();
$opauthModel->setData($response);

$emp_redirect = OpauthHelper::getEmpRedirect();

$user = $opauthModel->getUser();
if ($user) {
    //Уже есть привязка
    unset($_SESSION['opauth']);
    if ($type == OpauthHelper::ACTION_REGISTER) {
        
        $id = login($user['login'], $user['passwd'], 1); 
        
        $customRedirect = is_emp($user['role']) ? $emp_redirect : '';
        $back_url = !empty($customRedirect)? $customRedirect : (isset($_SESSION['ref_uri'])? urldecode($_SESSION['ref_uri']) : null);
        
        
        if ($id == users::AUTH_STATUS_2FA) {
            if (!empty($customRedirect)) {
                $_SESSION['2fa_redirect'] = array('redirectUri' => $customRedirect);
            }
            //Редирект на 2ой атап авторизации
            $back_url = '/auth/second/';
        } elseif (!$back_url) {
            $back_url = (is_emp() ? '/' : '/projects/');
        }
        
        
        
        //Успешная авторизация
        if ($id > 0) {
            
            //Отправляем в очередь событие об успешной авторизации
            if ($type = $opauthModel->getShortType()) {
                require_once(ABS_PATH . '/classes/statistic/StatisticFactory.php');
                $ga = StatisticFactory::getInstance('GA');
                $ga->queue('event', array(
                    'uid' => $id,
                    'cid' => users::getCid(),
                    'category' => is_emp()?'customer':'freelancer',
                    'action' => 'authorization_passed',
                    'label' => $type
                ));
            }

        }
        
    } else {
        $_SESSION['opauth_error'] = "Данный аккаунт социальной сети уже привязан к другому пользователю";
    }
    
    header("Location: {$back_url}");
    exit;
    
} else {
    if ($type == OpauthHelper::ACTION_REGISTER) {
        $registrationData = OpauthHelper::getRegistrationData($response);

        $postedRole = ($emp_redirect)? registration::ROLE_EMPLOYER : __paramInit('int', null, 'role_db_id');
        $postedEmail = __paramInit('string', null, 'email');
        $postedLogin = __paramInit('string', null, 'login');

        if ($postedRole && $postedEmail && $postedLogin) {
            $registrationData['role'] = $postedRole;
            $registrationData['email'] = $postedEmail;
            $registrationData['login'] = $postedLogin;

            $registration = new registration();
            $status = $registration->actionRegistrationOpauth($registrationData);

            if (isset($status['success']) && $status['success'] == true) {
                unset($_SESSION['opauth']);
                unset($_SESSION['opauth_role']);

                $opauthModel->create($status['user_id']);
                
                $redirect = $status['redirect'];
                
                if (is_emp() && $emp_redirect) {
                    $redirect = $emp_redirect;
                }
                
                header("Location: " . $redirect);
                exit;
            }
        }
    } else {
        unset($_SESSION['opauth']);
        unset($_SESSION['opauth_role']);
        unset($_SESSION['opauth_multilevel']);

        $opauthModel->create($uid, false, $multilevel);
        header("Location: " . $back_url);
        exit;
    }
    
}


$redirectUri = null;
if (isset($_SESSION['ref_uri'])) {
    $redirectUri = $_SESSION['ref_uri'];
}


$js_file = array(
    'opauth' => 'registration/opauth.js'
);

$hide_banner_top = true;

$content = "content.php";
$header = "../../header.php";
$footer = "../../footer.html";

include ("../../template3.php");