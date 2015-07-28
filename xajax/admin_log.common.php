<?php
$rpath = ($rpath)? $rpath : '../';
define( 'XAJAX_DEFAULT_CHAR_ENCODING', 'windows-1251' );

require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/xajax_core/xajax.inc.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/config.php' );

global $xajax;

if (!$xajax) {
    $xajax = new xajax( '/xajax/admin_log.server.php' );
    
    $xajax->configure( 'decodeUTF8Input', true );
    $xajax->configure( 'scriptLoadTimeout', XAJAX_LOAD_TIMEOUT );
    
    $xajax->register( XAJAX_FUNCTION, 'getComments' );
    $xajax->register( XAJAX_FUNCTION, 'setPrjBlockForm' );
    $xajax->register( XAJAX_FUNCTION, 'updatePrjBlock' );
    $xajax->register( XAJAX_FUNCTION, 'getUserWarns' );
    
    $xajax->register( XAJAX_FUNCTION, 'setOfferBlockForm' );
    $xajax->register( XAJAX_FUNCTION, 'updateOfferBlock' );
    
    $xajax->register( XAJAX_FUNCTION, 'getLastIps' );
    $xajax->register( XAJAX_FUNCTION, 'getLastEmails' );
    $xajax->register( XAJAX_FUNCTION, 'updateMoneyBlock' );
    $xajax->register( XAJAX_FUNCTION, 'nullRating' );
    $xajax->register( XAJAX_FUNCTION, 'activateUser' );
    $xajax->register( XAJAX_FUNCTION, 'updateSafetyPhone' );
    $xajax->register( XAJAX_FUNCTION, 'updateSafetyIp' );
    $xajax->register( XAJAX_FUNCTION, 'updateEmail' );
    $xajax->register( XAJAX_FUNCTION, 'updatePop' );
    
    $xajax->register( XAJAX_FUNCTION, 'stopNotifications' );
    $xajax->register( XAJAX_FUNCTION, 'setVerification' );
    $xajax->register( XAJAX_FUNCTION, 'saveExcDate' );
    $xajax->register( XAJAX_FUNCTION, 'getLoadExcDate' );
    
    $xajax->register( XAJAX_FUNCTION, 'setReasonBold' );
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdminActionReasons', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdminActionReasonText', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('updateUserBan', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserBanForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserMassBanForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    
    //комментарии
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('EditComment', $_SERVER['DOCUMENT_ROOT'] . '/xajax/comments.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('RateComment', $_SERVER['DOCUMENT_ROOT'] . '/xajax/comments.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('GetComment',  $_SERVER['DOCUMENT_ROOT'] . '/xajax/comments.server.php') );
    
    // предупреждения
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserWarnForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('updateUserWarn', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
}
