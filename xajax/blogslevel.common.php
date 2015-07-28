<?
$rpath = ($rpath)? $rpath : "../";
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");

$xajax = new xajax("/xajax/blogslevel.server.php");
//$xajax->debugOn();
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->configure('decodeUTF8Input',true);
$xajax->register(XAJAX_FUNCTION, "openlevel");
?>