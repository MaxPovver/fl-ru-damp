<?php

$error404_page = 1;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

session_start();
get_uid(false);
//$no_personal = 1;
//$no_banner = 1;
$g_page_id = "0|26";
$rpath = "./";
if (!$fpath) $fpath = "";
$header = ABS_PATH."/header.new.php";
$footer = ABS_PATH."/footer.new.html";
$content = ABS_PATH."/404_inner.php";
$page_title = "404 Not Found";

header("HTTP/1.0 404 Not Found");
include("template3.php");