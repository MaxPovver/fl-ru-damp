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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayback.php');

//------------------------------------------------------------------------------

$results = array();

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//------------------------------------------------------------------------------

$order_id = intval(@$_GET['order_id']);

try 
{
    if (!$order_id) throw new Exception('No order_id param');
    
    $orderModel = TServiceOrderModel::model();
    $orderModel->attributes(array('is_adm' => true));
    $orderData = $orderModel->getCard($order_id, 0);
    
    if (!$orderData || 
       !$orderModel->isStatusEmpClose() || 
       !$orderModel->isReserve()) throw new Exception('None');
    
    $reserveInstance = $orderModel->getReserve();
    
    if ($reserveInstance->isStatusReserve() && 
        $reserveInstance->isStatusBackError()) {
        
             $res = $reserveInstance->changeBackStatus(
                    ReservesModel::SUBSTATUS_PAYED);

             if ($res) {
                 $DB->update('reserves_payback', array(
                     'status' => 0,
                     'error' => 0,
                     'cnt' => 1000
                 ), 
                 'reserve_id = ?i', $reserveInstance->getID());
             }
            
             $results['changeBackStatus'] = (int)$res;
            
       } else {
           throw new Exception('Not allowed payback');
       }
}
catch (\Exception $e) 
{
    $results['Error Message'] = $e->getMessage();
} 
















//$reserveInstance = ReservesModelFactory::getInstanceById($id);






/*
try 
{
    $reservesPayback = ReservesPayback::getInstance();
    $results['doPayback'] = print_r($reservesPayback->doPayback(1),true);
} 
catch (\Exception $e) 
{
    $results['Error Message'] = $e->getMessage();
}   
*/

//------------------------------------------------------------------------------


/*
try 
{
    $reservesPayback = new ReservesPayback();
    $reservesPayback->requestPayback(15, 8687, 100000);
} 
catch (\Exception $e) 
{
    $results['Error Message'] = $e->getMessage();
}    
*/



//------------------------------------------------------------------------------


array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;