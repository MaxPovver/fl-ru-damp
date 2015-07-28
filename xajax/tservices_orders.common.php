<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");

global $xajax;
if(!$xajax) 
{
    $xajax = new xajax("/xajax/tservices_orders.server.php");

    $xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
    $xajax->configure('decodeUTF8Input',true);
    //$xajax->setFlag('decodeUTF8Input',true);

    $xajax->register(XAJAX_FUNCTION, "tservicesOrdersNewFeedback",array());
    $xajax->register(XAJAX_FUNCTION, "tservicesOrdersEditFeedback",array());
    $xajax->register(XAJAX_FUNCTION, "tservicesOrdersUpdateFeedback",array());
    $xajax->register(XAJAX_FUNCTION, "tservicesOrdersDeleteFeedback",array());
    $xajax->register(XAJAX_FUNCTION, "tservicesOrdersNewMessage",array());
    $xajax->register(XAJAX_FUNCTION, "tservicesOrdersCheckMessages",array());
    $xajax->register(XAJAX_FUNCTION, "tservicesOrdersSetPrice",array());
    $xajax->register(XAJAX_FUNCTION, "getOrderHistory", array());
    
    //Быстрая оплата
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPaymentProcess", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quick_payment.server.php"));
    //Выплаты по сделке с резервом средств
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("reservesPayoutProcess", $_SERVER['DOCUMENT_ROOT'] . "/xajax/reserves.server.php"));
    //Работа с арбитражем резерва
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("reservesArbitrageNew", $_SERVER['DOCUMENT_ROOT'] . "/xajax/reserves.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("reservesArbitrageApply", $_SERVER['DOCUMENT_ROOT'] . "/xajax/reserves.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("reservesArbitrageCancel", $_SERVER['DOCUMENT_ROOT'] . "/xajax/reserves.server.php"));
    
    //Аякс обработчики попапа покупки ПРО
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
    $xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
}