<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
global $xajax;
if (!$xajax) {
	$xajax = new xajax("/xajax/opinions.server.php");
    //$xajax->setFlag('debug',true);
	$xajax->configure('decodeUTF8Input',true);
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
	$xajax->register(XAJAX_FUNCTION, "AddOpinion");
        $xajax->register(XAJAX_FUNCTION, "EditOpinionForm");
        $xajax->register(XAJAX_FUNCTION, "EditOpinion");
        $xajax->register(XAJAX_FUNCTION, "DeleteOpinion");
        $xajax->register(XAJAX_FUNCTION, "AddOpComentForm");
        $xajax->register(XAJAX_FUNCTION, "EditOpinionComm");
        $xajax->register(XAJAX_FUNCTION, "DeleteOpinionComm");
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("PopVote", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetWarns", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setUserWarnForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("updateUserWarn", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setUserBanForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("updateUserBan", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasons", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasonText", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("SaveStatus", $_SERVER['DOCUMENT_ROOT'] . "/xajax/status.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("AddInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("DelInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('saveHeaderNote', $_SERVER['DOCUMENT_ROOT'] . '/xajax/notes.server.php'));
    
    // Платные рекомендации
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('NewAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('DeclineAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('RestoreAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('AcceptedAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('CalcPaidAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('RefuseAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('DeleteAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfolio', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfChoice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditProfile', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasons', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasonText', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('EditSBROpForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/sbr.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('editFeedbackNew', $_SERVER['DOCUMENT_ROOT'] . '/xajax/sbr.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('DeleteFeedback', $_SERVER['DOCUMENT_ROOT'] . '/xajax/sbr.server.php') );
    
    //Управление отзывами по заказу ТУ
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('tservicesOrdersDeleteFeedback', $_SERVER['DOCUMENT_ROOT'] . '/xajax/tservices_orders.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('tservicesOrdersUpdateFeedback', $_SERVER['DOCUMENT_ROOT'] . '/xajax/tservices_orders.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('tservicesOrdersEditFeedback', $_SERVER['DOCUMENT_ROOT'] . '/xajax/tservices_orders.server.php') );
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('projectDeleteFeedback', $_SERVER['DOCUMENT_ROOT'] . '/xajax/projects_status.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('projectUpdateFeedback', $_SERVER['DOCUMENT_ROOT'] . '/xajax/projects_status.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('projectEditFeedback', $_SERVER['DOCUMENT_ROOT'] . '/xajax/projects_status.server.php') );
    
    //Показываем контакты по запросу
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getContactsInfo", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
    
    //Аякс обработчики попапа покупки ПРО
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
}