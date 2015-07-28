<?php
define( 'IS_SITE_ADMIN', 1 );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );

session_start();
$uid = get_uid();

if ( !isset($aPermissions) ) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");
    $aPermissions = permissions::getUserPermissions( $uid );
}

if ( !admin_log::isAllowed( 'adm', $aPermissions ) ) {
    header_location_exit( '/404.php' );
}

$admin_log  = new admin_log( 'log', $uid, $aPermissions );
$aActions   = $admin_log->getAdminActions();
$sAction    = __paramInit( 'string', 'action', 'action', '' );
$reasonId   = __paramInit( 'string', 'id', 'id', 0 );
$act_sel    = __paramInit( 'string', 'act_sel', 'act_sel', $aActions[0]['id'] );
$aReason    = array();
$sNameError = '';
$sTextError = '';
$bFound     = false;

foreach ( $aActions as $aOne ) { 
    if ( $aOne['id'] == $act_sel ) {
    	$bFound = true;
    	break;
    }
}

if ( !$bFound ) {
	header_location_exit( '/404.php' );
}

if ($sAction && count($_POST) && $_POST["u_token_key"] != $_SESSION["rand"] ) {
    header_location_exit( '/404.php' );
}
switch ( $sAction ) {
    case 'add':
        $sName      = substr( trim($_POST['reason_name']), 0, 64 );
        $sReason    = trim($_POST['reason_text']);
        $sNameError = ( !$sName )   ? 'Укажите Название причины' : '';
        $sTextError = ( !$sReason ) ? 'Укажите Текст причины'    : '';
        
        if ( !$sNameError && !$sTextError ) {
        	admin_log::addAdminReason( $act_sel, $sName, $sReason, (!empty($_POST['is_bold']) ? 't' : 'f') );
        	header( 'Location: /siteadmin/proj_reasons?act_sel='.$act_sel );
        }
        break;
    case 'edit':
        $sCmd = __paramInit('string', null, 'cmd');
        
        if ( $sCmd == 'go' ) {
            $sName      = substr( trim($_POST['reason_name']), 0, 64 );
            $sReason    = trim($_POST['reason_text']);
            $sNameError = ( !$sName )   ? 'Укажите Название причины' : '';
            $sTextError = ( !$sReason ) ? 'Укажите Текст причины'    : '';
            
            if ( $reasonId && !$sNameError && !$sTextError ) {
            	admin_log::updateAdminReason( $reasonId, $sName, $sReason, (!empty($_POST['is_bold']) ? 't' : 'f') );
            	header( 'Location: /siteadmin/proj_reasons?act_sel='.$act_sel);
            }
        }

        $aReason = admin_log::getAdminReason( $reasonId );
        break;
    case 'del':
        admin_log::deleteAdminReason( $reasonId );
        break;
}

$sFormTitle = ( $sAction == 'edit' ) ? 'Редактировать причину: ' . $aReason['reason_name'] : 'Новая причина'; 
$aReasons   = admin_log::getAdminReasons( $act_sel, false );
$no_banner  = 1;
$rpath      = '../../';
$js_file    = array( 'admin_log.js' );
$css_file = array( "moderation.css", 'new-admin.css', 'nav.css' );
$content    = '../content.php';
$header     = $rpath . 'header.php';
$footer     = $rpath . 'footer.html';
$inner_page = 'inner_index.php';

include( $rpath . 'template.php' );

?>
