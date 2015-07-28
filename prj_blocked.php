<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
get_uid(false);
//$no_personal = 1;
//$no_banner = 1;
$g_page_id = "0|26";
$rpath = "./";
if (!$fpath) $fpath = "";
$header = ABS_PATH."/header.php";
$footer = ABS_PATH."/footer.html";
$content = ABS_PATH."/prj_blocked_inner.php";
include("template2.php");
?>
