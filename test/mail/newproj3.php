<?php

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' ); //???
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");


//------------------------------------------------------------------------------


$time_start = microtime(true); 


//------------------------------------------------------------------------------


$DB->query("
    UPDATE projects 
    SET post_date = NOW() - interval '24 hours'
    WHERE post_date >= DATE_TRUNC('day', NOW() - interval '24 hours')
");

$mail = new smail();
$cnt = $mail->NewProj2();

//------------------------------------------------------------------------------

$time_end = microtime(true);
$execution_time = number_format($time_end - $time_start,5);

print_r('execution_time = ' . $execution_time . ' sec');
print_r(PHP_EOL);
print_r('sended = ' . $cnt);
print_r(PHP_EOL);

exit;

//------------------------------------------------------------------------------