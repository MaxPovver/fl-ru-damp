<?php
$g_page_id    = "0|27";
$new_site_css = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bar_notify.php");

$main_path = $_SERVER['DOCUMENT_ROOT'];

if(!get_uid()) {
    header('Location: /fbd.php'); 
    exit;
}

//Редиректим на историю заказов
header('Location: /bill/history/?period=3'); 
exit;

/*
$content = "{$main_path}/bill/content.php";
$header  = "{$main_path}header.new.php";
$footer  = "{$main_path}footer.new.html";

$js_file = array('billing.js');

$bill = new billing(get_uid(false));

$ammount = $bill->getTotalAmmountOrders();
if ($ammount && (int)$bill->acc['sum'] > (int)$ammount) {
    $bill->preparePayments($ammount);
    $bill->completeOrders();
}

$bill->setPage();

// делаем уведомления прочитанными
$barNotify = new bar_notify($_SESSION['uid']);
$barNotify->delNotifies( array('page'=>'bill') );


include ("{$main_path}/template3.php");
 * 
 */
?>