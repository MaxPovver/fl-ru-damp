<?php 

$g_page_id = "0|90";
$rpath = "../../";
$promo = false;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

$uid = get_uid();

if(!$uid) {
    include $_SERVER['DOCUMENT_ROOT']."/fbd.php";
    exit;
}

$_SESSION['do_not_show_splash'] = 1;
$from_blocked_prj = intval($_GET['b']);

$header  = $rpath . "header.php";
$footer  = $rpath . "footer.html";
$content = "tpl.freelancer.php";

include ($rpath . "template2.php");

unset($_SESSION['do_not_show_splash']);
?>