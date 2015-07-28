<?php
/**
 * Поиск пользователей
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
define( 'IS_SITE_ADMIN', 1 );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_search.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/admin_log.php");
session_start();

$uid = get_uid();

if ( !hasPermissions('users') ) {
    header_location_exit( '/404.php' );
}

$_SESSION['admin_log_user'] = $_SERVER['REQUEST_URI'];

$menu_item   = 4;
$rpath       = '../../';
$css_file    = array( 'moderation.css', 'new-admin.css', 'nav.css', '/css/block/b-voting/b-voting.css' );
$js_file     = array( 'zeroclipboard/ZeroClipboard.js', 'user_search.js', 'admin_log.js', 'banned.js' );
$header      = $rpath . 'header.php';
$inner_page  = "index_inner.php";
$content     = '../content22.php';
$footer      = $rpath . 'footer.html';
$template    = 'template2.php';

$filter       = array();
$cmd          = __paramInit( 'string', 'cmd',          null, '' );
$search_name  = __paramInit( 'string', 'search_name',  null, '' );
$search_phone = __paramInit( 'string', 'search_phone', null, '' );
$who          = __paramInit( 'string', 'who',          null, '' );
$status       = __paramInit( 'string', 'status',       null, '' );
$f_ip         = __paramInit( 'string', 'f_ip',         null, '' );
$t_ip         = __paramInit( 'string', 't_ip',         null, '' );
$page         = __paramInit( 'int',    'page',        'page', 1 );
$log_pp       = __paramInit( 'int',    'log_pp',      'log_pp', 20 );
$filter_uid = __paramInit('int', 't_uid', null, '');

$search_name  = clearInputText( $search_name );
$search_phone = clearInputText( $search_phone );
$user_search  = new user_search( $log_pp );

if ( !$page ) {
	$page = 1;
}
elseif ( $page < 0 ) {
    header_location_exit( '/404.php' );
    exit;
}

$search_name_exact  = __paramInit( 'string', 'search_name_exact',  null, '' );
$search_phone_exact = __paramInit( 'string', 'search_phone_exact', null, '' );

if ( $cmd == 'filter' ) {
    $filter = $user_search->getIpRange( $error, $f_ip, $t_ip );
    
    if ( !$error ) {
        
        $filter['search_name']  = $search_name;
        $filter['search_phone'] = $search_phone;
        $filter['who']          = $who;
        $filter['status']       = $status;
        
        $filter['search_name_exact']  = $search_name_exact;
        $filter['search_phone_exact'] = $search_phone_exact;
        
        $filter['uid'] = $filter_uid;
        
        //$aReasons = admin_log::getAdminReasons( 1 );
        //$users = $user_search->searchUsers( $count, $filter, $page );
        //$pages = ceil( $count / $user_search->items_pp );
        
        $users = $user_search->searchUsersBySphinx($count, $filter, $page);
        $pages = ceil($count / $user_search->items_pp);
        
        if ( !$users && $page > 1 ) {
        	$sHref = e_url( 'page', null );
        	header( 'Location: '.$sHref );
        	exit;
        }
    }
}

include( $rpath . $template );
