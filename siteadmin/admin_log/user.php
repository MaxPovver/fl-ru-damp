<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

$sUid = __paramInit( 'string', 'uid' );

$js_file[] = 'zeroclipboard/ZeroClipboard.js';
$js_file[] = 'banned.js';

if ( $sUid ) {
    if ( !preg_match('/^[\d]+$/', $sUid) ) {
    	header_location_exit( '/404.php' );
    }
    
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_search.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    
    $user_search = new user_search( $log_pp );
    
    $user = $user_search->getUserByUid( $sUid );
    $act  = __paramInit( 'string', 'act', null, 0 );
	$log  = $admin_log->getLogUserById( $nCount, $sUid, $act );
	$stat = $admin_log->getLogCounts( admin_log::OBJ_CODE_USER, $sUid );
}
else {
    $_SESSION['admin_log_user'] = $_SERVER['REQUEST_URI'];
    
    $actions  = $admin_log->getAdminActions( admin_log::OBJ_CODE_USER );
    $admins   = $admin_log->getAdminsInLog( admin_log::OBJ_CODE_USER );
    $aReasons = admin_log::getAdminReasons( 1 );
    
    $filter    = array();
    $cmd       = __paramInit( 'string', 'cmd',    null, '' );
    $toD       = __paramInit( 'string', 'to_d',   null, date('d') );
    $toM       = __paramInit( 'string', 'to_m',   null, date('m') );
    $toY       = __paramInit( 'string', 'to_y',   null, date('Y') );
    $act       = __paramInit( 'string', 'act',    null, 0 );
    $adm       = __paramInit( 'string', 'adm',    null, 0 );
    $search    = __paramInit( 'string', 'search', null, '' );
    $order     = __paramInit( 'string', 'sort',   null, 'date' );
    $direction = __paramInit( 'string', 'dir',    null, 'desc' );
    $search    = clearInputText( $search );
    
    if ( $cmd == 'filter' ) {
        $fromD = __paramInit( 'string', 'from_d', null, '' );
        $fromM = __paramInit( 'string', 'from_m', null, '' );
        $fromY = __paramInit( 'string', 'from_y', null, '' );
        
        $filter = admin_log::getDatePeriod( $error, $fromD, $fromM, $fromY, $toD, $toM, $toY );
        
        $filter['act_id']   = $act;
        $filter['admin_id'] = $adm;
        $filter['search']   = $search;
    }
    
    if ( !$error ) {
        $log   = $admin_log->getLogUser( $count, $filter, $page, $order, $direction );
        $pages = ceil( $count / $admin_log->getLogPerPage() );
    }
}