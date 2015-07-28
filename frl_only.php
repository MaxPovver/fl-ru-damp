<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
get_uid(false);
$no_banner = 1;
$stretch_page = true;
if (!$fpath) $fpath = "";
$header = $fpath."header.php";
$footer = $fpath."footer.html";
$content = $fpath."frl_only_inner.php";
include("template2.php");
?>
