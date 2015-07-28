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

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_helper.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");

//------------------------------------------------------------------------------


$results = array();

//$profiler = new profiler();


//------------------------------------------------------------------------------


//$profiler->start('fill_frl_mem');


//------------------------------------------------------------------------------







//$results['link'] = projects_helper::getStatusUrl('25','decline',6);


$id = 1907699;

$counte = projects::CountProjectByID($id);
$page = floor($counte / $GLOBALS["prjspp"]) + 1;
$counte_page = $counte % $GLOBALS["prjspp"];


$results['$counte'] = $counte;
$results['$page'] = $page;
$results['$counte_page'] = $counte_page;



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