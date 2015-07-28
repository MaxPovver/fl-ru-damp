<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
$xajax = new xajax("/xajax/docs.server.php");
//$xajax->debugOn();
$xajax->configure('decodeUTF8Input',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->register(XAJAX_FUNCTION, "AddSection");
$xajax->register(XAJAX_FUNCTION, "UpdateSection");
$xajax->register(XAJAX_FUNCTION, "DeleteDoc");
$xajax->register(XAJAX_FUNCTION, "MoveDocs");
$xajax->register(XAJAX_FUNCTION, "AddDoc");
$xajax->register(XAJAX_FUNCTION, "EditDocFormPrepare");
$xajax->register(XAJAX_FUNCTION, "RefreshUploadedFiles");
$xajax->register(XAJAX_FUNCTION, "DeleteSection");
$xajax->register(XAJAX_FUNCTION, "DeleteSections");
$xajax->register(XAJAX_FUNCTION, "DeleteDocHTML");
$xajax->register(XAJAX_FUNCTION, "GetDocHTML");
$xajax->register(XAJAX_FUNCTION, "DeleteSectionHTML");
$xajax->register(XAJAX_FUNCTION, "GetSectionHTML");
$xajax->register(XAJAX_FUNCTION, "SectionMoveTo");
$xajax->register(XAJAX_FUNCTION, "FileMoveTo");
$xajax->register(XAJAX_FUNCTION, "DeleteFile");
$xajax->register(XAJAX_FUNCTION, "DeleteEditFile");
?>