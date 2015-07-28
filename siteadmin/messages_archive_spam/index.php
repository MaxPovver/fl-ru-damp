<?php
/**
 * Жалобы на спам в личных сообщениях
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
define( 'IS_SITE_ADMIN', 1 );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages_spam.php");
session_start();

$uid  = get_uid();

if ( !hasPermissions('users') ) {
    header_location_exit( '/404.php' );
}

$menu_item   = 20;
$rpath       = '../../';
$css_file    = array( 'moderation.css', 'new-admin.css', 'nav.css' );
$js_file     = array( 'admin_log.js', 'messages_spam.js', 'banned.js' );
$header      = $rpath . 'header.php';
$inner_page  = "index_inner.php";
$content     = '../content22.php';
$footer      = $rpath . 'footer.html';
$template    = 'template2.php';

$log_pp = __paramInit( 'int', 'log_pp', 'log_pp', 20 );
$oSpam  = new messages_spam( $log_pp );
$filter = array();
$error  = '';

$task = __paramInit('string',  'task',  'task');
$cmd  = __paramInit( 'string', 'cmd',    null,  '' );
$page = __paramInit( 'int',    'page',  'page', 1 );
$toDs = __paramInit( 'string', 's_to_d', null,  date('d') );
$toMs = __paramInit( 'string', 's_to_m', null,  date('m') );
$toYs = __paramInit( 'string', 's_to_y', null,  date('Y') );
$toDc = __paramInit( 'string', 'c_to_d', null,  date('d') );
$toMc = __paramInit( 'string', 'c_to_m', null,  date('m') );
$toYc = __paramInit( 'string', 'c_to_y', null,  date('Y') );

if ( !$page ) {
	$page = 1;
}
elseif ( $page < 0 ) {
    header_location_exit( '/404.php' );
    exit;
}

if ( $task == 'del' ) {
	$oSpam->deleteSpamByMsg( $_REQUEST['sid'], $_REQUEST['md5'], 1 );
	header('Location: '.$_SERVER['HTTP_REFERER']);
	exit();
}
$resolve = array();
if ( $cmd == 'go' ) {
    $fromDs    = __paramInit( 'string', 's_from_d',  null, '' );
    $fromMs    = __paramInit( 'string', 's_from_m',  null, '' );
    $fromYs    = __paramInit( 'string', 's_from_y',  null, '' );
    $fromDc    = __paramInit( 'string', 'c_from_d',  null, '' );
    $fromMc    = __paramInit( 'string', 'c_from_m',  null, '' );
    $fromYc    = __paramInit( 'string', 'c_from_y',  null, '' );
    $spamer    = __paramInit( 'string', 'spamer',    null, '' );
    $spamer_ex = __paramInit( 'string', 'spamer_ex', null, '' );
    $kwd       = __paramInit( 'string', 'kwd',       null, '' );
    $user      = __paramInit( 'string', 'user',      null, '' );
    $user_ex   = __paramInit( 'string', 'user_ex',   null, '' );
    $resolve   = __paramInit( 'array_int', 'resolve',null, array() );
    
    $spamer    = clearInputText( $spamer );
    $kwd       = clearInputText( $kwd );
    $user      = clearInputText( $user );
    
    $filter = $oSpam->getDatePeriod( $error, 's', $fromDs, $fromMs, $fromYs, $toDs, $toMs, $toYs );
    
    if ( !$error ) {
        $aDateC = $oSpam->getDatePeriod( $error, 'c', $fromDc, $fromMc, $fromYc, $toDc, $toMc, $toYc );
        
        if ( !$error ) {
            $filter = array_merge( $filter, $aDateC );
            $filter['spamer']    = $spamer;
            $filter['spamer_ex'] = $spamer_ex;
            $filter['kwd']       = $kwd;
            $filter['user']      = $user;
            $filter['user_ex']   = $user_ex;
            if(count($resolve) == 0 || $resolve == "") {
                $filter['resolve']   = "{1,2,3}";
            } else {
                $filter['resolve']   = "{".implode(", ", $resolve)."}";
            }
        }
    }
}

if ( !$error ) {
    if(!$filter) {
        $filter['resolve']   = "{1,2,3}";
    }
    $spam  = $oSpam->getSpam( $count, $filter, $page );
    $pages = ceil( $count / $oSpam->items_pp );
    
    if ( !$spam && $page > 1 ) {
    	$sHref = e_url( 'page', null );
    	header( 'Location: '.$sHref );
    	exit;
    }
}

$aDays   = range( 1, 31 );
$aYears  = array_reverse( range(2011, date('Y')) );
$aMounth = array(
    '01' => "Января", 
    '02' => "Февраля", 
    '03' => "Марта", 
    '04' => "Апреля", 
    '05' => "Мая", 
    '06' => "Июня", 
    '07' => "Июля", 
    '08' => "Августа", 
    '09' => "Сентября", 
    '10' => "Октября", 
    '11' => "Ноября", 
    '12' => "Декабря"
);

include( $rpath . $template );