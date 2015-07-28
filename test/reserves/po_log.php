<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_order_history.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayout.php');

//require_once('ReservesPayoutTest.php');


//------------------------------------------------------------------------------


$results = array();


//------------------------------------------------------------------------------

/*
$reservesPayout = new ReservesPayout();
//$reservesPayout->errorLog(111, 'Некорректная сумма выплаты2', 1000);

$techmessage = NULL;//'описание ошибки от сервиса';
if ($techmessage) $techmessage = " ({$techmessage})";

$reservesPayout->errorLog(123, sprintf(
        ReservesPayoutException::LAST_PAYED_FAIL, 
        1000, 
        41,
        $techmessage));
*/

//$sum = 77777;
//print_r($reservesPayout->calcRequestList(777, $sum));


//$reservesPayout->saveToHistory(5);


//------------------------------------------------------------------------------


array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;