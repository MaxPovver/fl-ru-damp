<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/account.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");

session_start();
get_uid(false);


/**
 * Отправить повторно код для 
 * подтверждения сохранения финансов
 * на текущий номер
 * 
 * @return \xajaxResponse
 */
function resendAcceptCode()
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
    
    $objResponse = new xajaxResponse();
    $uid = get_uid(false);
    
    if ($uid > 0 && isset($_SESSION['sms_accept_phone'])) {
        unset($_SESSION['sms_accept']);
        
        $sms_gate = new sms_gate_a1($_SESSION['sms_accept_phone']);

        if(!$sms_gate->isNextSend()) {
            
            $sms_gate->sendAuthCellCode(sms_gate::TYPE_ACTIVATE);
            
            $_SESSION['sms_accept_code'] = $sms_gate->getAuthCode();
            unset($_SESSION['sms_accept_try']);
            
            if(SMS_GATE_DEBUG) {
                $objResponse->script("$('sms_accept_code').set('value', '{$_SESSION['sms_accept_code']}')");
            }
            
            $objResponse->alert("Код выслан повторно.");
            
        } else {
            $timer = $sms_gate->next_time_send - time();
            $objResponse->alert("
                Следующее сообщение можно будет послать через {$timer} ". 
                ending($timer, 'секунду', 'секунды', 'секунд'));
        }
        
        $objResponse->script("$('sms_accept_error').addClass('b-layout__txt_hide'); $('sms_accept_code').getParent().removeClass('b-combo__input_error');");
    }
    
    return $objResponse;
}


/**
 * Проверка СМС кода для сохранения финансов
 * 
 * @param type $code
 * @return \xajaxResponse
 */
function checkAcceptCode($code) 
{
    $MAX_ACCEPT_TRY = 5;
    
    $objResponse = new xajaxResponse();
    $uid = get_uid(false);
    
    if ($uid > 0 && isset($_SESSION['sms_accept_code'])) {
        
        $error = false;
        unset($_SESSION['sms_accept']);
        
        if ($_SESSION['sms_accept_try'] >= $MAX_ACCEPT_TRY) {

            $error = 'Превышен лимит попыток ввода. Получите код повторно.';
            
        } elseif($code == $_SESSION['sms_accept_code']) {

            $_SESSION['sms_accept'] = true;
            unset($_SESSION['sms_accept_try']);
            $objResponse->script("$('financeFrm').submit();");
            
        } else {
            $_SESSION['sms_accept_try'] = !isset($_SESSION['sms_accept_try'])?1:++$_SESSION['sms_accept_try'];
            $cnt = $MAX_ACCEPT_TRY - $_SESSION['sms_accept_try'];
            if ($cnt > 0) {
                $error = "Неправильный код. Осталось попыток: {$cnt}.";
            } else {
                $error = 'Превышен лимит попыток ввода. Получите код повторно.';
            }
        }
        
        if ($error) {
            $objResponse->script("
                $('sms_accept_error').removeClass('b-layout__txt_hide').set('html', '{$error}'); 
                $('sms_accept_code').getParent().addClass('b-combo__input_error');");            
        }
    }
    
    return $objResponse;
}




function delAttach($id, $login){
    $objResponse = new xajaxResponse();
    if(!hasPermissions('users')) $login = $_SESSION['login'];
    $user = new users();
    $user->GetUser($login);
    if($user->uid) {
        $account = new account();
        $account->GetInfo($user->uid);
    }
    if(!$account->id) $err = 'Ошибка';
    //if(!sbr::isFileInReqvHistory($user->uid,$id)) {
        if(!$account->delAttach($id)) $err = 'Ошибка';
    //}
    $objResponse->call('delFinAttach', $id, $login, 1, $err);
    return $objResponse;
}


/**
 * Восстановить удаленные финансовые данные
 * 
 * @param type $uid
 * @return \xajaxResponse
 */
function repairFinData($uid)
{
    $objResponse = new xajaxResponse();
    
    if (hasPermissions('users')) {
        $user = new users();
        $user->GetUserByUID($uid);
        if ($user->uid > 0) {
            sbr_meta::repairFinance($user->uid);
            $objResponse->script('window.location.reload(true)');
        }
    }

    return $objResponse;
}


$xajax->processRequest();