<?
$g_page_id = "0|51";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();

if (!get_uid(0) || is_emp()) {
    include $_SERVER['DOCUMENT_ROOT']."/403.php";
    exit;
}

$period = "1 неделю";
$cost = 5;

$stretch_page = true;
$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.pro_test_payed.php";

include ("../template2.php");
?>