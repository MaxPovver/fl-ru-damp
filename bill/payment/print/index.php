<?php
$g_page_id    = "0|27";
$new_site_css = true;
$print        = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");

$main_path = $_SERVER['DOCUMENT_ROOT'];

if(!get_uid()) {
    header('Location: /fbd.php'); 
    exit;
}

$content = "{$main_path}/bill/payment/print/content.php";
$header  = "{$main_path}header.new.php";
$footer  = "{$main_path}footer.new.html";

$js_file = array('billing.js', '/scripts/b-combo/b-combo-phonecodes.js');

$type_payment = __paramInit('string', 'type', NULL, 'webmoney');

$bill = new billing(get_uid(false));
$bill->setPage('index');
$bill->setPaymentMethod($type_payment);

if($bill->type_menu_block == '') {
    header("Location: /403.php");
    exit;
} 

include ($content);
?>