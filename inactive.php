<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
get_uid(false);
$no_personal = 0;
$no_banner = 1;
$stretch_page = 1;
$header = "header.php";
$footer = "footer.html";
$content = "inactive_inner.php";
logout();
include("template.php");
?>
