<?

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");

global $xajax;
if(!$xajax) 
{
    $xajax = new xajax("/xajax/quick_payment.server.php");

    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
    $xajax->configure('decodeUTF8Input',true);
    //$xajax->setFlag('decodeUTF8Input',true);

    $xajax->register(XAJAX_FUNCTION, "quickPaymentProcess",array());
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkPromoCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/promo_codes.server.php"));

    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php")); 
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php")); 
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php")); 
    
    //Разморозка ПРО
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("freezePro", $_SERVER['DOCUMENT_ROOT'] . "/xajax/professions.server.php"));
}