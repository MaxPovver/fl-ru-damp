<?php

/**
 * @deprecated ѕока не используетс€
 */

exit;

/*
 * «апускать каждые 30 минут
 * [0,30 * * * *]
 * 
 * 
 *  рон обнаружени€ и генерации недостоющий документов 
 * по закрытым резервам заказов (Ѕ—).
 * 
 * Ёто временное решение от проблемы ошибки генерации документов "на лету"
 * ѕока генераци€ небудет перенесена в очередь обработки
 */

//ini_set('display_errors',1);
//error_reporting(E_ALL ^ E_NOTICE);

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    //@todo: укажите вместо '' относительное положение doc_root например '/../' 
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(dirname(__FILE__) . ''), '/');
} 

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/multi_log.php");

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

$log = new log('hourly_reserves_docgen/'.SERVER.'-%d%m%Y.log', 'a', '[%d.%m.%Y %H:%M:%S] ');

//------------------------------------------------------------------------------

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
    
    $log->writeln(sprintf("BEGIN hourly for %s reserves",count($rows)));
    $cnt = 0;
    
    foreach ($rows AS $row) {
        $order_id = $row['src_id'];
        $doc_types = $DB->array_to_php2($row['doc_types']);

        $log->writeln(sprintf("Order Id = %s", $order_id));
        
        try 
        {
            $orderModel = TServiceOrderModel::model();
            $orderModel->attributes(array('is_adm' => true));
            $orderData = $orderModel->getCard($order_id, 0);

            if(!$orderData || 
               !$orderModel->isStatusEmpClose() || 
               !$orderModel->isReserve()) {

                $log->writeln('Not isStatusEmpClose');
                continue;
            }

            $reserveInstance = $orderModel->getReserve();

            if(!$reserveInstance->isClosed()) {

                $log->writeln('Not isClosed');
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
                        $file_url = getFileUrl($doc->generateActCompletedFrl());
                        $log->writeln(sprintf("generateActCompletedFrl = %s", $file_url));
                    break;

                    case DocGenReserves::ACT_SERVICE_EMP_TYPE:
                        $file_url = getFileUrl($doc->generateActServiceEmp());
                        $log->writeln(sprintf("generateActServiceEmp = %s", $file_url));
                    break;

                    case DocGenReserves::AGENT_REPORT_TYPE:
                        $file_url = getFileUrl($doc->generateAgentReport());
                        $log->writeln(sprintf("generateAgentReport = %s", $file_url));
                    break;

                    case DocGenReserves::RESERVE_OFFER_CONTRACT_TYPE:
                    //case DocGenReserves::RESERVE_OFFER_AGREEMENT_TYPE:
                        $file_url = (int)$doc->generateOffers();
                        $log->writeln(sprintf("generateOffers = %s", $file_url));
                        break;

                    case DocGenReserves::LETTER_FRL_TYPE:
                        $file_url = getFileUrl($doc->generateInformLetterFRL());
                        $log->writeln(sprintf("generateInformLetterFRL = %s", $file_url));
                    break;

                    case DocGenReserves::ARBITRAGE_REPORT_TYPE:
                        $file_url = getFileUrl($doc->generateArbitrageReport());
                        $log->writeln(sprintf("generateArbitrageReport = %s", $file_url));
                    break;
                } 
            }
        
            $cnt++;
        } 
        catch (\Exception $e) 
        {
            $message = $e->getMessage();
            $log->writeln(sprintf("Error Message: %s", iconv('cp1251', 'utf-8', $message)));
        }   
    }
    
    $log->writeln(sprintf("END hourly. Well done %s reserves", $cnt) . PHP_EOL);
}