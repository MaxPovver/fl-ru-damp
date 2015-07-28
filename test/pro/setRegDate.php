<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");



if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//------------------------------------------------------------------------------

$login = @$_GET['login'];
$days = @$_GET['days'];
$days = (!$days)?60:$days;

//------------------------------------------------------------------------------


$user = new users();
$user->GetUser($login);

if($user->uid <= 0) {
    print_r('Not Found' . PHP_EOL);
    exit;
}

$ok = $DB->query("
    UPDATE users SET 
        reg_date = NOW() - '{$days} days'::interval
    WHERE uid = ?i
", $user->uid);

if($ok) {
    echo 'done!' . PHP_EOL;
}
        
exit;