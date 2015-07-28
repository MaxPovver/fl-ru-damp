<?php
define( 'IS_SITE_ADMIN', 1 );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");;
session_start();

$uid = get_uid();

if ( !hasPermissions('userphone') ) {
    header_location_exit( '/404.php' );
}

$_SESSION['admin_log_user'] = $_SERVER['REQUEST_URI'];

$menu_item   = 4;
$rpath       = '../../';
$css_file    = array( 'moderation.css', 'new-admin.css', 'nav.css' );
$js_file     = array( 'zeroclipboard/ZeroClipboard.js', 'user_search.js', 'admin_log.js', 'banned.js' );
$header      = $rpath . 'header.php';
$inner_page  = "index_inner.php";
$content     = '../content22.php';
$footer      = $rpath . 'footer.html';
$template    = 'template2.php';

$filter       = array();
$cmd          = __paramInit( 'string', 'cmd',         'cmd', '' );
$page         = __paramInit( 'int',    'page',        'page', 1 );
$search_phone = __paramInit( 'string', 'search_phone','search_phone', '' );

$search_phone = clearInputText( $search_phone );
$sbr_meta  = new sbr_meta();

if ( !$page ) {
	$page = 1;
} elseif ( $page < 0 ) {
    header_location_exit( '/404.php' );
    exit;
}

$search_phone_exact = __paramInit( 'string', 'search_phone_exact', null, '' );

if ( $cmd == 'filter' ) {
    
    $filter['search_phone']       = $search_phone;
    $filter['search_phone_exact'] = $search_phone_exact;
    
    
    $users = $sbr_meta->searchUsersPhone( $count, $filter, $page );
    $pages = ceil( $count / 50 );

    if ( !$users && $page > 1 ) {
        $sHref = e_url( 'page', null );
        header( 'Location: '.$sHref );
        exit;
    }
}

include( $rpath . $template );
