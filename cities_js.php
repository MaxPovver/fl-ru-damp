<?php 

define('IS_PHP_JS', true);

require_once $_SERVER["DOCUMENT_ROOT"]."/classes/stdf.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
$membuf = new memBuff();
$memkey = "b-combo-getcitiesandcountries";
$s = 0;//$membuf->get($memkey);
if (!$s) {
    $rows = country::GetCountriesByCountUser();
    $result = array(0=>"'0' : 'Все страны'");
    foreach ($rows as $k=>$i) {
        $i["name"] = str_replace('"', "\"", $i["name"]);
        $result[$i["id"]] = str_replace("'", "\'", $i["name"]);
    }
    //города России 1
    $rows = city::GetCities(1);
    $cResult = array("0"=>"'0' : 'Россия'", "undefined_value"=>"'undefined_value' : 'Все города'");
    foreach ($rows as $k=>$i) {
        $i = str_replace('"', "\"", $i);
        $i = str_replace("'", "\'", $i);
        $cResult[$k] = "'$k' : '$i'";
    }
    $result[1] = $cResult;
    //города Украины 2
    $rows = city::GetCities(2);
    $cResult = array("0"=>"'0' : 'Украина'", "undefined_value"=>"'undefined_value' : 'Все города'");
    foreach ($rows as $k=>$i) {
        $i = str_replace('"', "\"", $i);
        $i = str_replace("'", "\'", $i);
        $cResult[$k] = "'$k' : '$i'";
    }
    $result[2] = $cResult;
    //города Беларусии 10
    $rows = city::GetCities(10);
    $cResult = array("0"=>"'0' : 'Беларусь'", "undefined_value"=>"'undefined_value' : 'Все города'");
    foreach ($rows as $k=>$i) {
        $i = str_replace('"', "\"", $i);
        $i = str_replace("'", "\'", $i);
        $cResult[$k] = "'$k' : '$i'";
    }
    $result[10] = $cResult;
    //города Казахстана     38 
    $rows = city::GetCities(38);
    $cResult = array("0"=>"'0' : 'Казахстан'", "undefined_value"=>"'undefined_value' : 'Все города'");
    foreach ($rows as $k=>$i) {
        $i = str_replace('"', "\"", $i);
        $i = str_replace("'", "\'", $i);
        $cResult[$k] = "'$k' : '$i'";
    }
    $result[38] = $cResult;
        
    $tdata = array();
    foreach ($result as $k=>$i) {
        $inner = "'$i'";
        $item = "'$k' : $inner";
        if (is_array($i)) {
            $inner = join(",", $i); //join(",\n\t\t\t",
            $item  = "'$k' : "."{".$inner."}"; //"'$k' : \n\t{\n\t\t\t$inner\n\t\t}";
        }
        $tdata[] = $item;
    }
    $result = $tdata;
    $s = join(",", $result); //join(",\n"
    $s = "{".$s."}";
    $membuf->add($memkey, $s, 3600);
}
print('var citiesList = '.$s.'; citiesList["1"]["0"] = "Россия"; ' );
