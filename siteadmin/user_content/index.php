<?php
/**
 * Модерирование пользовательского контента. Индекс.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
define( 'IS_SITE_ADMIN', 1 );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_content.php");

session_start();

$uid  = get_uid();
$site = __paramInit( 'string', 'site', 'site', 'choose' );

if ( !in_array($site, user_content::$site_allow) ) {
    header_location_exit( '/404.php' );
    exit;
}

if ( !isset($aPermissions) ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/permissions.php' );
    $aPermissions = permissions::getUserPermissions( $uid );
}

$user_content = new user_content( $uid, $aPermissions );

if ( !$user_content->hasPermissions($site) || !user_content::isAllowed( 'adm', $aPermissions) ) { 
    header_location_exit( '/404.php' );
    exit;
}

$menu_item  = array_search( $site, user_content::$site_allow );
$rpath      = '../../';
$css_file    = array( 'moderation.css', 'nav.css', '/css/block/b-button/__icon/b-button__icon.css', '/css/block/b-icon/_dop/b-icon_dop.css', '/css/block/b-input-hint/b-input-hint.css' );
$js_file    = array( 'user_content.js' );
$header     = $rpath . 'header.php';
$inner_page = "{$site}_inner.php";
$content    = '../content22.php';
$footer     = $rpath . 'footer.html';
$template   = 'template2.php';
$no_tpl     = array( 'stream', 'blocked', 'frames' );
$js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
include( "{$site}.php" );

if ( !in_array($site, $no_tpl) ) {
    include( $rpath . $template );
}