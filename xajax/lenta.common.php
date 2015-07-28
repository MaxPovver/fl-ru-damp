<?php

define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
$xajax = new xajax("/xajax/lenta.server.php");
//$xajax->debugOn();
//$xajax->setFlag('debug',true);
$xajax->configure('decodeUTF8Input',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->register(XAJAX_FUNCTION, "Lenta_AddFav");
$xajax->register(XAJAX_FUNCTION, "Lenta_EditFav");
$xajax->register(XAJAX_FUNCTION, "Lenta_SortFav");
$xajax->register(XAJAX_FUNCTION, "Lenta_Save");
$xajax->register(XAJAX_FUNCTION, "Lenta_Show");

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("CommunePoll_Vote", $_SERVER['DOCUMENT_ROOT']."/xajax/commune.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("CommunePoll_Show", $_SERVER['DOCUMENT_ROOT']."/xajax/commune.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("CommunePoll_Close", $_SERVER['DOCUMENT_ROOT']."/xajax/commune.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("CommunePoll_Remove", $_SERVER['DOCUMENT_ROOT']."/xajax/commune.server.php"));

$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlogsPoll_Vote", $_SERVER['DOCUMENT_ROOT']."/xajax/blogs.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlogsPoll_Show", $_SERVER['DOCUMENT_ROOT']."/xajax/blogs.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlogsPoll_Close", $_SERVER['DOCUMENT_ROOT']."/xajax/blogs.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("BlogsPoll_Remove", $_SERVER['DOCUMENT_ROOT']."/xajax/blogs.server.php"));

//Аякс обработчики попапа покупки ПРО
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));