<?php
ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../'), '/');
} 

$path = $_SERVER['DOCUMENT_ROOT'];


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");


$result = getCBRates();

var_dump($result);