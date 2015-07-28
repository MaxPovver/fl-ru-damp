<?php
/**
 * Редатирование пользовательского контента модератором
 */

define( 'XAJAX_DEFAULT_CHAR_ENCODING', 'windows-1251' );

require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/xajax_core/xajax.inc.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/config.php' );

global $xajax;

if ( !$xajax ) {
    $xajax = new xajax( '/xajax/adm_edit_content.server.php' );
    
	$xajax->configure( 'decodeUTF8Input', TRUE );
	$xajax->configure( 'scriptLoadTimeout', XAJAX_LOAD_TIMEOUT );
	$xajax->setCharEncoding( 'windows-1251' );
    
    $xajax->register( XAJAX_FUNCTION, 'admEditContacts' );
    $xajax->register( XAJAX_FUNCTION, 'admEditBlogs' );
    $xajax->register( XAJAX_FUNCTION, 'admEditCommunity' );
    $xajax->register( XAJAX_FUNCTION, 'admEditProjects' );
    $xajax->register( XAJAX_FUNCTION, 'admEditPrjOffers' );
    $xajax->register( XAJAX_FUNCTION, 'admEditPrjOffersLoadWorks' );
    $xajax->register( XAJAX_FUNCTION, 'admEditArtCom' );
    $xajax->register( XAJAX_FUNCTION, 'admEditProfile' );
    $xajax->register( XAJAX_FUNCTION, 'admEditPrjDialog' );
    $xajax->register( XAJAX_FUNCTION, 'admEditContestCom' );
    $xajax->register( XAJAX_FUNCTION, 'admEditPortfChoice' );
    $xajax->register( XAJAX_FUNCTION, 'admEditPortfolio' );
    $xajax->register( XAJAX_FUNCTION, 'admEditSdelau' );
    $xajax->register( XAJAX_FUNCTION, 'getAdmEditReasons' );
    $xajax->register( XAJAX_FUNCTION, 'getAdmEditReasonText' );
    $xajax->register( XAJAX_FUNCTION, 'makeVacancy' );
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('GetCitysByCid', $_SERVER['DOCUMENT_ROOT'] . '/xajax/countrys.server.php') );
    
}