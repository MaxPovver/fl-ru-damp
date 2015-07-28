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

$order_ids = @$_GET['order_ids'];
$doc_types = 5;
unset($_GET['order_ids']);

try 
{
    $order_ids = ($order_ids)?explode(',', $order_ids):$order_ids;
    if (!$order_ids || !count($order_ids)) {
        throw new Exception('No order_ids param');
    }
    
    $doc_types = ($doc_types)?explode(',', $doc_types):$doc_types;
    if (!$doc_types || !count($doc_types)) {
        throw new Exception('No types param');
    }
    
    //успешные сделки
    $rows = $DB->rows("
        SELECT 
            fro.src_id,
            array_agg(fro.doc_type)::int[] AS doc_types
        FROM file_reserves_order AS fro 
        WHERE 
            fro.src_id IN(?l)
        GROUP BY fro.src_id 
    ", $order_ids);
    
    if ($rows) {
        foreach ($rows AS $row) {
            $order_id = $row['src_id'];
            $exists_doc_types = $DB->array_to_php2($row['doc_types']);
            $exists_doc_types = array_unique($exists_doc_types);
            
            $results[] = sprintf("Order Id = %s", $order_id);
            
            try 
            {
                $orderModel = TServiceOrderModel::model();
                $orderModel->attributes(array('is_adm' => true));
                $orderData = $orderModel->getCard($order_id, 0);
                
                if(!$orderData || 
                   !$orderModel->isReserve()) {
                    
                    $results[] = 'Not isReserve';
                    continue;
                }

                
                $reserveInstance = $orderModel->getReserve();

                if ($reserveInstance->isReserveByService()) {
                    $results[] = 'Not isReserveByService';
                    continue;
                }
                
                deleteFiles($order_id, $doc_types);
                
                $exists_doc_types = array_diff($exists_doc_types, $doc_types); 
                $base_doc_types = array(5);
                $needed_doc_types = array_diff($base_doc_types, $exists_doc_types);

                $history = new tservices_order_history($order_id);
                $doc = new DocGenReserves($orderData);

                if(!empty($_GET)) {
                    foreach($_GET as $key => $value) {
                        $value = iconv("utf-8", "windows-1251", $value);
                        $doc->setOverrideField($key, $value);
                    }
                }
                
                foreach ($needed_doc_types as $needed_doc_type) {
                    switch ($needed_doc_type) {
                        case DocGenReserves::BANK_INVOICE_TYPE:
                            
                            $reserveBank = $reserveInstance->getReservesBank();
                            
                            if ($reserveBank) {
                                $reqv = $reserveBank->getCheckByReserveId($reserveInstance->getID());
                                if ($reqv) {
                                    $file_url = getFileUrl($reserveInstance->getReservesBank()->generateInvoice2($reqv));
                                } else {
                                    $file_url = 'Not CheckByReserveId';
                                }
                            } else {
                                $file_url = 'Not ReservesBank';
                            }    

                            $results[] = sprintf("generateBankInvoice = %s", $file_url);
                        break;
                        
                        case DocGenReserves::ACT_COMPLETED_FRL_TYPE:
                            $file_url = getFileUrl($doc->generateActCompletedFrl());
                            $results[] = sprintf("generateActCompletedFrl = %s", $file_url);
                        break;

                        case DocGenReserves::ACT_SERVICE_EMP_TYPE:
                            $file_url = getFileUrl($doc->generateActServiceEmp());
                            $results[] = sprintf("generateActServiceEmp = %s", $file_url);
                        break;

                        case DocGenReserves::AGENT_REPORT_TYPE:
                            $file_url = getFileUrl($doc->generateAgentReport());
                            $results[] = sprintf("generateAgentReport = %s", $file_url);
                        break;

                        case DocGenReserves::RESERVE_OFFER_CONTRACT_TYPE:
                        //case DocGenReserves::RESERVE_OFFER_AGREEMENT_TYPE:
                            $file_url = (int)$doc->generateOffers();
                            $results[] = sprintf("generateOffers = %s", $file_url);
                            break;

                        case DocGenReserves::LETTER_FRL_TYPE:
                            $file_url = getFileUrl($doc->generateInformLetterFRL());
                            $results[] = sprintf("generateInformLetterFRL = %s", $file_url);
                        break;

                        case DocGenReserves::ARBITRAGE_REPORT_TYPE:
                            $file_url = getFileUrl($doc->generateArbitrageReport());
                            $results[] = sprintf("generateArbitrageReport = %s", $file_url);
                        break;
                    } 
                }
                
                
            } 
            catch (\Exception $e) 
            {
                $message = $e->getMessage();
                $results[] = sprintf("Error Message: %s", iconv('cp1251', 'utf-8', $message));
            }
        }
    } else {
        $results[] = 'Not found';
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