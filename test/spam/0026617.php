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
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");

//------------------------------------------------------------------------------


$results = array();
$profiler = new profiler();


//------------------------------------------------------------------------------

$profiler->start('spam');

//------------------------------------------------------------------------------



$mail = new smail();
$results['cnt'] = $mail->sendFrlProjectsExec();


/*
$list = projects::getFrlExec('2014-01-01',NULL,1,10);
$res = DB::array_to_php($list[1]['projects_list']);
print_r(explode('||', $res[0]));
exit;
*/

//------------------------------------------------------------------------------

$profiler->stop('spam');

//------------------------------------------------------------------------------


$results += array(
    'execution_time (sec)' => number_format($profiler->get('spam'),5)
);


//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;