<?
$g_page_id = "0|41";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
get_uid(false);
$no_banner = 1;
$stretch_page = 1;
if (!$fpath) $fpath = "";
$js_file[] = "/css/block/b-eye/b-eye.js";
$header = $fpath."header.php";
$footer = $fpath."footer.html";
$content = $fpath."fbd_inner.php";
include("template2.php");
?>