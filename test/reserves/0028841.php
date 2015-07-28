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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayout.php');


//------------------------------------------------------------------------------


$results = array();

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//------------------------------------------------------------------------------

$reserve_id = intval(@$_GET['reserve_id']);

try 
{
    $is_repeat = !ReservesPayout::getInstance()->doPayout($reserve_id);
    
    $results['is_repeat'] = (int)$is_repeat;
} 
catch (\Exception $e) 
{
    $results['Error Message'] = iconv('cp1251','utf-8',$e->getMessage());
}   



array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));



exit;





$order_id = intval(@$_GET['order_id']);
$type = @$_GET['type'];

//------------------------------------------------------------------------------



//$type = 'dolcard';//'dolcard';//'ya';
//$order_id = 31392;

try 
{
    if(!$order_id) throw new Exception('No order_id param');
    if(!$type) throw new Exception('No type param');
        
    $orderModel = TServiceOrderModel::model();
    $orderModel->attributes(array('is_adm' => true));
    $orderData = $orderModel->getCard($order_id, 0);
    
    if(!$orderData || 
       !$orderModel->isStatusEmpClose() || 
       !$orderModel->isReserve()) throw new Exception('None');
    
    $reserveInstance = $orderModel->getReserve();
    
    if(!$reserveInstance->isAllowPayout($orderData['frl_id']) || 
       !$reserveInstance->isFrlAllowFinance()) throw new Exception('Not isAllowPayout');   

    $reservesPayout = new ReservesPayout();

    $status = $reservesPayout->requestPayout($reserveInstance, $type);
        
    $results['status'] = (int)$status;
       
    $is_done = $reserveInstance->changePayStatus($status);
    
    $results['is_done'] = (int)$is_done;
    
    
    if ($is_done) {
        //@todo: передача данных устаревший способ но оставляем для поддержки пока
        //посностью не передем на обьекты
        $orderData['reserve_data'] = $reserveInstance->getReserveData();
        //@todo: правильный способ - нужно оперировать обьектами
        $orderData['reserve'] = $reserveInstance;

        try {
            $doc = new DocGenReserves($orderData);
            //$doc->generateInformLetterFRL();

            //@todo: генерируем документ когда резерв закрыт после всех выплат
            if ($reserveInstance->isClosed()) {
                
                $results['isClosed'] = (int)$reserveInstance->isClosed();
                
                $doc->generateActServiceEmp();
                $doc->generateAgentReport();
            }                
        } catch (Exception $e) {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');
            $log = new log('reserves_docs/' . SERVER . '-%d%m%Y.log');
            $log->trace(sprintf("Order Id = %s: %s", $orderData['id'], $e->getMessage()));
        }
    }

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