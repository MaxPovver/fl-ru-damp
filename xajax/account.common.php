<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
global $xajax;

if (!$xajax) {
    $xajax = new xajax("/xajax/account.server.php?rnd=".mt_rand());
	//$xajax->setFlag('debug',true);
    $xajax->setFlag('decodeUTF8Input',true);
    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
    $xajax->register(XAJAX_FUNCTION, 'delAttach');
    
    if (function_exists('hasPermissions') && hasPermissions('users')) {
        $xajax->register(XAJAX_FUNCTION, 'repairFinData');
    }
    
    
    $xajax->register(XAJAX_FUNCTION, 'checkAcceptCode');
    $xajax->register(XAJAX_FUNCTION, 'resendAcceptCode');
    
    
    //$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("rezDocChange", $_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("authSMS", $_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("authCodeSMS", $_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("resendAuthCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("authCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
    
    //Модерация финансов
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setDelReasonForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasonTextDel", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setDeleted", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("unBlocked", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
}