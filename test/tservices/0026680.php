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
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mem_storage.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_smail.php');

//------------------------------------------------------------------------------


$results = array();

//$profiler = new profiler();


//------------------------------------------------------------------------------


//$profiler->start('fill_frl_mem');


//------------------------------------------------------------------------------

//$results['locale'] = setlocale(LC_ALL, NULL);



$string = 'íåóæåëè? ýòà õóéíÿ ÍÅÐÀÁÎÒÀÅÒ íà áåòå èç-çà êîäèðîâêè? ñóêà? ÁËßÒÜ!';



$string = sentence_case($string); 

//$results['test'] = $string;

//$string = iconv('utf-8','cp1251',$string);
$string = iconv('cp1251','utf-8',$string);

$results['test'] = $string;


/*
$results['test1'] = iconv('cp1251','utf8',sentence_case($string)); 
$results['test2'] = iconv('cp1251','utf-8',sentence_case($string));
$results['test3'] = print_r( preg_split('/([.?!]+)/', $string, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE) ,true);

$results['test4'] = iconv('cp1251','utf8', ucfirst(mb_strtolower(trim($string))) );
$results['test5'] = iconv('cp1251','utf-8', ucfirst(mb_strtolower(trim($string))) );
*/

//------------------------------------------------------------------------------


//$profiler->stop('fill_frl_mem');


//------------------------------------------------------------------------------





//------------------------------------------------------------------------------



//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;