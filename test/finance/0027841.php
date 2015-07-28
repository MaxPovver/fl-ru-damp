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

//-------------------------------------------------------

exit;

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//-------------------------------------------------------

$is_update = isset($_GET['update']);


$url = 'http://www.artlebedev.ru/tools/country-list/xml/';
$key = md5($url);

$memBuff = new memBuff();
if((!$decodedArray = $memBuff->get($key)) || $is_update) {
    $data = file_get_contents($url);
    $xml = simplexml_load_string($data);
    $json = json_encode($xml);
    $decodedArray = json_decode($json,TRUE); 

    $memBuff->set($key, $decodedArray, 3600);
}

$sql = '';

if (isset($decodedArray['country']) && !empty($decodedArray['country'])) {
    foreach($decodedArray['country'] as $country){
        
        $sql .= $DB->parse("UPDATE country SET iso = ?i WHERE iso_code3 = ?;", $country['iso'], $country['alpha3']) . PHP_EOL;
                
    }
    
    print_r($sql);
}

exit;