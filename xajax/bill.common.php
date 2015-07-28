<?php

$rpath = ($rpath)? $rpath : "../";
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once ($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
$xajax = new xajax("/xajax/bill.server.php");
//$xajax->setFlag('debug',true);
$xajax->configure('decodeUTF8Input',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);

$xajax->register(XAJAX_FUNCTION, "ShowBillComms");
$xajax->register(XAJAX_FUNCTION, "ShowBillText");
//$xajax->register(XAJAX_FUNCTION, "CheckUserType");
//$xajax->register(XAJAX_FUNCTION, "CheckUser");
//$xajax->register(XAJAX_FUNCTION, "changeCalendarMonth");
//$xajax->register(XAJAX_FUNCTION, "BlockOperation");
//$xajax->register(XAJAX_FUNCTION, "UnBlockOperation");
//$xajax->register(XAJAX_FUNCTION, "PreparePaymentOD");
//$xajax->register(XAJAX_FUNCTION, "addService");
//$xajax->register(XAJAX_FUNCTION, "updateOrder");
//$xajax->register(XAJAX_FUNCTION, "removeOrder");
//$xajax->register(XAJAX_FUNCTION, "clearOrdersServices");
//$xajax->register(XAJAX_FUNCTION, "updateAutoProlong");
//$xajax->register(XAJAX_FUNCTION, "preparePaymentServices");
//$xajax->register(XAJAX_FUNCTION, "cancelReservedOrders");
$xajax->register(XAJAX_FUNCTION, "ShowReserveOrders");
$xajax->register(XAJAX_FUNCTION, "ShowReserveText");
//$xajax->register(XAJAX_FUNCTION, "walletActivate");
//$xajax->register(XAJAX_FUNCTION, "updateProAuto");
//$xajax->register(XAJAX_FUNCTION, "walletRevoke");
$xajax->register(XAJAX_FUNCTION, "removeBillInvoice");

//Быстрая оплата
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPaymentProcess", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quick_payment.server.php"));

//Аякс обработчики попапа покупки ПРО
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));