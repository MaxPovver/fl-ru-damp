<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
global $xajax;
if (!$xajax) {
    $xajax = new xajax("/xajax/hh.server.php?rnd=".mt_rand());
    $xajax->setFlag('decodeUTF8Input',true);
    //$xajax->setFlag('debug',true);
    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
    $xajax->register(XAJAX_FUNCTION, 'addHHSpecProf');
    $xajax->register(XAJAX_FUNCTION, 'delProf');
    $xajax->register(XAJAX_FUNCTION, 'delHHSpec');
}
?>
