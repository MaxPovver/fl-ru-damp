<?php

define('IS_SITE_ADMIN', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

$css_file  = array( 'moderation.css', 'new-admin.css', 'nav.css' );
$js_file   = array( 'siteadmin/adriver/AdriverKeyWords.js' );

if (!hasPermissions('adm') || 
    !hasPermissions('users')) {
    
    header_location_exit('/404.php');
}

$rpath       = '../../';
$content = "../content22.php";
$inner_page = "inner_index.php";
include ("{$rpath}template3.php");