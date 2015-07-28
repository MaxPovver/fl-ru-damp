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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing/BillPayback.php');


//------------------------------------------------------------------------------


$results = array();

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//------------------------------------------------------------------------------

$src_id = intval(@$_GET['src_id']);
$invoice_id = intval(@$_GET['invoice_id']);
$price = intval(@$_GET['price']);

try 
{
    if(!$src_id) throw new Exception('No src_id param');
    if(!$invoice_id) throw new Exception('No invoice_id param');
    if(!$price) throw new Exception('No price param');
    
    
    BillPayback::getInstance()->requestPayback(
        $src_id,
        $invoice_id,
        $price
    );
    
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