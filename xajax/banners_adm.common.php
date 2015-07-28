<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once ($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
$xajax = new xajax("/xajax/banners_adm.server.php");
//$xajax->setFlag('debug',true);
$xajax->configure('decodeUTF8Input',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->register(XAJAX_FUNCTION, "checkStatic");
$xajax->register(XAJAX_FUNCTION, "updateCitys");
$xajax->register(XAJAX_FUNCTION, "GetCities");
$xajax->register(XAJAX_FUNCTION, "AddClient");
$xajax->register(XAJAX_FUNCTION, "CheckLogin");
$xajax->register(XAJAX_FUNCTION, "GetClientBanners");
?>