<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
$xajax = new xajax("/xajax/tservices.server.php");


//$xajax->debugOn();
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->configure('decodeUTF8Input',true);

$xajax->register(XAJAX_FUNCTION, "more_feedbacks",array('callback' => 'xajax_callback_feedbacks'));
$xajax->register(XAJAX_FUNCTION, "tservices_order_auth",array());


$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setDelReasonForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getAdminActionReasonTextDel", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("setDeleted", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("unBlocked", $_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php"));


$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("AddInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("DelInTeam", $_SERVER['DOCUMENT_ROOT'] . "/xajax/team.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPaymentProcess", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quick_payment.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("getUserPhoto", $_SERVER['DOCUMENT_ROOT'] . "/xajax/users.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("checkPromoCode", $_SERVER['DOCUMENT_ROOT'] . "/xajax/promo_codes.server.php"));


//Аякс обработчики попапа покупки ПРО
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));