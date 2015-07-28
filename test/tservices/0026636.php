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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_smail.php');

//------------------------------------------------------------------------------


$results = array();

//$profiler = new profiler();


//------------------------------------------------------------------------------


//$profiler->start('fill_frl_mem');


//------------------------------------------------------------------------------



//$results['data'] = print_r(TServiceOrderModel::model()->getInactiveOrders(),true);
//$results['data'] = print_r(TServiceOrderModel::model()->getNoneFeedbackOrders(),true);

//print_r($DB->sql);
//exit;


$DB = new DB('master');//$_SESSION['DB'];
if(!isset($DB)) exit;

//77, 78, 79, 80
//74 73 17 72

$DB->query("
    UPDATE tservices_orders 
    SET date = NOW() - interval '1 day'
    WHERE id = 77
");

$DB->query("
    UPDATE tservices_orders 
    SET date = NOW() - interval '3 days'
    WHERE id = 78
");

$DB->query("
    UPDATE tservices_orders 
    SET date = NOW() - interval '1 day'
    WHERE id = 79
");

$DB->query("
    UPDATE tservices_orders 
    SET date = NOW() - interval '3 days'
    WHERE id = 80
");


$tservices_smail = new tservices_smail();
$results['inactiveOrders'] = $tservices_smail->inactiveOrders();
$results['noneFeedbackOrders'] = $tservices_smail->noneFeedbackOrders();


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