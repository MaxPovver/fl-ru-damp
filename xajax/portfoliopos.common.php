<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
$xajax = new xajax("/xajax/portfoliopos.server.php");
//$xajax->setFlag('debug',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->register(XAJAX_FUNCTION, "ChangeProfPos");
$xajax->register(XAJAX_FUNCTION, "ChangePos");
$xajax->register(XAJAX_FUNCTION, "ChangeTextPrev");
$xajax->register(XAJAX_FUNCTION, "ChangeGrPrev");
$xajax->register(XAJAX_FUNCTION, "ChangePortfPrice");
$xajax->register(XAJAX_FUNCTION, "ChangeProfCountSelected");
$xajax->register(XAJAX_FUNCTION, "DelPict");
?>