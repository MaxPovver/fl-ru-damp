<?php


ini_set('display_errors',0);
//error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_smail.php');

//------------------------------------------------------------------------------


$results = array();
if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);


//------------------------------------------------------------------------------

$id = intval(@$_GET['id']);
$kind = intval(@$_GET['kind']);
if (!$kind) $kind = 1;

try 
{

    if ($id > 0) {
        $DB->query("
            UPDATE tservices_binds 
            SET 
                date_stop = NOW() + interval '23 hours',
                sent_prolong = false
            WHERE tservice_id = ?i AND kind = ?i
        ", $id, $kind);
    }

    $tservices_smail = new tservices_smail();
    $results['remind24hEndBinds'] = $tservices_smail->remind24hEndBinds();
    
} 
catch (\Exception $e) 
{
    $results['Error Message'] = $e->getMessage();
}  


//------------------------------------------------------------------------------



//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;