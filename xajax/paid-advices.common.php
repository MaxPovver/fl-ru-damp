<?

define("XAJAX_DEFAULT_CHAR_ENCODING", "windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
global $xajax;
if (!$xajax) {
    $xajax = new xajax("/xajax/paid-advices.server.php");
//$xajax->setFlag('debug',true);
    $xajax->configure('decodeUTF8Input', true);
    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
    $xajax->setCharEncoding("windows-1251");

    $xajax->register(XAJAX_FUNCTION, "NewAdvice");
    $xajax->register(XAJAX_FUNCTION, "DeclineAdvice");
    $xajax->register(XAJAX_FUNCTION, "DeleteAdvice");
    $xajax->register(XAJAX_FUNCTION, "RestoreAdvice");
    $xajax->register(XAJAX_FUNCTION, "AcceptedAdvice");
    $xajax->register(XAJAX_FUNCTION, "CalcPaidAdvice"); 
    $xajax->register(XAJAX_FUNCTION, "RefuseAdvice");
    $xajax->register(XAJAX_FUNCTION, "getFormDeclined");
    $xajax->register(XAJAX_FUNCTION, "ModDeclinedAdvice");
    $xajax->register(XAJAX_FUNCTION, "ModAcceptedAdvice");
}