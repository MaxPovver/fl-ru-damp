<?

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");

$rpath = ($rpath)? $rpath : "../";

require_once $_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php";

global $xajax;

if (!$xajax) {

    $xajax = new xajax("/xajax/contest.server.php");
    $xajax->configure('decodeUTF8Input',true);
    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
//    $xajax->setFlag('debug',true);

	$xajax->register(XAJAX_FUNCTION, 'CreateComment');
	$xajax->register(XAJAX_FUNCTION, 'ChangeComment');
	$xajax->register(XAJAX_FUNCTION, 'DeleteComment');
	$xajax->register(XAJAX_FUNCTION, 'RestoreComment');
	$xajax->register(XAJAX_FUNCTION, 'DelOffer');
	$xajax->register(XAJAX_FUNCTION, 'Candidate');
	$xajax->register(XAJAX_FUNCTION, 'UserBlock');
	$xajax->register(XAJAX_FUNCTION, 'RemoveOffer');
	$xajax->register(XAJAX_FUNCTION, 'RestoreOffer');
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getStatProject", $_SERVER['DOCUMENT_ROOT'] . "/xajax/projects_ci.server.php"));
	
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("updateUserBan", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setUserBanForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	
	$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserWarnForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('updateUserWarn', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedProject", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasons", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasonText", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));

    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickprjedit_save_budget", $_SERVER['DOCUMENT_ROOT'] . "/xajax/projects-quick-edit.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickprjedit_get_prj", $_SERVER['DOCUMENT_ROOT'] . "/xajax/projects-quick-edit.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickprjedit_save_prj", $_SERVER['DOCUMENT_ROOT'] . "/xajax/projects-quick-edit.server.php"));
	
	$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getNotesForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/notes.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('delNote', $_SERVER['DOCUMENT_ROOT'] . '/xajax/notes.server.php') );
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('addNotes', $_SERVER['DOCUMENT_ROOT'] . "/xajax/notes.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('saveHeaderNoteFromProject', $_SERVER['DOCUMENT_ROOT'] . "/xajax/notes.server.php"));
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPaymentProcess", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quick_payment.server.php"));
    
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkWebmoneyWMID", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickver.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkIsVerify", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickver.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("storeFIO", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickver.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickYandexKassaAC", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickver.server.php"));
}