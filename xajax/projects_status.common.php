<?

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");

global $xajax;
if(!$xajax) 
{
    $xajax = new xajax("/xajax/projects_status.server.php");

    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
    $xajax->configure('decodeUTF8Input',true);
    //$xajax->setFlag('decodeUTF8Input',true);

    $xajax->register(XAJAX_FUNCTION, "changeProjectStatus",array());
    $xajax->register(XAJAX_FUNCTION, "projectEditFeedback",array());
    $xajax->register(XAJAX_FUNCTION, "projectUpdateFeedback",array());
    $xajax->register(XAJAX_FUNCTION, "projectDeleteFeedback",array());
}