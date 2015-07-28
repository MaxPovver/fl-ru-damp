<?php
/**
 * Серый список IP
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
define( 'IS_SITE_ADMIN', 1 );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/gray_ip.php");

session_start();

$uid = get_uid();

if ( !hasPermissions('grayip') ) {
    header_location_exit( '/404.php' );
}

$menu_item   = 5;
$rpath       = '../../';
$css_file   = array( 'moderation.css', 'new-admin.css', 'nav.css' );
$js_file     = array( 'gray_ip.js', 'admin_log.js', 'banned.js' );
$header      = $rpath . 'header.php';
$inner_page  = "index_inner.php";
$content     = '../content22.php';
$footer      = $rpath . 'footer.html';
$template    = 'template2.php';
$log_pp      = __paramInit( 'int', 'log_pp', 'log_pp', 20 );
$gray_ip     = new gray_ip( $log_pp );
$task        = __paramInit('string', 'task', 'task');
$page        = __paramInit( 'int', 'page', 'page', 1 );
$filter      = array();
$cmd         = __paramInit( 'string', 'cmd',         null, '' );
$search_name = __paramInit( 'string', 'search_name', null, '' );
$adm         = __paramInit( 'string', 'adm',         null, 0 );
$primary_id  = __paramInit( 'string', 'primary_id',  null, 0 );
$f_ip        = __paramInit( 'string', 'f_ip',        null, '' );
$t_ip        = __paramInit( 'string', 't_ip',        null, '' );
$admins      = $gray_ip->getAdmins();
$search_name = clearInputText( $search_name );

if ( !$page ) {
	$page = 1;
}
elseif ( $page < 0 ) {
    header_location_exit( '/404.php' );
    exit;
}

if ( $task == 'checklogin' ) {
    $login = __paramInit( 'string', 'login', 'login' );

    $result = array();
    $result['success'] = false;

    if ( $login ) {
        $users = new users();
        $users->GetUser( $login );

        if ( $users->uid ) {
            $result['success'] = true;
            $result['user']    = array(
                'uid'      => $users->uid,
                'login'    => $users->login,
                'uname'    => iconv( 'CP1251', 'UTF-8', $users->uname ),
                'usurname' => iconv( 'CP1251', 'UTF-8', $users->usurname ),
            );
        }
    }
    
    $result['test'] = $login;

    echo json_encode($result);
    exit();
}
elseif ( $task == 'pdel' ) {
	$gray_ip->deletePrimaryUser( __paramInit('int', 'puid') );
	$_SESSION['gray_ip_parent_reload'] = ( $primary_id ) ? 'yes' : '';
	header('Location: '.$_SERVER['HTTP_REFERER']);
	exit();
}
elseif ( $task == 'sdel' ) {
	$gray_ip->deleteSecondaryIp( $_REQUEST['chk_users'] );
	$_SESSION['gray_ip_parent_reload'] = ( $primary_id ) ? 'yes' : '';
	header('Location: '.$_SERVER['HTTP_REFERER']);
	exit();
}
elseif ( $task == 'mass_sdel' ) {
	$gray_ip->deleteSecondaryIpByPrimary( $_REQUEST['chk_users'] );
	$_SESSION['gray_ip_parent_reload'] = ( $primary_id ) ? 'yes' : '';
	header('Location: '.$_SERVER['HTTP_REFERER']);
	exit();
}

if ( $cmd == 'filter' ) {
    $filter = $gray_ip->getIpRange( $error, $f_ip, $t_ip );
    
    if ( !$error ) {
        $filter['primary_id']  = $primary_id;
        $filter['search_name'] = $search_name;
        $filter['admin_id']    = $adm;
    }
}

$grayIp = $gray_ip->getGrayIpList( $count, $filter, $page );
$pages  = ceil( $count / $gray_ip->items_pp );

$bWindowClose = ( $primary_id && !$grayIp ) ? true : false;

include( $rpath . $template );
