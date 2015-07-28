<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");

global $xajax;

if(!$xajax) {
    $xajax = new xajax("/xajax/norisk.server.php");
    $xajax->configure('decodeUTF8Input',true);
    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
    $xajax->register(XAJAX_FUNCTION, "SaveDraftProject");
    $xajax->register(XAJAX_FUNCTION, "SaveDraftContacts");
    $xajax->register(XAJAX_FUNCTION, "SaveDraftBlog");
    $xajax->register(XAJAX_FUNCTION, "CheckDraftsContacts");
    $xajax->register(XAJAX_FUNCTION, "CheckDraftsProject");
    $xajax->register(XAJAX_FUNCTION, "CheckDraftsBlog");
    $xajax->register(XAJAX_FUNCTION, "FillDraftForm");
    $xajax->register(XAJAX_FUNCTION, "PostDraft");
}

?>
