<?php

define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$hide_banner_top = true;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
	session_start();
	get_uid();
	if (!hasPermissions('adm') && $_SESSION['login'] != "noncash")
		{$no_banner = 0; include ABS_PATH."/404.php"; exit;}
$content = "content.php";
$rpath = "../";

$inner_page = "inner_index.php";
$css_file = array('nav.css', 'moderation.css', 'new-admin.css' );

$header = "../header.php";
$footer = "../footer.html";

include ("../template.php");