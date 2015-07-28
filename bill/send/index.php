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

$content = "{$main_path}/bill/send/content.php";
$header  = "{$main_path}header.new.php";
$footer  = "{$main_path}footer.new.html";

$js_file = array('billing.js');

$bill = new billing(get_uid(false));
$bill->setPage('send');

include ("{$main_path}/template3.php");
?>