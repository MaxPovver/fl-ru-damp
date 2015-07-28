<?

define("XAJAX_DEFAULT_CHAR_ENCODING", "windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
global $xajax;
if (!$xajax) {
    $xajax = new xajax("/xajax/rating.server.php");
//    $xajax->setFlag('debug',true);
    $xajax->configure('decodeUTF8Input', true);
    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);

    $xajax->register(XAJAX_FUNCTION, "GetRating");
}
