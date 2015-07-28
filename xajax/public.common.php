<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
$xajax = new xajax("/xajax/public.server.php");
//$xajax->setFlag('debug',true);
$xajax->setFlag('decodeUTF8Input',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->register(XAJAX_FUNCTION, "DelAttach");
$xajax->register(XAJAX_FUNCTION, "DelLogo");
$xajax->register(XAJAX_FUNCTION, "GetCitysByCid");
$xajax->register(XAJAX_FUNCTION, "GetProfessionsBySpec");
$xajax->register(XAJAX_FUNCTION, "GetPreview");
$xajax->register(XAJAX_FUNCTION, "HideDizkonAdv");
$xajax->register(XAJAX_FUNCTION, "getRelativeTU");

// Черновики
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("SaveDraftProject", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("CheckDraftsProject", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("FillDraftForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPRJPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickprj.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPRJGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickprj.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkPromoCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/promo_codes.server.php"));