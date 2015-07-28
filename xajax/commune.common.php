<?php

global $xajax;
if (!$xajax) {
	define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
	$xajax = new xajax("/xajax/commune.server.php");
//    $xajax->setFlag('debug',true);
	$xajax->configure('decodeUTF8Input',true);
	$xajax->register(XAJAX_FUNCTION, "BlockedTopic");
	$xajax->register(XAJAX_FUNCTION, "DeleteTopic");
	$xajax->register(XAJAX_FUNCTION, "restoreDeletedPost");
	$xajax->register(XAJAX_FUNCTION, "DeleteComment");
	$xajax->register(XAJAX_FUNCTION, "Vote");
        $xajax->register(XAJAX_FUNCTION, 'VoteTopic');
	$xajax->register(XAJAX_FUNCTION, "CreateCommentForm");
	$xajax->register(XAJAX_FUNCTION, "DeleteAttach");
	$xajax->register(XAJAX_FUNCTION, "BanMemberForComment");
	$xajax->register(XAJAX_FUNCTION, "BanMemberForTopic");
	$xajax->register(XAJAX_FUNCTION, "BanMember");
	$xajax->register(XAJAX_FUNCTION, "UpdateNote");
	$xajax->register(XAJAX_FUNCTION, "UpdateNoteMP");
	$xajax->register(XAJAX_FUNCTION, "AddFav");
	$xajax->register(XAJAX_FUNCTION, "CommunePoll_Vote");
	$xajax->register(XAJAX_FUNCTION, "CommunePoll_Show");
	$xajax->register(XAJAX_FUNCTION, "CommunePoll_Close");
	$xajax->register(XAJAX_FUNCTION, "CommunePoll_Remove");
	$xajax->register(XAJAX_FUNCTION, "SubscribeTheme");
	$xajax->register(XAJAX_FUNCTION, "SubscribeCommune");
	$xajax->register(XAJAX_FUNCTION, "SortFav");
	$xajax->register(XAJAX_FUNCTION, "EditFav");
	$xajax->register(XAJAX_FUNCTION, "AddCategory");
	$xajax->register(XAJAX_FUNCTION, "CommuneMove");
	$xajax->register(XAJAX_FUNCTION, "CommuneSetPosition");
	$xajax->register(XAJAX_FUNCTION, "DeleteCategory");
	$xajax->register(XAJAX_FUNCTION, "ShowCategoriesList");
	$xajax->register(XAJAX_FUNCTION, "EditCategory");
	$xajax->register(XAJAX_FUNCTION, "UpdateCategory");
	$xajax->register(XAJAX_FUNCTION, "commPrntCommentForm");
        $xajax->register(XAJAX_FUNCTION, "JoinCommune");
	$xajax->register(XAJAX_FUNCTION, "OutCommune");
        $xajax->register(XAJAX_FUNCTION, "JoinDialogCommune");
        $xajax->register(XAJAX_FUNCTION, "FileMoveTo");
        $xajax->register(XAJAX_FUNCTION, "MsgDelFile");
    $xajax->register(XAJAX_FUNCTION, "setRoleUser");
    $xajax->register(XAJAX_FUNCTION, "NewPromoCommune");
    $xajax->register(XAJAX_FUNCTION, "BanMemberNewComment");
    $xajax->register(XAJAX_FUNCTION, "BanNewMember");
	// Надо вывести все в отдельный спец. модуль.
	// Это функции, исползуемые в /user/
	$xajax->setCharEncoding("windows-1251");
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetCitysByCid", $_SERVER['DOCUMENT_ROOT'] . "/xajax/countrys.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("SaveStatus", $_SERVER['DOCUMENT_ROOT'] . "/xajax/status.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("FormSave", $_SERVER['DOCUMENT_ROOT'] . "/xajax/notes.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("PopVote", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("AddInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("DelInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("DelInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
	
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("updateUserBan", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setUserBanForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedPortfolio", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedThread", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetWarns", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setUserWarnForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("updateUserWarn", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedCommune", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedCommuneTheme", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedProject", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasons", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasonText", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlogsPoll_Vote", $_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlogsPoll_Show", $_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlogsPoll_Close", $_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlogsPoll_Remove", $_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getFeedback", $_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("editFeedback", $_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetRating", $_SERVER['DOCUMENT_ROOT'] . "/xajax/rating.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("RateComment", $_SERVER['DOCUMENT_ROOT'] . "/xajax/comments.server.php"));
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetComment", $_SERVER['DOCUMENT_ROOT'] . "/xajax/comments.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetMorePrj", $_SERVER['DOCUMENT_ROOT'] . "/xajax/rating.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetMoreSBR", $_SERVER['DOCUMENT_ROOT'] . "/xajax/rating.server.php"));
    
    // Черновики блогов
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("SaveDraftBlog", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("CheckDraftsBlog", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("FillDraftForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));

    // Черновики сообществ
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("SaveDraftCommune", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("CheckDraftsCommune", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("PostDraft", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getNotesForm', $_SERVER['DOCUMENT_ROOT'] . "/xajax/notes.server.php"));
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('addNotes', $_SERVER['DOCUMENT_ROOT'] . "/xajax/notes.server.php"));
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('delNote', $_SERVER['DOCUMENT_ROOT'] . "/xajax/notes.server.php"));
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('saveHeaderNote', $_SERVER['DOCUMENT_ROOT'] . '/xajax/notes.server.php'));
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfolio', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfChoice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditProfile', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasons', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasonText', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('openProfession', $_SERVER['DOCUMENT_ROOT'] . '/xajax/portfolio.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('editProfession', $_SERVER['DOCUMENT_ROOT'] . '/xajax/portfolio.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('removeProfession', $_SERVER['DOCUMENT_ROOT'] . '/xajax/portfolio.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('openEditWork', $_SERVER['DOCUMENT_ROOT'] . '/xajax/portfolio.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('editWork', $_SERVER['DOCUMENT_ROOT'] . '/xajax/portfolio.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('removeWork', $_SERVER['DOCUMENT_ROOT'] . '/xajax/portfolio.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('updatePreview', $_SERVER['DOCUMENT_ROOT'] . '/xajax/portfolio.server.php') );
	
	$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getContactsInfo", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
    
    
    //Аякс обработчики попапа покупки ПРО
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
}