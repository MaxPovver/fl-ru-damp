<?
$rpath = ($rpath)? $rpath : "../";
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
global $xajax;
if (!$xajax) {
    $xajax = new xajax("/xajax/blogs.server.php");
//    $xajax->setFlag('debug',true);
    $xajax->configure('decodeUTF8Input',true);
    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
    $xajax->register(XAJAX_FUNCTION, "AddFavBlog");
    $xajax->register(XAJAX_FUNCTION, "DelFavBlog");
    $xajax->register(XAJAX_FUNCTION, "EditFavBlog");
    $xajax->register(XAJAX_FUNCTION, "openlevel");
/*    $xajax->register(XAJAX_FUNCTION, "banBlogThread");
    $xajax->register(XAJAX_FUNCTION, "warnUser");*/
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedThread", $_SERVER['DOCUMENT_ROOT']."/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("WarnUser",  $_SERVER['DOCUMENT_ROOT']."/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, "CorporativeTags");
    $xajax->register(XAJAX_FUNCTION, "searchCorporativeTag");
	$xajax->register(XAJAX_FUNCTION, "BlogsPoll_Vote");
	$xajax->register(XAJAX_FUNCTION, "BlogsPoll_Show");
	$xajax->register(XAJAX_FUNCTION, "BlogsPoll_Close");
	$xajax->register(XAJAX_FUNCTION, "BlogsPoll_Remove");
	$xajax->register(XAJAX_FUNCTION, "SetBlogSubscribe");
	$xajax->register(XAJAX_FUNCTION, "DelBlogSubscribe");
	$xajax->register(XAJAX_FUNCTION, "ResetAttachedfiles");
	
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setUserBanForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("updateUserBan", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedThread", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetWarns", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setUserWarnForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("updateUserWarn", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedCommune", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedProject", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasons", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasonText", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setDelReasonForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setDeleted", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasonTextDel", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    
    // Ð§ÐµÑ€Ð½Ð¾Ð²Ð¸ÐºÐ¸ Ð±Ð»Ð¾Ð³Ð¾Ð²
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("SaveDraftBlog", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("CheckDraftsBlog", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("FillDraftForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("PostDraft", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));

    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("SaveStatus", $_SERVER['DOCUMENT_ROOT'] . "/xajax/status.server.php"));
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('saveHeaderNote', $_SERVER['DOCUMENT_ROOT'] . '/xajax/notes.server.php'));
    
    /* Äëÿ ïðîôèëÿ ïîëüçîâàòåëÿ */
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetCitysByCid", $_SERVER['DOCUMENT_ROOT'] . "/xajax/countrys.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("SaveStatus", $_SERVER['DOCUMENT_ROOT'] . "/xajax/status.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("FormSave", $_SERVER['DOCUMENT_ROOT'] . "/xajax/notes.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("PopVote", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("AddInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("DelInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("DelInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfolio', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfChoice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditProfile', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasons', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasonText', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
}
?>
