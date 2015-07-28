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
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");



$res = pay_place::cronRequest();
print_r($res);
exit;


//-------------------------------------------------------




/*
$catalog = 0;
$payPlace = new pay_place(isset($catalog) ? $catalog : 1);
$res = $payPlace->cron(69683);
//$res = $payPlace->isDone();
var_dump($res);
exit;
*/

//-------------------------------------------------------

/*
$res = pay_place::getTypePlacesInRequest();
print_r($res);
exit;
*/


//-------------------------------------------------------

/*
$catalog = rand(0,1);
$payPlace = new pay_place(isset($catalog) ? $catalog : 1);



$res = $payPlace->cron_test();

print_r($res);
exit;
*/


//----------------------------------------------------

$catalog = rand(0,1);
$payPlace = new pay_place(isset($catalog) ? $catalog : 1);

$uid = rand(1, 10000);

$options = array(
    'uid' => $uid,
    'ad_header' => "Я пользователь #{$uid}",
    'ad_text' => "Это текст пользователя {$uid} до 500 символов!",
    'num' => rand(1,10),
    'hours' => rand(1,10)
);

$res = $payPlace->addUserRequest($options);

var_dump($res);
exit;