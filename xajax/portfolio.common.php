<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
$xajax = new xajax("/xajax/portfolio.server.php");
//$xajax->debugOn();
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
//$xajax->register(XAJAX_FUNCTION, "AddFav");
$xajax->register(XAJAX_FUNCTION, "SwitchFilter");
$xajax->register(XAJAX_FUNCTION, "openProfession");
$xajax->register(XAJAX_FUNCTION, "editProfession");
$xajax->register(XAJAX_FUNCTION, "removeProfession");
$xajax->register(XAJAX_FUNCTION, "openEditWork");
$xajax->register(XAJAX_FUNCTION, "editWork");
$xajax->register(XAJAX_FUNCTION, "removeWork");
$xajax->register(XAJAX_FUNCTION, "updatePreview");

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("AddInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("DelInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));