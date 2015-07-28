<?php


ini_set('display_errors',0);
//error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);


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
if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);


//------------------------------------------------------------------------------

/*
if (empty($_GET)) {
    print_r("
        Requred params: order_id (optional), days (optional, default 1 day) \n
        Example: \n 
            GET: order_id=7777&days=1
            CLI: order_id=7777 days=1
            
");
    exit;
}
*/

//------------------------------------------------------------------------------

$order_id = intval(@$_GET['order_id']);
$days = intval(@$_GET['days']);
if (!$days) $days = 1;

try 
{

    if ($order_id > 0) {
        $DB->query("
            UPDATE tservices_orders 
            SET close_date = NOW() - interval '{$days} days'
            WHERE id = ?i AND status = ?i
        ", $order_id, TServiceOrderModel::STATUS_EMPCLOSE);
    }

    $tservices_smail = new tservices_smail();
    //$results['inactiveOrders'] = $tservices_smail->inactiveOrders();
    $results['noneFeedbackOrders'] = $tservices_smail->noneFeedbackOrders();
    
} 
catch (\Exception $e) 
{
    $results['Error Message'] = $e->getMessage();
}  


//------------------------------------------------------------------------------



//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;