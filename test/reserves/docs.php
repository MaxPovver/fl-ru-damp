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


$results = array();

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//------------------------------------------------------------------------------

$order_id = intval(@$_GET['order_id']);
$doc_type = @$_GET['type'];

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

try 
{
    if(!$order_id) throw new Exception('No order_id param');
    if(!$doc_type) throw new Exception('No type param');     
    
    $orderModel = TServiceOrderModel::model();
    $orderModel->attributes(array('is_adm' => true));
    $orderData = $orderModel->getCard($order_id, 0);
    
    if(!$orderData || 
       !$orderModel->isStatusEmpClose() || 
       !$orderModel->isReserve()) {
        
        throw new Exception('None');
    }
       
    $reserveInstance = $orderModel->getReserve();
    
    if(!$reserveInstance->isClosed()) {
        throw new Exception('Not isClosed');   
    }
    
    $types = explode(',', $doc_type);
    if(!count($types)) throw new Exception('No type param');  
    
    $history = new tservices_order_history($order_id);
    $doc = new DocGenReserves($orderData);
    
    foreach ($types as $type) {
        
        $type = trim($type);
        
        switch ($type) {
            case DocGenReserves::ACT_COMPLETED_FRL_TYPE:
                deleteFiles($order_id, $type);
                $results['generateActCompletedFrl'] = getFileUrl($doc->generateActCompletedFrl());
                break;
            
            case DocGenReserves::ACT_SERVICE_EMP_TYPE:
                deleteFiles($order_id, $type);
                $results['generateActServiceEmp'] = getFileUrl($doc->generateActServiceEmp());
                break;
            
            case DocGenReserves::AGENT_REPORT_TYPE:
                deleteFiles($order_id, $type);
                $results['generateAgentReport'] = getFileUrl($doc->generateAgentReport());
                break;
            
            case DocGenReserves::LETTER_FRL_TYPE:
                deleteFiles($order_id, $type);
                $results['generateInformLetterFRL'] = getFileUrl($doc->generateInformLetterFRL());
                break;
        }
    }
    
    if(empty($results)) {
        $results['Nothink found?'] = 'Yep, sorry master.';
    }
    
} 
catch (\Exception $e) 
{
    $message = $e->getMessage();
    $results['Error Message'] = iconv('cp1251','utf-8',$message);
}   





//------------------------------------------------------------------------------


array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;