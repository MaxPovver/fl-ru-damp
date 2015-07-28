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
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayout.php');

require_once('ReservesPayoutTest.php');


//------------------------------------------------------------------------------


$results = array();


//------------------------------------------------------------------------------

$type = 'dolcard';//'dolcard';//'ya';
//$order_id = 35708;
//$uid = 703979;

$order_id = 10886;
$uid = 729373;

try 
{

    $orderModel = TServiceOrderModel::model();
    $orderData = $orderModel->getCard($order_id, $uid);
    
    if(!$orderData || 
       !$orderModel->isStatusEmpClose() || 
       !$orderModel->isReserve()) throw new Exception('None');
    
    $reserveInstance = $orderModel->getReserve();
    
    //if(!$reserveInstance->isAllowPayout($uid) || 
    //   !$reserveInstance->isFrlAllowFinance()) throw new Exception('Not isAllowPayout');   

    $reservesPayout = new ReservesPayoutTest();

/*
    $sum = $reserveInstance->getPayoutSum();
    
    $res = $reservesPayout->getUserReqvs($uid, $type, $sum);
    
    var_dump($res);
*/    

    $status = $reservesPayout->requestPayout($reserveInstance, $type);
        
    $results['status'] = (int)$status;
       
    $is_done = $reserveInstance->changePayStatus($status);
    
    $results['is_done'] = $is_done;
    
} 
catch (\Exception $e) 
{
    $results['Error Message'] = $e->getMessage();
}   





//------------------------------------------------------------------------------


array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;