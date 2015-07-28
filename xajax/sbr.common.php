<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
global $xajax;
if (!$xajax) {
    $xajax = new xajax("/xajax/sbr.server.php?rnd=".mt_rand());
    $xajax->setFlag('decodeUTF8Input',true);
//    $xajax->setFlag('debug',true);
    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
    $xajax->register(XAJAX_FUNCTION, 'addFrl');
    $xajax->register(XAJAX_FUNCTION, 'getMsgForm');
    $xajax->register(XAJAX_FUNCTION, 'getIndication');
    $xajax->register(XAJAX_FUNCTION, 'delMsg');
    $xajax->register(XAJAX_FUNCTION, 'getDocForm');
    $xajax->register(XAJAX_FUNCTION, 'delDoc');
    $xajax->register(XAJAX_FUNCTION, 'getArbDescr');
    $xajax->register(XAJAX_FUNCTION, "getFeedback");
    $xajax->register(XAJAX_FUNCTION, "editFeedback");
    $xajax->register(XAJAX_FUNCTION, "editFeedbackNew");
    $xajax->register(XAJAX_FUNCTION, "getInvoiceForm");
    $xajax->register(XAJAX_FUNCTION, "setDocsReceived");
    $xajax->register(XAJAX_FUNCTION, "setRemoved");
    $xajax->register(XAJAX_FUNCTION, "setArbPercent");
    $xajax->register(XAJAX_FUNCTION, "changeRezTypeFrl");
    //$xajax->register(XAJAX_FUNCTION, "rezDocChange");
    $xajax->register(XAJAX_FUNCTION, "setNotNp");
    $xajax->register(XAJAX_FUNCTION, "openPayoutPopup");
    $xajax->register(XAJAX_FUNCTION, "saveLimit");
    $xajax->register(XAJAX_FUNCTION, "elPayout");
    $xajax->register(XAJAX_FUNCTION, "changeEmpRezType");
    $xajax->register(XAJAX_FUNCTION, "EditSBROpForm");
    $xajax->register(XAJAX_FUNCTION, "checkWMDoc");
    $xajax->register(XAJAX_FUNCTION, "sendFeedbackSMSCode");
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("PopVote", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetWarns", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setUserWarnForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("updateUserWarn", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setUserBanForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("updateUserBan", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasons", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasonText", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("AddInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("DelInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("SaveStatus", $_SERVER['DOCUMENT_ROOT'] . "/xajax/status.server.php"));
    $xajax->register(XAJAX_FUNCTION, "sbrCalc");
    $xajax->register(XAJAX_FUNCTION, "DeleteFeedback");
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('saveHeaderNote', $_SERVER['DOCUMENT_ROOT'] . '/xajax/notes.server.php'));
    
    // Платные рекомендации
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('NewAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('DeclineAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('RestoreAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('AcceptedAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('CalcPaidAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('RefuseAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction('DeleteAdvice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.server.php'));
    
    $xajax->register(XAJAX_FUNCTION, "loadCurrents");
    $xajax->register(XAJAX_FUNCTION, "loadSbr");
    $xajax->register(XAJAX_FUNCTION, "agreeStage");
    $xajax->register(XAJAX_FUNCTION, "checkSbr");
    $xajax->register(XAJAX_FUNCTION, "deleteDraftSbr");
    $xajax->register(XAJAX_FUNCTION, "checkFrlRezType");
    
    $xajax->register(XAJAX_FUNCTION, "setReqvs");
    $xajax->register(XAJAX_FUNCTION, "preparePayment");
    $xajax->register(XAJAX_FUNCTION, "checkPayment");
    $xajax->register(XAJAX_FUNCTION, "subOpen");
    $xajax->register(XAJAX_FUNCTION, "resendCode");
    $xajax->register(XAJAX_FUNCTION, "updCostSys");
    $xajax->register(XAJAX_FUNCTION, "generateStatement");
    $xajax->register(XAJAX_FUNCTION, "aGetLCInfo");
    $xajax->register(XAJAX_FUNCTION, "aCompleteEvent");
    $xajax->register(XAJAX_FUNCTION, "aRecreateDocLC");
    $xajax->register(XAJAX_FUNCTION, "aGetHistoryLC");
    $xajax->register(XAJAX_FUNCTION, "aCreateDocITO");
    $xajax->register(XAJAX_FUNCTION, "authSMS");
    $xajax->register(XAJAX_FUNCTION, "authCodeSMS");
    $xajax->register(XAJAX_FUNCTION, "checkState");
    $xajax->register(XAJAX_FUNCTION, "setState");
    
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfolio', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditPortfChoice', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('admEditProfile', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasons', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('getAdmEditReasonText', $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.server.php') );
    
    $xajax->register(XAJAX_FUNCTION, "aAddDocument");
    $xajax->register(XAJAX_FUNCTION, "aDelDocument");
    $xajax->register(XAJAX_FUNCTION, "aEditDocument");
    $xajax->register(XAJAX_FUNCTION, "aSaveDocument");
    $xajax->register(XAJAX_FUNCTION, "aGetLogPSKBInfo");
    $xajax->register(XAJAX_FUNCTION, "aFindLogPSKB");
    $xajax->register(XAJAX_FUNCTION, "aClearCloneLogPSKB");
    $xajax->register(XAJAX_FUNCTION, "unactivateAuth");
    $xajax->register(XAJAX_FUNCTION, "unauthCodeSMS");
    $xajax->register(XAJAX_FUNCTION, "resendAuthCode");
    $xajax->register(XAJAX_FUNCTION, "authCode");
    $xajax->register(XAJAX_FUNCTION, "sendCode");
    
    $xajax->register(XAJAX_FUNCTION, "addFeedbackToPromo");
    $xajax->register(XAJAX_FUNCTION, "setArbitr");
    
    
    //Показываем контакты по запросу
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getContactsInfo", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
    
    //Аякс обработчики попапа покупки ПРО
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
}