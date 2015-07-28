<?php

//exit;

//https://beta.free-lance.ru/mantis/view.php?id=28663

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/base.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/api.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/mobile.php");

/*
$text = '
    F??©d?ration Camerounaise de Football
    when it is rendered on the browser it displays J???lio C?
';*/

$text = 'Привет как дела???';

$from_uid = 237958;
$uid = 238766;

externalApi_Mobile::addPushMsg($from_uid, 'message', array('from_user_id' => $uid, 'text' => stripslashes($text)));

//echo json_encode(pack("H*" ,'c32e'));
echo json_last_error();