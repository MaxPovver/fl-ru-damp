<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
global $xajax;
if (!$xajax) {
    $xajax = new xajax( '/xajax/quickpro.server.php' );
    
    $xajax->configure( 'decodeUTF8Input', true );
    $xajax->configure( 'scriptLoadTimeout', XAJAX_LOAD_TIMEOUT );
    //$xajax->setFlag('debug',true);
    
    $xajax->register( XAJAX_FUNCTION, 'quickPROPayAccount' );
    $xajax->register( XAJAX_FUNCTION, 'quickPROGetYandexKassaLink' );
    $xajax->register( XAJAX_FUNCTION, 'quickPROGetPlatipotomLink' );
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetRating", $_SERVER['DOCUMENT_ROOT'] . "/xajax/rating.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkPromoCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/promo_codes.server.php"));

}