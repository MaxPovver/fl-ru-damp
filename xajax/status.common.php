<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
global $xajax;
if (!$xajax) {
	$xajax = new xajax("/xajax/status.server.php");
	//$xajax->debugOn();
	$xajax->configure('decodeUTF8Input',true);
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
	$xajax->setCharEncoding("windows-1251");
	$xajax->register(XAJAX_FUNCTION, "SaveStatus");
	
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
}
?>
