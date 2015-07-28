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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');


/**
БС0010271
БС#0010324
БС#0010554
БС#0010454
БС0010359
БС0010401
БС#0010900
в этих сделках нужно переключить статус в режим "сумма успешно зарезервирована
 */



$order_ids = array(
10271,
10324,
10554,
10454,
10359,
10401,
10900    
);


