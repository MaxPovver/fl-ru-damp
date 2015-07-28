<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
global $xajax;
if (!$xajax) {
	$xajax = new xajax("/xajax/team.server.php");
	//$xajax->debugOn();
	$xajax->configure('decodeUTF8Input',true);
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
	$xajax->setCharEncoding("windows-1251");
	$xajax->register(XAJAX_FUNCTION, "AddInTeam");
    $xajax->register(XAJAX_FUNCTION, "DelInTeam");
    $xajax->register(XAJAX_FUNCTION, "AddInTeamNew");
    $xajax->register(XAJAX_FUNCTION, "DelInTeamNew");
    $xajax->register(XAJAX_FUNCTION, "addFavorite");
    $xajax->register(XAJAX_FUNCTION, "delFavorite");
}
?>