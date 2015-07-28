<?php
//php /var/www/_beta/html/test/mail/newproj2.php

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


$mail = new smail();
//$mail->NewProj($users);
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

/*

local performance

execution_time = 6.59270 sec
sended = 12704

---холодный запуск
execution_time = 116.79537 sec
sended = 32704


1th optimization

execution_time = 13.43457 sec
sended = 32704

execution_time = 13.42381 sec
sended = 32704

---холодный запуск
execution_time = 111.76793 sec
sended = 52704

execution_time = 28.61685 sec
sended = 52704

execution_time = 28.40967 sec
sended = 52704
 



2th optim. BAD?
 
execution_time = 31.52172 sec
sended = 52704

execution_time = 30.99544 sec
sended = 52704

execution_time = 33.26990 sec
sended = 52704 


3th rollback to 1th and fix
 
execution_time = 24.79772 sec
sended = 52704
 
execution_time = 26.31185 sec
sended = 52704

execution_time = 26.16395 sec
sended = 52704




 */