<?php

exit;

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


//------------------------------------------------------------------------------

$results = array();


//------------------------------------------------------------------------------

try 
{
    //успешные сделки
    $rows = $DB->rows("
        SELECT 
            fro.src_id,
            array_agg(fro.doc_type)::int[] AS doc_types
        FROM file_reserves_order AS fro 
        INNER JOIN reserves AS r ON r.src_id = fro.src_id 
        WHERE 
            r.status IN(20,30) 
        GROUP BY fro.src_id 
        HAVING 
            array_agg(fro.doc_type)::int[] @> ARRAY[10,20,30,40,50,60] = false AND
            array_agg(fro.doc_type)::int[] @> ARRAY[10,20,30,40,50,60,70] = false AND 
            array_agg(fro.doc_type)::int[] @> ARRAY[20,30,40,50,60,70] = false
        LIMIT 10;
    ");
    
    
    if ($rows) {
        foreach ($rows AS $row) {
            $order_id = $row['src_id'];
            $doc_types = $DB->array_to_php2($row['doc_types']);

            $orderModel = TServiceOrderModel::model();
            $orderModel->attributes(array('is_adm' => true));
            $orderData = $orderModel->getCard($order_id, 0);
            
            if(!$orderData || 
               !$orderModel->isStatusEmpClose() || 
               !$orderModel->isReserve()) {
                continue;
            }
            
            
            $reserveInstance = $orderModel->getReserve();
    
            if(!$reserveInstance->isClosed()) {
                continue;  
            }
            
            $doc_types = array_unique($doc_types);
            
            $base_doc_types = array(10,20,30,40,50,60);
            
            if ($reserveInstance->isArbitrage()) {
                
                $base_doc_types = $reserveInstance->isStatusPayPayed()?
                        array(10,20,30,40,50,60,70):
                        array(20,30,40,50,60,70);
            }

            $doc_types = array_diff($base_doc_types, $doc_types);

            $history = new tservices_order_history($order_id);
            $doc = new DocGenReserves($orderData);
            
            foreach ($doc_types as $doc_type) {
                switch ($doc_type) {
                    case DocGenReserves::ACT_COMPLETED_FRL_TYPE:
                        getFileUrl($doc->generateActCompletedFrl());
                    break;
                
                    case DocGenReserves::ACT_SERVICE_EMP_TYPE:
                        getFileUrl($doc->generateActServiceEmp());
                    break;
            
                    case DocGenReserves::AGENT_REPORT_TYPE:
                        getFileUrl($doc->generateAgentReport());
                    break;
            
                    case DocGenReserves::RESERVE_OFFER_CONTRACT_TYPE:
                    case DocGenReserves::RESERVE_OFFER_AGREEMENT_TYPE:
                        break;
                
                    case DocGenReserves::LETTER_FRL_TYPE:
                        getFileUrl($doc->generateInformLetterFRL());
                    break;
                
                    case DocGenReserves::ARBITRAGE_REPORT_TYPE:
                        getFileUrl($doc->generateArbitrageReport());
                    break;
                }
            }
            
            print_r($order_id . PHP_EOL);
            //exit;
            
        } 
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