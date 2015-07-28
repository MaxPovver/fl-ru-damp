<?php

/**
 * Статистика ТУ
 * 
 */
define('IS_SITE_ADMIN', 1);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

hasPermissions('tservices') || header_location_exit('/404.php');
$rpath = "../../";

$css_file = array(
    'moderation.css',
    'new-admin.css',
    'nav.css');
$header = $rpath . 'header.new.php';
$content = '../content.php';
$footer = $rpath . 'footer.new.html';
$template = 'template3.php';

$data = array();

$mode = __paramInit('string', 'mode', null, ''); // раздел

if ( !in_array($mode, array('orders')) ) {
    header ("Location: /404.php"); 
    exit;
}

switch ($mode) {
    case 'orders':

        $inner_page = "orders_inner.php";
        $css_file[] = 'calendar.css';
        $js_file = array( 'calendar.js' );
        
        break;
}

include($rpath . $template);