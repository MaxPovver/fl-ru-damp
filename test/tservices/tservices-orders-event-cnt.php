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

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mem_storage.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');

//------------------------------------------------------------------------------


$results = array();


//------------------------------------------------------------------------------


//$profiler->start('fill_frl_mem');


//------------------------------------------------------------------------------


$tservicesOrderModel = new TServiceOrderModel();

$results['Cnt Event For Frelancer'] = $tservicesOrderModel->getCountEvents(7, FALSE);
$results['Cnt Event For Employer'] = $tservicesOrderModel->getCountEvents(33, TRUE);


//------------------------------------------------------------------------------


//$profiler->stop('fill_frl_mem');


//------------------------------------------------------------------------------


//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;