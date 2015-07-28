<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
$xajax = new xajax("/xajax/professions.server.php?rnd=".mt_rand());
//$xajax->setFlag('debug',true);
$xajax->setFlag('decodeUTF8Input',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->register(XAJAX_FUNCTION, 'downSpec');
$xajax->register(XAJAX_FUNCTION, 'moveSpec');
$xajax->register(XAJAX_FUNCTION, 'setSpecAutoPay');
$xajax->register(XAJAX_FUNCTION, 'prolongSpecs');
$xajax->register(XAJAX_FUNCTION, 'freezePro');
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("walletActivate", $_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("walletRevoke", $_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.server.php"));


$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkPromoCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/promo_codes.server.php"));