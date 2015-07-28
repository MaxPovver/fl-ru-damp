<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupFactory.php');

$uid = get_uid(false);

if (!$uid) {
    header_location_exit('/404.php');
}


$_SESSION['quickprj_is_success'] ='y';
if ($_SESSION['quickprj_is_begin']==1) {
    unset($_SESSION['quickprj_is_begin']);
    
    $memBuff = new memBuff();
    $project_id = $memBuff->get('bill_ok_project_'.$uid);
                
    if($project_id) {
        
        $is_payed = $memBuff->get('bill_ok_project_payed_'.$uid);
        
        if ($is_payed) {
            $memBuff->delete('bill_ok_project_payed_'.$uid);
            header("Location: /public/?step=2&public={$project_id}");
        } else {
            $friendly_url = getFriendlyURL('project', $project_id);
            $_SESSION['quickprj_ok'] = 1;
            header('Location: '.$friendly_url.'?quickprj_ok=1');
        }
        
        $memBuff->delete('bill_ok_project_'.$uid);
        
    } else {
        header('Location: /');
    }
    
    exit;
}

$_SESSION['quickmas_is_success'] ='y';
if ($_SESSION['quickmas_is_begin']==1) {
   unset($_SESSION['quickmas_is_begin']);
    if($_SESSION['referer']) {
        $friendly_url = strtok($_SESSION["referer"],'?');
        $_SESSION['quickmass_ok'] = 1;
        header('Location: '.$friendly_url.'?quickmas_ok=1');
    } else {
        header('Location: /?quickmas_ok=1');
    }
    exit;
}


$_SESSION['quickbuypro_is_success'] ='y';
if ($_SESSION['quickbuypro_is_begin']==1) {
    unset($_SESSION['quickbuypro_is_begin']);
    $opcode = @$_SESSION['quickbuypro_success_opcode2'];
    unset($_SESSION['quickbuypro_success_opcode2']);
                        
    if ($opcode == 164) {
        header('Location: /profi/?quickprofi_ok=1');
    } else {
        $uri = $_SESSION['quickbuypro_redirect'] ? : (is_emp() ? '/payed-emp/' : '/payed/');
        unset($_SESSION['quickbuypro_redirect']);
        header('Location: '.$uri.'?quickpro_ok=1');
    }
    exit;
}

if (__paramInit('string', 'quickprobuy', 'quickprobuy', null)==1) {
	$_SESSION['quickpro_order'] = 'done';
    echo "<html><body><script>window.close();</script></body></html>";
    exit;
}


//После успешной оплаты по банковской карте за верификацию закрываем окно
if ($_SESSION['quickver_is_begin'] == 1) {
    unset($_SESSION['quickver_is_begin']);
    echo "<html><body><script>window.close();</script></body></html>";  
    exit;
}


if ($_SESSION['quickacc_is_begin']==1) {
    unset($_SESSION['quickacc_is_begin']);
    header('Location: /bill/history/?period=3'); 
    exit;
}


//Если есть обработчики по новым попапам быстрой оплаты
if (quickPaymentPopupFactory::isExistProcess()) {
    //Посылаем событие при успешной операции
    $processInstance = quickPaymentPopupFactory::getInstance();
    if($processInstance) $processInstance->fireEventSuccess();
}


//Редиректим на историю заказов
header('Location: /bill/history/?period=3'); 
exit;