<?php
$rpath = ($rpath)? $rpath : '../';
define( 'XAJAX_DEFAULT_CHAR_ENCODING', 'windows-1251' );

require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/xajax_core/xajax.inc.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/config.php' );

global $xajax;

if ( !$xajax ) {
    $xajax = new xajax( '/xajax/letters.server.php' );
    
    $xajax->configure( 'decodeUTF8Input', true );
    $xajax->configure( 'scriptLoadTimeout', XAJAX_LOAD_TIMEOUT );
    //$xajax->setFlag('debug',true);
    
    $xajax->register( XAJAX_FUNCTION, 'showLetters' );
    $xajax->register( XAJAX_FUNCTION, 'addLetter' );
    $xajax->register( XAJAX_FUNCTION, 'saveLetter' );
    $xajax->register( XAJAX_FUNCTION, 'showGroup' );
    $xajax->register( XAJAX_FUNCTION, 'showByUser' );
    $xajax->register( XAJAX_FUNCTION, 'showDoc' );
    $xajax->register( XAJAX_FUNCTION, 'delDoc' );
    $xajax->register( XAJAX_FUNCTION, 'editDoc' );
    $xajax->register( XAJAX_FUNCTION, 'getDocField' );
    $xajax->register( XAJAX_FUNCTION, 'updateDocField' );
    $xajax->register( XAJAX_FUNCTION, 'resetAttachedFiles' );
    $xajax->register( XAJAX_FUNCTION, 'calcDeliveryCost' );
    $xajax->register( XAJAX_FUNCTION, 'showMassStatus' );
    $xajax->register( XAJAX_FUNCTION, 'updateMassStatus' );
    $xajax->register( XAJAX_FUNCTION, 'processDocs' );
    $xajax->register( XAJAX_FUNCTION, 'processSendDocs' );
    $xajax->register( XAJAX_FUNCTION, 'showCompanies' );
    $xajax->register( XAJAX_FUNCTION, 'updateMassDeliveryCost' );
    $xajax->register( XAJAX_FUNCTION, 'updateMassDate' );
    $xajax->register( XAJAX_FUNCTION, 'addTemplate' );
    $xajax->register( XAJAX_FUNCTION, 'updateTemplate' );
    $xajax->register( XAJAX_FUNCTION, 'selectTemplate' );
    $xajax->register( XAJAX_FUNCTION, 'changeWithoutourdocs' );
    
}