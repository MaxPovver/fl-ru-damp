<?php

$rpath = ($rpath)? $rpath : "../";
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
$xajax = new xajax("/xajax/contacts.server.php");
//$xajax->setFlag('debug',true);
$xajax->configure('decodeUTF8Input',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
$xajax->register(XAJAX_FUNCTION, "ChFolder");
$xajax->register(XAJAX_FUNCTION, "ChFolderInner");
$xajax->register(XAJAX_FUNCTION, "RnFolder");
$xajax->register(XAJAX_FUNCTION, "FormSave");
$xajax->register(XAJAX_FUNCTION, "GetNewMsgCount");

$xajax->register(XAJAX_FUNCTION, 'PmFolders');
$xajax->register(XAJAX_FUNCTION, 'PmFolderEdit');
$xajax->register(XAJAX_FUNCTION, 'PmFolderDel');

$xajax->register(XAJAX_FUNCTION, 'getSpamComplaints');
$xajax->register(XAJAX_FUNCTION, 'sendSpamComplaint');

// Р§РµСЂРЅРѕРІРёРєРё
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("SaveDraftContacts", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("CheckDraftsContacts", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("FillDraftForm", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("PostDraft", $_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.server.php"));

//Аякс обработчики попапа покупки ПРО
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROPayAccount", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetYandexKassaLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));
$xajax->register(XAJAX_FUNCTION, new xajaxUserFunction("quickPROGetPlatipotomLink", $_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.server.php"));