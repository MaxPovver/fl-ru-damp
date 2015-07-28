<?php

if (!$_in_setup) {
    header ("HTTP/1.0 403 Forbidden"); 
    exit;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );


unset($sbr, $stage, $reqvs);

//------------------------------------------------------------------------------

$is_adm = hasPermissions('users');
$is_owner = ($uid === get_uid(false));

if ($is_adm) { 
    $js_file[] = "banned.js";
}

//------------------------------------------------------------------------------


$account = new account();
$ok = $account->GetInfo($uid, true);

$redirect_uri = isset($_SESSION['redirect_from_finance']) && ($is_owner) ? $_SESSION['redirect_from_finance'] : '';
unset($_SESSION['redirect_from_finance']);

//------------------------------------------------------------------------------

$reqvs = sbr_meta::getUserReqvs($uid);

$is_finance_deleted = (isset($reqvs['validate_status']) && $reqvs['validate_status'] == sbr_meta::VALIDATE_STATUS_DELETED); 
//Если финансы помечены как удаленные пользователем 
//то выходим и игнорируем всю логику ниже
if ($is_finance_deleted) {
    return;
}


//------------------------------------------------------------------------------


if(isset($_GET['logout'])) {
    unset($_SESSION['is_finance_access']);
    header_location_exit("/users/{$_SESSION['login']}/setup/finance/");
}
if(hasPermissions('users')) {
    $_SESSION['is_finance_access'] = true;
}
if($reqvs['is_safety_mob'] == 't' && $_SESSION['is_finance_access'] == false) {
    $ureqv = $reqvs[$reqvs['form_type']];
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
    $sms_gate = new sms_gate_a1($ureqv['mob_phone']);
    if(!$sms_gate->isNextSend()) {
        $sms_gate->sendAuthCellCode(sms_gate::TYPE_AUTH);
        $_SESSION['sms_access_code'] = $sms_gate->getAuthCode();
    }
    
    if(SMS_GATE_DEBUG) {
        $code_debug = $_SESSION['sms_access_code'];
    }
    
    $access = false;
} elseif($reqvs['is_safety_mob'] == 'f' || $reqvs['is_safety_mob'] == null || empty($_SESSION['is_finance_access'])) {
    $access = true;
} elseif($_SESSION['is_finance_access'] == true) {
    $access = true;
}

//@todo: $access - только визульно скрывает финансы а POST всегда могу послать! 


//------------------------------------------------------------------------------


//Можно ли юзеру редактировать финансы?
$block_finance_edit = ($is_adm && !$is_owner)? false : !sbr_meta::isStatusAllowEditFinance($reqvs['validate_status']);


//------------------------------------------------------------------------------


$is_finance_allow_delete = !$is_finance_deleted && $access && $is_owner && $block_finance_edit;

//Обрабатываем событие по удалению финансов
if (__paramInit('int', NULL, 'finance_delete') == 1 && $is_finance_allow_delete) {
    
    $is_finance_deleted = sbr_meta::deleteFinance($uid);
    header_location_exit('.');
    return;
}


//------------------------------------------------------------------------------

$form_type = $reqvs['form_type'];
$rez_type = __paramInit('int', NULL, 'rez_type');

if ($rez_type) {
    $reqvs['rez_type'] = $rez_type;
} elseif ($reqvs['rez_type']) {
    $rez_type = $reqvs['rez_type'];
} else {
    $rez_type = sbr::RT_RU;
    $reqvs['rez_type'] = $rez_type;
}

//------------------------------------------------------------------------------

//Если в номере паспорта есть символы
//$is_symbols_idcard_ser = !empty($reqvs[$form_type]['idcard_ser']) && !is_numeric($reqvs[$form_type]['idcard_ser']);


//------------------------------------------------------------------------------

if ($action == 'updfin') 
{
    $error = array();
    $form_type = __paramInit('int', NULL, 'form_type');
    $is_agree_view  = __paramInit('int', NULL, 'is_agree_view_doc');
    $is_agree_view  = $is_agree_view == 1 ? 't' : 'f';

    if(($form_type || $rez_type || isset($_POST['ft'.$form_type])) && !$block_finance_edit) 
    {
        if(!$ft_disabled) $reqvs['form_type'] = $form_type;
        $mob_phone = $reqvs[$form_type]['mob_phone'];
        $reqvs[$form_type] = $_POST['ft'.$form_type];
        
        //Если в номере паспорта есть символы
        //$is_symbols_idcard_ser = !empty($reqvs[$form_type]['idcard_ser']) && !is_numeric($reqvs[$form_type]['idcard_ser']);
        
        if ($mob_phone) {
            $reqvs[$form_type]['mob_phone'] = $mob_phone;
            $reqvs[$form_type]['phone'] = $mob_phone;
        }
        
        $other_error = array();
        
        //$hasReserve = false;
        
        if (!$is_adm) {
            
            //@todo: Пока убрал эту проверку согласно https://beta.free-lance.ru/mantis/view.php?id=29351
            //
            //Если есть хоть один резерв то переводим статус реквизитов
            //в "Ожидающие проверки" иначе в "Неактивные данные"
            /*
            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
            $reserveInstance = ReservesModelFactory::getInstance(ReservesModelFactory::TYPE_TSERVICE_ORDER);
            $hasReserve = $reserveInstance->hasReserveByUserId($uid);
            $reqvs[$form_type]['validate_status'] = ($hasReserve)?1:0;
            */
            
            //после успешного сохранения отправлям на модерацию
            $reqvs[$form_type]['validate_status'] = 1;
            
            unset($_SESSION['sms_accept_code']);
            
            if ((!isset($reqvs['is_activate_mob']) || 
                 !$reqvs['is_activate_mob'] || 
                 $reqvs['is_activate_mob'] == 'f') AND (
                !isset($_SESSION['sms_accept_phone']) || 
                !isset($reqvs[$form_type]['phone']) || 
                $_SESSION['sms_accept_phone'] != $reqvs[$form_type]['phone'] || 
                !isset($_SESSION['sms_accept']) || 
                $_SESSION['sms_accept'] !== true)) {
                
                $other_error['phone'] = true;
                unset($_SESSION['sms_accept_phone'], $_SESSION['sms_accept'], $_SESSION['sms_accept_try']);
            }
            
        }
        
        
        
        $error_file = array();
        
        if ($form_type == sbr::FT_PHYS && !is_emp($u->role)) {
            // сканы документов
            $attachedFiles = new attachedfiles($_POST['attachedfiles_session']);
            
            $attachedFiles_files = $attachedFiles->getFiles(array(1,4));
            $err = $account->addAttach2($attachedFiles_files); // сохраняем файлы
            $filesExists = count($attachedFiles->getFiles()) > 0;
            $attachedFiles->clearBySession();
            
            if ($err) {
                $error_file['err_attach'] = $err;
            } elseif (!$filesExists) {
                $error_file['err_attach'] = "Необходимо загрузить скан одной или нескольких страниц паспорта.";
            }
        }

        
        $required_error = sbr_meta::checkRequired($form_type, $rez_type, $reqvs[$form_type], is_emp($u->role));
        $start_errors = array_merge($required_error, $error_file, $other_error);


        if($err = sbr_meta::setUserReqv(
                $uid, 
                $rez_type, 
                $form_type, 
                $reqvs[$form_type], 
                $ft_disabled, 
                $is_agree_view, 
                $start_errors))
        {
            if (isset($err['mob_phone'])) {
                $err['phone'] = $err['mob_phone'];
            }
            
            
            if (isset($err['phone']) && $err['phone'] === true) {
                if (count($err) == 1) {
                    //отправляем смс
                    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
                    $sms_gate = new sms_gate_a1($reqvs[$form_type]['phone']);
                    if(!$sms_gate->isNextSend()) {
                        $sms_gate->sendAuthCellCode(sms_gate::TYPE_ACTIVATE);
                        $_SESSION['sms_accept_code'] = $sms_gate->getAuthCode();
                        $_SESSION['sms_accept_phone'] = $reqvs[$form_type]['phone'];
                        
                        if(SMS_GATE_DEBUG) {
                            $code_debug = $sms_gate->getAuthCode();
                        }
                    }
                }
                
                unset($err['phone']);
            }
            
            $error['sbr'] = $err;
        }
        elseif(!$is_adm /*&& $hasReserve*/)
        {
            //Если есть сделки то отправляем обновленные данные на проверку модератору в потоки
            user_content::sendToModeration($uid, user_content::MODER_SBR_REQV);
        }
    
    }
    
    if (!$error) 
    {
        unset($_SESSION['sms_accept_code'], $_SESSION['sms_accept_phone'], $_SESSION['sms_accept']);
        
        
        $_SESSION['users.setup.fin_success'] = 1;
        
        //@todo: неиспользуется отправка письма об изменений финансов админу
        /*
        if(!hasPermissions('users')) {
            $smail = new smail();
            $smail->FinanceChanged($login);
        }
         */
        
        $uri = ($redirect_uri = __paramInit('string', NULL, 'redirect_uri'))
                ? urldecode($redirect_uri)
                : "/users/{$login}/setup/finance/";
        
        if ($redirect_uri) {
            unset($_SESSION['users.setup.fin_success']);
        }
                
        header_location_exit($uri);
    }
    
    $finance_error = $error;
}

array_push($js_file, '/scripts/finance.js');

$attach = $account->getAllAttach();
$prepared = sbr_meta::prepareFinanceFiles ($attach, $login);
$attachDoc = $prepared['attachDoc'];
$attachOther = $prepared['attachOther'];
$attachedFilesDoc = $prepared['attachedFilesDoc'];
$attachedFilesOther = $prepared['attachedFilesOther'];//@todo: не используется?

if (isset($_SESSION['users.setup.fin_success'])) {
    unset($_SESSION['users.setup.fin_success']);
    $finance_success = true;
}