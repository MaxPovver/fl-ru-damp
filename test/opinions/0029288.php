<?php


ini_set('display_errors',0);
//error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/opinions.php');

//------------------------------------------------------------------------------



if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);


//------------------------------------------------------------------------------

$login = @$_GET['login'];

$user = new users();
$user->GetUser($login);

if ($user->uid > 0) {
    
    $result = opinions::GetCounts2($user->uid, array('total'));
    var_dump($result);
    
}


//------------------------------------------------------------------------------


exit;