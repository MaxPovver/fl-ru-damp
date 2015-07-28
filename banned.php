<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/admin_log.php");
session_start();
get_uid(false);
$no_personal = 0;
$no_banner = 1;
$stretch_page = 1;

if (!$fpath) $fpath = "";
$header = $fpath."header.php";
$footer = $fpath."footer.html";
$content =$fpath."banned_inner.php";
include("template.php");
?>