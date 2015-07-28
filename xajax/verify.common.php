<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
global $xajax;
if (!$xajax) {
	$xajax = new xajax("/xajax/verify.server.php");
	$xajax->configure('decodeUTF8Input',true);
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
	$xajax->register(XAJAX_FUNCTION, "addSubscribe");
}
?>