<?php

//@todo: ƒанный раздел /bill/payment/ более не используетс€
//сто»т вопрос об его удалении

//$g_page_id    = "0|27";
//$new_site_css = true;
//$print        = false;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bar_notify.php");

//$main_path = $_SERVER['DOCUMENT_ROOT'];

if(!get_uid(false)) {
    header('Location: /fbd.php'); 
    exit;
}

//–едиректим на историю заказов
header('Location: /bill/history/?period=3'); 
exit;


/*
$content = "{$main_path}/bill/payment/content.php";
$header  = "{$main_path}header.new.php";
$footer  = "{$main_path}footer.new.html";

$js_file = array('billing.js', '/scripts/b-combo/b-combo-phonecodes.js');

$type_payment = __paramInit('string', 'type', NULL, 'webmoney');

$bill = new billing(get_uid(false));
$bill->setPage('orders');
$bill->setPaymentMethod($type_payment);

if($bill->type_menu_block == '') {
    header("Location: /404.php");
    exit;
}
 
foreach($bill->list_service as $service) { 
    $payed_sum += ($bill->pro_exists_in_list_service && ($service['pro_ammount'] > 0 || $service['op_code'] == 53) ? $service['pro_ammount']: $service['ammount']);//$service['ammount'];
}

$action = __paramInit('string', NULL, 'action');
if($action == 'payment') {
    $payment_sum = $payed_sum - $bill->acc['sum']; //ceil($payed_sum - $bill->acc['sum']) > 10 ? ceil($payed_sum - $bill->acc['sum']) : ($bill->acc['sum'] > $payed_sum ? 0 : 10);
    
    if($payment_sum <=0) { // ≈сли хватает средств полностью оплатить с личного счета
        $bill->transaction = intval($_REQUEST['transaction']);
        $ok = $bill->preparePayments($payed_sum, true);
        if($ok) {
            $complete = $bill->completeOrders($bill->reserved);
            if($complete) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
                $ret[$bill->reserved] = $bill->reserve;
                $mail_reserved[] = $bill->reserved;
                
                if (substr($bill->user['subscr'], 15, 1) == 1) {
                    //$smail = new smail();
                    //$smail->sendReservedOrders($ret, $mail_reserved);
                }
                
                header_location_exit("/bill/success/");
            }
        }
    }
}

$bill->calcPayedSum($payed_sum, $bill->acc['sum']<0 ? 0 : $bill->acc['sum']);
if ($payed_sum <= 0) {
    header("Location: /bill/orders/");
    exit;
}

// делаем уведомлени€ прочитанными
$barNotify = new bar_notify($_SESSION['uid']);
$barNotify->delNotifies( array('page'=>'bill') );


include ("{$main_path}/template3.php");
*/