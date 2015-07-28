<?php

//php /var/www/_beta/html/test/mail/newproj2.php
//php /var/www/_beta/html/test/mail/spam-debug.php
//clear;tail -20 /var/www/_beta/html/classes/pgq/logs/spam.pgq
//cat /var/log/maillog | grep kazakov@fl.ru
//clear; tail -50 /var/log/maillog

//php /Applications/MAMP/htdocs/visualpharm/fl/beta/test/mail/empproj.php
//php /var/www/_beta/html/test/mail/empproj.php
//clear;tail -20 /var/www/_beta/html/classes/pgq/logs/spam.pgq



ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

$path = $_SERVER['DOCUMENT_ROOT'];

require_once($path . "/classes/config.php");
require_once($path . "/classes/smail.php");
require_once($path . "/classes/projects.php");
require_once($path . "/classes/employer.php");
//require_once($path . "/classes/freelancer.php");

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' ); //???
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");



//------------------------------------------------------------------------------

$time_start = microtime(true); 


$mail = new smail();
$cnt = $mail->EmpNewProj();


$time_end = microtime(true);
$execution_time = number_format($time_end - $time_start,5);

print_r('execution_time = ' . $execution_time . ' sec');
print_r(PHP_EOL);
print_r('sended = ' . $cnt);
print_r(PHP_EOL);


exit;


//------------------------------------------------------------------------------

//1010101000000000
/*
$subscr = array(
    'some_index_1' => 1,
    'some_index_2' => 0,
    'some_index_3' => 111,
    'some_index_4' => 0,
    'some_index_5' => '1',
    'some_index_6' => 'ffff',
    'some_index_7' => 1.1,
    0
);

$user = new users;
$user->UpdateSubscr2(33, $subscr);

print_r(PHP_EOL);
exit;
*/

//------------------------------------------------------------------------------


$result = projects::GetNewProjectsPreviousDay($error, false, 10, true);

var_dump($result);
exit;


//------------------------------------------------------------------------------




/*
exit;



$time_start = microtime(true); 


//local
//$users = array(1,2);

//beta
$users = NULL;//array(1,2);

$mail = new smail();
//$mail->NewProj($users);
$cnt = $mail->NewProj2($users);



$time_end = microtime(true);
$execution_time = number_format($time_end - $time_start,5);

print_r('execution_time = ' . $execution_time . ' sec');
print_r(PHP_EOL);
print_r('sended = ' . $cnt);
print_r(PHP_EOL);

//var_dump($cnt);
exit;
 */