<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
get_uid(false);
$no_personal = 1;
$no_banner = 1;
$stretch_page = 1;
$rpath = "./";
if (!$fpath) $fpath = "";
$header = $fpath."header.php";
$footer = $fpath."footer.html";
$content = $fpath."501_inner.php";
include("template.php");
?>
