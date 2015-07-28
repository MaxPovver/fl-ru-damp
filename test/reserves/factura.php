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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DocGen/DocGenReserves.php');

//require_once(__DIR__ . '/../models/ReservesAdminReestrFacturaModel.php');

//------------------------------------------------------------------------------

    function getOrderId($orderName) 
    {
        return (int)preg_replace("/[^0-9]/", "", $orderName);
    }

    function parseFile($filename) 
    {
        //@todo: это не красиво :(
        ini_set('max_execution_time', 300);
        //ini_set('memory_limit', '512M');
        
        $uri = WDCPREFIX_LOCAL . '/reserves/factura/' . $filename;
        
        $list = array();
        $ids = array();
        $handle = fopen($uri, 'r');
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			if ($data[0] == 'order_id' || count($data) != 7) {
				continue;
			} 
            
            //order_id;sf_num;sf_date;sf_summa;pp_num;pp_date;pp_type
            $res = array(
                'id' => getOrderId($data[0]), //номер сделки,
                'sf_num' => $data[1], //Номер счета-фактуры
                'sf_date' => $data[2], //Дата счета фактуры
                'sf_summa' => $data[3], //Сумма счета фактуры
                'pp_num' => $data[4], //Номер платежного документа
                'pp_date' => $data[5], //Дата дата платежного документа
                'pp_type' => $data[6] //тип платежного документа (Якасса или банк)
            );
            $ids[] = $res['id'];
            $list[] = $res;
        }        
        fclose($handle);

        //print_r($list);
        
        
        if ($list) {
           
           $reserveModel = ReservesModelFactory::getInstance(
                   ReservesModelFactory::TYPE_TSERVICE_ORDER); 
           
           $empData = $reserveModel->getEmpByReserveIds($ids);

           print_r($empData);
           
           
           /*
           foreach ($list as $data) {

                if (!isset($empData[$data['id']])) {
                    continue;
                }

                $data['employer']['login'] = $empData[$data['id']]['login'];
                $data['employer']['uid'] = $empData[$data['id']]['uid'];

                try {
                    $doc = new DocGenReserves($data);
                    $doc->generateFactura();
                } catch (Exception $e) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');
                    $log = new log('reserves_docs/' . SERVER . '-%d%m%Y.log');
                    $log->trace(sprintf("Order Id = %s: %s", $data['id'], $e->getMessage()));
                }
           } */
        }
    }





    parseFile('f_77054c9e522f0cac.csv');




exit;