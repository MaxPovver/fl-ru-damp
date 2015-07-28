<?
$g_page_id = "0|56";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();

$count = __paramInit('int', 'count', null, 0);
$cost = __paramInit('float', 'cost', null, 0);

$stretch_page = true;
$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.masssending_done.php";

include ("../template2.php");
?>