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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');

//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayout.php');

require_once('ReservesPayoutTest.php');


//------------------------------------------------------------------------------


$results = array();


//------------------------------------------------------------------------------



$reservesPayout = new ReservesPayoutTest();
$results['cron'] = $reservesPayout->cron(3);








//$reservesPayout = new ReservesPayoutTest();
//$reservesPayout->errorLog(777, 'Некорректная сумма выплаты');
//$sum = 77777;
//print_r($reservesPayout->calcRequestList(777, $sum));
//$reservesPayout->saveToHistory(5);


/*
$reservesModel = new ReservesModel();

$res = $reservesModel->getReservesWithStatusPayByService(ReservesModel::SUBSTATUS_INPROGRESS);

if ($res) {
    
    $log = new log('reserves_docs/' . SERVER . '-%d%m%Y.log');
    $reservesPayout = new ReservesPayoutTest();
    
    foreach($res as $el) {
        
        //print_r($el);
        //exit;
        
        continue;
        
        $reserveInstance = ReservesModelFactory::getInstance($el['type']);
        $reserveInstance->setReserveData($el);
        
        $status = $reservesPayout->payout($reserveInstance, $el['pay_type']);
        $is_done = $reserveInstance->changePayStatus($status);
        
        if ($is_done && $reserveInstance->isClosed()) {
            
            $orderData = array(
                'id' => $el['src_id'],
                'reserve_data' => $el,
                'employer' => array(
                    'login' => $el['emp_login'],
                    'email' => $el['emp_email']
                )
            );

            try {
                $doc = new DocGenReserves($orderData);
                $doc->generateActServiceEmp();
                $doc->generateAgentReport();
            }
            catch(Exception $e) {
                $log->trace(sprintf("Order Id = %s: %s", $orderData['id'], $e->getMessage()));
            }
       }
    }
}
*/

//------------------------------------------------------------------------------


array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;