<?php

//https://beta.free-lance.ru/mantis/view.php?id=29300

//ini_set('display_errors',1);
//error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

$from_login = $_GET['from'];
$to_login = $_GET['to'];

$user = new users();

$user->GetUser($from_login);
if (!$user->uid) {
    echo iconv('cp1251','utf-8',"Не найден пользователь: {$from_login}");
    exit;
}

$from_id = $user->uid;

$user->GetUser($to_login);
if (!$user->uid) {
    echo iconv('cp1251','utf-8',"Не найден пользователь: {$to_login}");
    exit;
}

$to_id = $user->uid;


echo iconv('cp1251','utf-8',"Права и Роль учитваются только для залогиненых пользователей. <br/><br/>\n");

//messages::isAllowed($to_id, $from_id);
//messages::isAllowed($to_id, $from_id);        
//messages::isAllowed($to_id, $from_id);

if (messages::isAllowed($to_id, $from_id)) {
    echo iconv('cp1251','utf-8',"Переписка разрешена\n\n");
    exit;
}

echo iconv('cp1251','utf-8',"Переписка запрещена\n\n");
exit;