<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once $_SERVER['DOCUMENT_ROOT']."/xajax/xajax_core/xajax.inc.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/config.php";
global $xajax;

if (!$xajax) {
	$xajax = new xajax("/xajax/banned.server.php");
	$xajax->configure('decodeUTF8Input', TRUE);
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
	$xajax->setCharEncoding("windows-1251");
	$xajax->register(XAJAX_FUNCTION, "BlockedThread");
	$xajax->register(XAJAX_FUNCTION, "BlockedProject");
	$xajax->register(XAJAX_FUNCTION, "BlockedProjectWithComplain");
	$xajax->register(XAJAX_FUNCTION, "getAdminActionReasons");
	$xajax->register(XAJAX_FUNCTION, "getAdminActionReasonText");
    $xajax->register(XAJAX_FUNCTION, "getAdminActionReasonTextStream");
    $xajax->register(XAJAX_FUNCTION, "getAdminActionReasonTextDel");
    $xajax->register(XAJAX_FUNCTION, "BlockedCommune");
    $xajax->register(XAJAX_FUNCTION, "BlockedCommuneTheme");
    $xajax->register(XAJAX_FUNCTION, "BlockedFreelanceOffer");
    $xajax->register(XAJAX_FUNCTION, "BlockedProjectOffer");
    $xajax->register(XAJAX_FUNCTION, "BlockedPortfolio");
    $xajax->register(XAJAX_FUNCTION, "BlockedDialogue");
    $xajax->register(XAJAX_FUNCTION, "GetWarns");
    $xajax->register(XAJAX_FUNCTION, "setUserBanForm");
    $xajax->register(XAJAX_FUNCTION, "setDelReasonForm");
    $xajax->register(XAJAX_FUNCTION, "setDeleted");
    $xajax->register(XAJAX_FUNCTION, "setUserMassBanForm");
    $xajax->register(XAJAX_FUNCTION, "updateUserBan");
    
    $xajax->register(XAJAX_FUNCTION, "setUserWarnForm");
    $xajax->register(XAJAX_FUNCTION, "setUserWarnFormNew");
    $xajax->register(XAJAX_FUNCTION, "updateUserWarn");
    
    $xajax->register(XAJAX_FUNCTION, "unBlocked");
}

?>
