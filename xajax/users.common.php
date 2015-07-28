<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");

global $xajax;

if (!$xajax) {
	$xajax = new xajax("/xajax/users.server.php");
	$xajax->configure('decodeUTF8Input',true);
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
	$xajax->register(XAJAX_FUNCTION, "PopVote");
	$xajax->register(XAJAX_FUNCTION, "CheckUser");
        $xajax->register(XAJAX_FUNCTION, "AddSubscFilter");
        $xajax->register(XAJAX_FUNCTION, "removeSubscFilter");
        $xajax->register(XAJAX_FUNCTION, "togglePrj");
        $xajax->register(XAJAX_FUNCTION, "SetSex");
        $xajax->register(XAJAX_FUNCTION, "SetGiftResv");
        $xajax->register(XAJAX_FUNCTION, "SetPromoBlockClosed");
        $xajax->register(XAJAX_FUNCTION, "setDirectExternalLinks");
        $xajax->register(XAJAX_FUNCTION, "getsms");
		$xajax->register(XAJAX_FUNCTION, "checkCode");
        $xajax->register(XAJAX_FUNCTION, "recalcUserPortfolioRating");
        $xajax->register(XAJAX_FUNCTION, "getUserPhoto"); 
        $xajax->register(XAJAX_FUNCTION, "GetFreeLogin");
        
    //Аякс обработчики попапа покупки ПРО
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
}