<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
$xajax = new xajax("/xajax/projects.server.php?rnd=".mt_rand());
//$xajax->setFlag('debug',true);
$xajax->setFlag('decodeUTF8Input',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->register(XAJAX_FUNCTION, "TabChange");
$xajax->register(XAJAX_FUNCTION, "ToTop");
$xajax->register(XAJAX_FUNCTION, "SwitchFilter");
$xajax->register(XAJAX_FUNCTION, "SwitchOS");
$xajax->register(XAJAX_FUNCTION, "HideProject");
$xajax->register(XAJAX_FUNCTION, "HideTopProjects");
$xajax->register(XAJAX_FUNCTION, "_HideProject");
$xajax->register(XAJAX_FUNCTION, "OpenAllProjects");
$xajax->register(XAJAX_FUNCTION, "WstProj");
$xajax->register(XAJAX_FUNCTION, "getProjectIndication");
$xajax->register(XAJAX_FUNCTION, "getPositionProject");
$xajax->register(XAJAX_FUNCTION, "sendOfferComplain");
$xajax->register(XAJAX_FUNCTION, 'getOfferComplaints');
$xajax->register(XAJAX_FUNCTION, 'getProjectComplaints');
$xajax->register(XAJAX_FUNCTION, 'setReadAllProject');
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetCitysByCid", $_SERVER['DOCUMENT_ROOT'] . "/xajax/countrys.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedProject", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlockedFreelanceOffer", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasons", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasonText", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("SetGiftResv", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("AddInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("DelInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('saveHeaderNote', $_SERVER['DOCUMENT_ROOT'] . '/xajax/notes.server.php'));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickprjedit_save_budget", $_SERVER['DOCUMENT_ROOT'] . "/xajax/projects-quick-edit.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickprjedit_get_prj", $_SERVER['DOCUMENT_ROOT'] . "/xajax/projects-quick-edit.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickprjedit_save_prj", $_SERVER['DOCUMENT_ROOT'] . "/xajax/projects-quick-edit.server.php"));


$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserWarnForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserWarnFormNew', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('updateUserWarn', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction("setUserBanForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction("updateUserBan", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('addFavorite', $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('delFavorite', $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));

$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfolio', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfChoice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditProfile', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasons', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
$xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasonText', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPaymentProcess", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quick_payment.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getUserPhoto", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkPromoCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/promo_codes.server.php"));


//Аякс обработчики попапа покупки ПРО
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));