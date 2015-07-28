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

//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayBack.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/YandexMoney3/YandexMoney3.php');


//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/YandexMoney3/ReturnPaymentRequest.php');



//------------------------------------------------------------------------------


$results = array();


//------------------------------------------------------------------------------

//use YandexMoney3\Request\DepositionRequest;
//use YandexMoney3\Request\BalanceRequest;
use YandexMoney3\Request\ReturnPaymentRequest;
use YandexMoney3\YandexMoney3;



$apiMwsFacade = YandexMoney3::getMwsApiFacade(array(
    'crypt' => array(
        'encrypt_cert_path' => ABS_PATH . '/classes/reserves/data_mws/certnew_vaan.cer',
        //'decrypt_cert_path' => ABS_PATH . '/classes/reserves/data/deposit.cer',
        'private_key_path' => ABS_PATH . '/classes/reserves/data_mws/private_mws.key',
        'passphrase' => 'swirls53.quarks'
    ),
    'uri_test' => 'https://penelope-demo.yamoney.ru:8083',
    'uri_main' => 'https://penelope.yamoney.ru',
    
    'is_test' => true
));



$returnPaymentRequest = new ReturnPaymentRequest();
$returnPaymentRequest->setShopId(17233);
$returnPaymentRequest->setClientOrderId(777);
$returnPaymentRequest->setInvoiceId(888);
$returnPaymentRequest->setCurrency(10643);//643
$returnPaymentRequest->setCause('Возврат средств Заказчику БС#00001 по решению арбитража.');
$returnPaymentRequest->setAmount('1.0');


//print_r($returnPaymentRequest->getDefinedParams());

try 
{
    $result = $apiMwsFacade->returnPayment($returnPaymentRequest);
} 
catch (\Exception $e) 
{
    $results['test Error Message'] = $e->getMessage();
}    

if($result)
{
    $results['test isSuccess'] = $result->isSuccess();
    $results['test getDefinedParams'] = print_r($result->getDefinedParams(),true);
}


/*
$reservePayBack = new ReservesPayBack();
*/







//------------------------------------------------------------------------------


array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;