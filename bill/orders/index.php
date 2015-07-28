<?php
$g_page_id    = "0|27";
$new_site_css = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
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
$content = "{$main_path}/bill/orders/content.php";
$header  = "{$main_path}header.new.php";
$footer  = "{$main_path}footer.new.html";

$action = __paramInit('string', NULL, 'action');
$tr_id = intval($_REQUEST['transaction']);
$bill = new billing(get_uid(false));

if($bill->getOrders()) {
	foreach($bill->getOrders() as $order) {
		if($order['op_code']==135) { $bill->clearOrders(); break; }
	}
}

$bill->setPage('orders');

// делаем уведомления прочитанными
$barNotify = new bar_notify($_SESSION['uid']);
$barNotify->delNotifies( array('page'=>'bill', 'subpage'=>'orders') );


// Подготавливаем заказ и идем на страницу оплаты
if(!is_emp($bill->user['role'])) { 
    $is_user_was_pro = $bill->IsUserWasPro();
}
$pro_payed = payed::getPayedPROList( is_emp($bill->user['role'])? 'emp' : 'frl' );
foreach($pro_payed as $p) {
    $pro_type[$p['opcode']] = $p;
}
$payed_sum = 0; //реальная сумма

foreach($bill->list_service as $service) {
    $payed_sum += ($bill->pro_exists_in_list_service && ($service['pro_ammount'] > 0 || $service['op_code'] == 53) ? $service['pro_ammount'] : $service['ammount']);
}//foreach //подсчитали реальную сумму к оплате
$bill->calcPayedSum($payed_sum);
?>
<form id="form" method="post" id="payment" action="/bill/payment/">
<input type="hidden" name="transaction" value="<?=$bill->account->start_transaction($bill->user['uid'], $tr_id)?>" />
<input type="hidden" name="action" value="payment"/>
<input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
</form>
<script>document.getElementById('form').submit();</script>
<?
exit;

$js_file = array('billing.js');
include ("{$main_path}/template3.php");
 * 
 */
?>