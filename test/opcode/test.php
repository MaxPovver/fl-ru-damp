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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/op_codes_price.php');

exit;

//$data = op_codes_price::getAllOpCodesPrice();

$data = op_codes_price::getOpCodePrice(143);

print_r($data);
exit;