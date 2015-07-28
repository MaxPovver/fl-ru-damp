<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
global $xajax;
if (!$xajax) {
	$xajax = new xajax("/xajax/presscenter.server.php");
	//$xajax->setFlag('debug',true);
	$xajax->configure('decodeUTF8Input',true);
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
	$xajax->setCharEncoding("windows-1251");
	$xajax->register(XAJAX_FUNCTION, "GetPeopleTeamInfo");
    $xajax->register(XAJAX_FUNCTION, "DeletePhoto");
    $xajax->register(XAJAX_FUNCTION, "ReorderTeam");
}
?>
