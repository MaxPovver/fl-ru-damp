<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
get_uid(false);
$no_banner = 1;
$stretch_page = 1;
if (!$fpath) $fpath = "";
$header = $fpath."header.php";
$footer = $fpath."footer.html";
$action = (isset($GLOBALS['_user_action']) && $GLOBALS['_user_action'])?substr(htmlspecialchars($GLOBALS['_user_action']), 0, 15):'';
$content = $fpath."emp_only_inner.php";
include("template2.php");
?>
