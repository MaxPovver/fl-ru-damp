<?php
$rpath = ($rpath)? $rpath : '../';
define( 'XAJAX_DEFAULT_CHAR_ENCODING', 'windows-1251' );

require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/xajax_core/xajax.inc.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/config.php' );

global $xajax;

if ( !$xajax ) {
    $xajax = new xajax( '/xajax/user_content.server.php' );
    
    $xajax->configure( 'decodeUTF8Input', true );
    $xajax->configure( 'scriptLoadTimeout', XAJAX_LOAD_TIMEOUT );
    //$xajax->setFlag('debug',true);
    
    $xajax->register( XAJAX_FUNCTION, 'updateStreamsForUser' );
    $xajax->register( XAJAX_FUNCTION, 'chooseStream' );
    $xajax->register( XAJAX_FUNCTION, 'releaseStream' );
    $xajax->register( XAJAX_FUNCTION, 'getContents' );
    $xajax->register( XAJAX_FUNCTION, 'getLetters' );
    $xajax->register( XAJAX_FUNCTION, 'getBlocked' );
    $xajax->register( XAJAX_FUNCTION, 'delLetter' );
    $xajax->register( XAJAX_FUNCTION, 'getBlockedLetters' );
    $xajax->register( XAJAX_FUNCTION, 'approveLetter' );
    $xajax->register( XAJAX_FUNCTION, 'updateLetter' );
    $xajax->register( XAJAX_FUNCTION, 'chooseContent' );
    $xajax->register( XAJAX_FUNCTION, 'resolveContent' );
    $xajax->register( XAJAX_FUNCTION, 'resolveAndBan' );
    $xajax->register( XAJAX_FUNCTION, 'massApproveContent' );
    $xajax->register( XAJAX_FUNCTION, 'unblock' );
    $xajax->register( XAJAX_FUNCTION, 'otherCounters' );
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdminActionReasons', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdminActionReasonText', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdminActionReasonTextStream', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdminActionReasonTextDel', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserWarnForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('updateUserWarn', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('updateUserBan', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserBanForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setDelReasonForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setDeleted', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditContacts', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditBlogs', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditCommunity', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditProjects', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPrjOffers', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPrjOffersLoadWorks', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditArtCom', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditProfile', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPrjDialog', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditContestCom', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfChoice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfolio', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditSdelau', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasons', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasonText', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('makeVacancy', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getUserWarns', $_SERVER['DOCUMENT_ROOT'] . '/xajax/admin_log.server.php') );
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('GetCitysByCid', $_SERVER['DOCUMENT_ROOT'] . '/xajax/countrys.server.php') );
    
}