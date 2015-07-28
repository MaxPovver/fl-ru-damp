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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DocGen/DocGenReserves.php');


//------------------------------------------------------------------------------



function getFileUrl($file) 
{
    if(!$file) return 0;
    return WDCPREFIX . '/'.$file->path . $file->name;
}


function deleteFiles($order_id, $types) 
{
    $types = !is_array($types)?array($types):$types;
    $rows = CFile::selectFilesBySrc('file_reserves_order', $order_id);
    
    if(!$rows) return 0;
    
    foreach($rows as $row)
    {
        if(!in_array($row['doc_type'], $types)) {
            continue;
        }
        
        $file = new CFile();
        $file->Delete($row['id']);
    }
}


//------------------------------------------------------------------------------

$results = array();
if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//------------------------------------------------------------------------------

$order_id = @$_GET['order_id'];
$state = @$_GET['state'];

try 
{   
    $orderModel = TServiceOrderModel::model();
    $orderModel->attributes(array('is_adm' => true));
    $orderData = $orderModel->getCard($order_id, 0);
    
    if(!$orderData || 
       !$orderModel->isReserve()) {
        throw new Exception('Not isReserve');
    }
    
    $reserveInstance = $orderModel->getReserve();
    $data = $reserveInstance->getReserveData();
    
    switch ($state) {
        case 1:
            $DB->start();

            $DB->query("
                UPDATE tservices_orders SET 
                    status = 1,
                    close_date = NULL
                WHERE id = {$order_id};
                    
                UPDATE reserves SET
                    status = 10,
                    date_complete = NULL,
                    status_pay = NULL, 	
                    status_back = NULL
                WHERE id = {$reserveInstance->getID()} AND src_id = {$order_id} AND type = 10;
                    
                INSERT INTO reserves_arbitrage(reserve_id, is_emp, frl_id, emp_id, status, message) 
                VALUES({$reserveInstance->getID()},true,{$data['frl_id']},{$data['emp_id']},0,'Спорный вопрос по заказу.');
                    
                DELETE FROM reserves_payout WHERE reserve_id = {$reserveInstance->getID()};
                DELETE FROM reserves_payout_reqv WHERE reserve_id = {$reserveInstance->getID()};
                DELETE FROM reserves_payback WHERE reserve_id = {$reserveInstance->getID()};
            ");    
            
 
            $DB->commit();
            
            $results["state {$state}"] = 'done';
            
            break;
    }
    
    
} 
catch (\Exception $e) 
{
    $message = $e->getMessage();
    $results[] = sprintf("Error Message: %s", iconv('cp1251', 'utf-8', $message));
}


//------------------------------------------------------------------------------


array_walk($results, function(&$value, $key){
    $value = (is_int($key))?
            sprintf('%s'.PHP_EOL, $value):
            sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;