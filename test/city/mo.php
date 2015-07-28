<?php

/**
 * Получить города Московской области и найти возможные ID у нас
 */

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

//if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//-------------------------------------------------------

exit;

//http://geo.webmoney.ru/XML/XMLGetGeoData.aspx?group=cities&id=1840

$url = 'http://geo.webmoney.ru/XML/XMLGetGeoData.aspx?group=cities&id=1840';
$data = file_get_contents($url);
$xml = simplexml_load_string($data);
$json = json_encode($xml);
$decodedArray = json_decode($json,TRUE); 

$city = array();
if(count($decodedArray)){
    foreach($decodedArray['row'] as $el){
        $city[] = iconv('utf-8','cp1251',$el['@attributes']['cityName']);
    }
}

//print_r($city);exit;

if($city) {
    
    $col = $DB->col("
        SELECT array_to_string(array_agg(id),',') 
        FROM city 
        WHERE country_id = 1 AND city_name IN(?l)
    ", $city);
    
    //print_r($DB->sql);
    print_r($col);
}





//print_r($decodedArray);
exit;




/*
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
*/


exit;