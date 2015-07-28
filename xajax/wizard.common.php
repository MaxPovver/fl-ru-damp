<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
global $xajax;
if (!$xajax) {
	$xajax = new xajax("/xajax/wizard.server.php");
	$xajax->configure('decodeUTF8Input',true);
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);

    $xajax->register(XAJAX_FUNCTION, "searchProject");
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("CheckUser", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("CheckEmail", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetFreeLogin", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
}