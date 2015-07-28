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
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reqv.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');


//------------------------------------------------------------------------------


$results = array();
//$profiler = new profiler();


//------------------------------------------------------------------------------


$uid = 33;

$reqvs = sbr_meta::getUserReqvs($uid);
$reqv = $reqvs[$reqvs['form_type']];

//Проверка наличия резерва средств
$reserve_id = 3;
$reserveInstance = ReservesModelFactory::getInstanceById($reserve_id);
$reserve_data = $reserveInstance->getReserveData();

$sum = $reserve_data['reserve_price'];
$sum = ($sum < 10 ? 10 : $sum);
$reqv['price'] = $sum;

$reserveInstance->getReservesBank()->generateInvoice($reqv);




//------------------------------------------------------------------------------

//$profiler->start('fill_frl_mem');

//------------------------------------------------------------------------------




//------------------------------------------------------------------------------

//$profiler->stop('fill_frl_mem');

//------------------------------------------------------------------------------


//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;