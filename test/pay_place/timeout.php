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
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");


//------------------------------------------------------------------------------


$results = array();
if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);


//------------------------------------------------------------------------------

if (empty($_GET)) {
    print_r("
        Requred params: login, hours, type_place (optional) \n
        Example: \n 
            GET: login=Alex&hours=5
            CLI: login=Alex hours=5
            
");
    exit;
}

$login = @$_GET['login'];
$hours = (int)@$_GET['hours'];
$type_place  = @$_GET['type_place'];

if(!$type_place) {
    $type_place = 0;
}

try 
{

$user = new freelancer();
$user->GetUser($login);
if (!$user->uid) {
    throw new Exception('User not found'); 
}

$is_done = $DB->query("
    UPDATE " . pay_place::$_TABLE . "
    SET date_create = date_create - interval '{$hours} hours' 
    WHERE uid = ?i AND type_place = ?i
", $user->uid, $type_place);

if (!$is_done) {
    throw new Exception('Cant update ' . pay_place::$_TABLE); 
}

$is_done = $DB->query("
    UPDATE " . pay_place::$_TABLE_REQUEST . "
    SET date_published = date_published - interval '{$hours} hours' 
    WHERE uid = ?i AND type_place = ?i
", $user->uid, $type_place);

if (!$is_done) {
    throw new Exception('Cant update ' . pay_place::$_TABLE_REQUEST); 
}

$results['done?'] = 'Yep!';

} 
catch (\Exception $e) 
{
    $results['Error Message'] = $e->getMessage();
}  

//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;