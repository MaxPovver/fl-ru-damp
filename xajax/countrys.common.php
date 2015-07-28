<?php

$rpath = ($rpath)? $rpath : "../../";
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");

global $xajax;
if (!$xajax) {
	$xajax = new xajax("/xajax/countrys.server.php");
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
	$xajax->setFlag('decodeUTF8Input',true);
	$xajax->register(XAJAX_FUNCTION,"GetCitysByCid");
	$xajax->register(XAJAX_FUNCTION,"RFGetCitysByCid");
    
    //Аякс обработчики попапа покупки ПРО
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
}