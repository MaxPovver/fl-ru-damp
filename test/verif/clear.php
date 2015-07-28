<?php

//https://beta.free-lance.ru/mantis/view.php?id=28981

//ini_set('display_errors',1);
//error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

$wmid = intval($_GET['wmid']);

if (!$wmid) {
    echo 'Укажите параметр wmid';
    exit;
}

$done = $DB->query("DELETE FROM verify_webmoney WHERE wmid = ?", $wmid);

if ($done) {
    echo 'Привязка к WMID удалена.';
}

exit;