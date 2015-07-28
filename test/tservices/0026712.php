<?php

//0026712

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

//define('IN_STDF',1);

//require_once $_SERVER['DOCUMENT_ROOT'].'/classes/memBuff.php';
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DB.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages_tservices.php");

//------------------------------------------------------------------------------



$results = array();

//$profiler = new profiler();


//------------------------------------------------------------------------------


//$profiler->start('fill_frl_mem');


//------------------------------------------------------------------------------



messages_tservices::sendOrderStatus();


//------------------------------------------------------------------------------


//$profiler->stop('fill_frl_mem');


//------------------------------------------------------------------------------





//------------------------------------------------------------------------------



//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;