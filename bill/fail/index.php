<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupFactory.php');


//Если есть обработчики по новым попапам быстрой оплаты
if (quickPaymentPopupFactory::isExistProcess()) {
    //Посылаем событие при неудачной операции
    $processInstance = quickPaymentPopupFactory::getInstance();
    if($processInstance) $processInstance->failEventSuccess();
}


//@todo: пережико старья пока оставляю
$back_url = $_SESSION['referer'];
unset($_SESSION['referer']);
$back_url = ($back_url)?$back_url:'/';
header("Location: {$back_url}");
//Шлём туда, откуда пришли. ХЗ что тут, но в попапах все само показывалось, а теперь тут надо данные готовить.
//##0026732
exit;



if (!get_uid(0)) {
    header_location_exit('/404.php');
}

$_SESSION['quickacc_is_success'] ='n';
if($_SESSION['quickacc_is_begin']==1) {
    echo "<html><body><script>window.close();</script></body></html>";
    exit;
}

$_SESSION['quickmas_is_success'] ='n';
if($_SESSION['quickmas_is_begin']==1) {
    echo "<html><body><script>window.close();</script></body></html>";
    exit;
}

$_SESSION['quickcar_is_success'] ='n';
if($_SESSION['quickcar_is_begin']==1) {
    echo "<html><body><script>window.close();</script></body></html>";
    exit;
}

$_SESSION['quickbuypro_is_success'] ='n';
if($_SESSION['quickbuypro_is_begin']==1) {
    echo "<html><body><script>window.close();</script></body></html>";
    exit;
}


if(__paramInit('string', 'quickprobuy', 'quickprobuy', null)==1) {
    unset($_SESSION['quickpro_order']);
    echo "<html><body><script>window.close();</script></body></html>";
    exit;
}


//После неудачной оплаты по банковской карте за верификацию закрываем окно
if ($_SESSION['quickver_is_begin'] == 1) {
    unset($_SESSION['quickver_is_begin']);
    echo "<html><body><script>window.close();</script></body></html>";  
    exit;
}


$bill = new billing(get_uid(0));

$action = __paramInit('string', null, 'action', null);
// заново оплатить
if ($action === 'pay') {
    $reserveID = __paramInit('string', null, 'reserve_id', null);
    if ($reserveID) {
        $success = $bill->setReserveStatus($reserveID, billing::RESERVE_CANCEL_STATUS);
        if ($success) {
            if ($bill->updateOrderListStatus($reserveID, billing::STATUS_NEW)) {
                header_location_exit('/bill/orders/');
            }
        }
    }
}

$bill->setPage('fail');
if (!count($bill->list_service)) {
    header_location_exit('/404.php');
}
$reserveData = current($bill->list_service);
$js_file = array('billing.js');
$content = "content.php";
$header = "../../header.new.php";
$footer = "../../footer.new.html";
include ("../../template3.php");
?>