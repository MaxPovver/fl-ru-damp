<?php

define( 'IS_SITE_ADMIN', 1 );
ob_start();
$no_banner = 1;

$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");

session_start();
get_uid();

if (!hasPermissions('payments')) {
    header ("Location: /404.php"); 
    exit;
}
	
$content = "../content.php";
$css_file = array(
    'moderation.css',
    'new-admin.css', 
    'nav.css',
    'calendar.css');

$inner_page = "inner_index.php";
$js_file = array( 'calendar.js' );
$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");
ob_end_flush();