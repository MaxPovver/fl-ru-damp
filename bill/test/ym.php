<?php

/**
 * Ёмул€ци€ ответа от яƒ при запросах на выплату
 */

require_once(__DIR__ . "/../../classes/stdf.php");
require_once(ABS_PATH . '/classes/YandexMoney3/Utils/Array2XML.php');

use YandexMoney3\Utils\Array2XML;

if (is_release())  {
    exit;
}

$method = __paramInit('string', 'm', NULL, NULL);

$bodyRaw = file_get_contents('php://input');

if (empty($bodyRaw)) {
    exit;
}

$xml = simplexml_load_string($bodyRaw);
$json = json_encode($xml);
$decodedArray = json_decode($json,TRUE); 

$is_fail = false;

$clientOrderId = @$decodedArray['@attributes']['clientOrderId'];

if (!$clientOrderId) {
    $is_fail = true;
}

$converter = new Array2XML();
$converter->setConvertFromEncoding('windows-1251');

$converter->setTopNodeName($method . 'Response');

if ($is_fail) {
    $converter->importArray(array(
        'clientOrderId' => $clientOrderId,
        'status' => 3,
        'error' => 41,
        'processedDT' => date('c')
    ));
    
    echo $converter->saveXml();
}

switch($method) {
    case 'testDeposition':
        
        /**
         * <?xml version="1.0" encoding="UTF-8"?>
         * <testDepositionResponse clientOrderId="12345" status="0" processedDT="2011-07-01T20:38:01.000Z"/>
         */
        $converter->importArray(array('@attributes' => array(
            'clientOrderId' => $clientOrderId,
            'status' => 0,
            'processedDT' => date('c')
        )));
    break;



    case 'makeDeposition':

        /**
         * <?xml version="1.0" encoding="UTF-8"?>
         * <makeDepositionResponse clientOrderId="12345" status="0" processedDT="2011-07-01T20:38:01.000Z" balance="1000.00"/>
         */
        $converter->importArray(array('@attributes' => array(
            'clientOrderId' => $clientOrderId,
            'status' => 0,
            'processedDT' => date('c'),
            'balance' => 10000
        )));
    break;
}

echo $converter->saveXml();
exit;