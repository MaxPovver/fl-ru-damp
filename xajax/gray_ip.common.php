<?php
$rpath = ($rpath)? $rpath : '../';
define( 'XAJAX_DEFAULT_CHAR_ENCODING', 'windows-1251' );

require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/xajax_core/xajax.inc.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/config.php' );

global $xajax;

if (!$xajax) {
    $xajax = new xajax( '/xajax/gray_ip.server.php' );
    
    $xajax->configure( 'decodeUTF8Input', true );
    $xajax->configure( 'scriptLoadTimeout', XAJAX_LOAD_TIMEOUT );
    
    $xajax->register( XAJAX_FUNCTION, 'addPrimaryIp' );
    $xajax->register( XAJAX_FUNCTION, 'getPrimaryIpForm' );
    $xajax->register( XAJAX_FUNCTION, 'setPrimaryIp' );
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdminActionReasons', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdminActionReasonText', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('updateUserBan', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserBanForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserMassBanForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
}