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
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reqv.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayout.php');


//------------------------------------------------------------------------------


$results = array();


//------------------------------------------------------------------------------




try 
{

    $reservesPayout = new ReservesPayout();
    $result = $reservesPayout->getDestinationCardSynonim('4149497813559384','15100');//('4268033703545624', '1000000');
    print_r($result);
    exit;
    
    
    /*
    $request = new HTTP_Request2('https://paymentcard.yamoney.ru/gates/card/storeCard');
    $request->setMethod(HTTP_Request2::METHOD_POST)
            ->addPostParameter(array(
                'skr_destinationCardNumber' => '4268033703545624',
                'sum' => '1000000',
                //'skr_errorUrl' => 'fl.ru',
                //'skr_successUrl' => 'fl.ru'
                )); 
    
    
    $response = $request->send();//->getBody();

    $head = $response->getHeader();
    
    $query = parse_url($head['location'], PHP_URL_QUERY);
    parse_str($query, $results);
    
    print_r($results);
    
    
    //print_r($response);
    
    //print_r($request->send()->getHeader());
    //print_r(PHP_EOL);
    print_r($request->send()->getStatus());
    */
    
    //getDestinationCardSynonim
    
    
    
    
} 
catch (Exception $e) 
{
    print_r($e->getMessage());
}



exit;

//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;