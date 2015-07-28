<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
$xajax = new xajax("/xajax/projects_ci.server.php?rnd=".mt_rand());
//$xajax->configure('debug',true);
$xajax->configure('decodeUTF8Input',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->register(XAJAX_FUNCTION, "SelectProjectExecutor");
$xajax->register(XAJAX_FUNCTION, "SelectProjectOffer");
$xajax->register(XAJAX_FUNCTION, "RefuseProjectOffer");
$xajax->register(XAJAX_FUNCTION, "AddDialogueMessage");
$xajax->register(XAJAX_FUNCTION, "ChangePortfByProf");
$xajax->register(XAJAX_FUNCTION, "ReadOfferDialogue");
$xajax->register(XAJAX_FUNCTION, "ReadAllOffers");
$xajax->register(XAJAX_FUNCTION, "SendComplain");
$xajax->register(XAJAX_FUNCTION, "FrlRefuse");

$xajax->register(XAJAX_FUNCTION, "mass_Calc");

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setUserBanForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("updateUserBan", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));

$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserWarnForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('updateUserWarn', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedProject", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedProjectOffer", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedDialogue", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasons", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasonText", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickprjedit_save_budget", $_SERVER['DOCUMENT_ROOT'] . "/xajax/projects-quick-edit.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickprjedit_get_prj", $_SERVER['DOCUMENT_ROOT'] . "/xajax/projects-quick-edit.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickprjedit_save_prj", $_SERVER['DOCUMENT_ROOT'] . "/xajax/projects-quick-edit.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetCitysByCid", $_SERVER['DOCUMENT_ROOT'] . "/xajax/public.server.php"));
$xajax->register(XAJAX_FUNCTION, "getStatProject");
$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getNotesForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/notes.server.php') );
$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('delNote', $_SERVER['DOCUMENT_ROOT'] . '/xajax/notes.server.php') );
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('addNotes', $_SERVER['DOCUMENT_ROOT'] . "/xajax/notes.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('addFavorite', $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('delFavorite', $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('saveHeaderNoteFromProject', $_SERVER['DOCUMENT_ROOT'] . "/xajax/notes.server.php"));


$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkWebmoneyWMID", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickver.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkIsVerify", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickver.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("storeFIO", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickver.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickYandexKassaAC", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickver.server.php"));


$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));


$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickMASCheckOrder", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickmas.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickMASPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickmas.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickMASSetCats", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickmas.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickMASGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickmas.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("changeProjectStatus", $_SERVER['DOCUMENT_ROOT'] . "/xajax/projects_status.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkPromoCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/promo_codes.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPaymentProcess", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quick_payment.server.php"));

/*
$xajax->register(XAJAX_FUNCTION, "submitAddFileForm");
*/