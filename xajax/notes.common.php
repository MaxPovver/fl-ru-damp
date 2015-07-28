<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
global $xajax;
if (!$xajax) {
	$xajax = new xajax("/xajax/notes.server.php");
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
	$xajax->configure('decodeUTF8Input',true);
	$xajax->register(XAJAX_FUNCTION, "FormSave");
	$xajax->register(XAJAX_FUNCTION, "EditNote");
	$xajax->register(XAJAX_FUNCTION, "GetNote");
	$xajax->register(XAJAX_FUNCTION, "getNotesForm");
    $xajax->register( XAJAX_FUNCTION, "addNotes");
    $xajax->register( XAJAX_FUNCTION, "delNote");
}
?>
