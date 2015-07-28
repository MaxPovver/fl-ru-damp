<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
$xajax = new xajax("/xajax/freelancers.server.php");
//$xajax->debugOn();
$xajax->configure('waitCursor', true); // Для Fp нужен...
$xajax->configure('decodeUTF8Input',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->register(XAJAX_FUNCTION, "ChangeCity");
$xajax->register(XAJAX_FUNCTION, "AddFav");
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("GetCitysByCid", $_SERVER['DOCUMENT_ROOT'] . "/xajax/countrys.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("RFGetCitysByCid", $_SERVER['DOCUMENT_ROOT'] . "/xajax/countrys.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("AddInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("DelInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPaymentProcess", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quick_payment.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkPromoCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/promo_codes.server.php"));


//Аякс обработчики попапа покупки ПРО
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));


//Обработка переключения страниц в попапе редактирования работ в каталоге фрилансеров
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("FPEP_getTab", $_SERVER['DOCUMENT_ROOT'] . "/xajax/freelancers_preview_editor_popup.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("FPEP_saveProcess", $_SERVER['DOCUMENT_ROOT'] . "/xajax/freelancers_preview_editor_popup.server.php"));