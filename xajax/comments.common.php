<?php

global $xajax;
if (!$xajax) {
    $rpath = ($rpath) ? $rpath : "../";
    define("XAJAX_DEFAULT_CHAR_ENCODING", "windows-1251");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
    $xajax = new xajax("/xajax/comments.server.php");
    //$xajax->setFlag('debug',true);
    $xajax->configure('decodeUTF8Input', true);
    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);

    $xajax->register(XAJAX_FUNCTION, "EditComment");
    
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasons", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasonText", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setUserBanForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("updateUserBan", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
        
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('setUserWarnForm', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );
    $xajax->register( XAJAX_FUNCTION, new xajaxUserFunction('updateUserWarn', $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.server.php') );

    // Новые комментарии
    $xajax->register(XAJAX_FUNCTION, "RateComment");
    $xajax->register(XAJAX_FUNCTION, "GetComment");
}
?>
