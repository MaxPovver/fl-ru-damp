<?php

/**
 * Тест генератора счетов для ЛС пользователя
 */

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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/users.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DocGen/DocGenBill.php');


//------------------------------------------------------------------------------


$results = array();

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//------------------------------------------------------------------------------

$login = @$_GET['login'];
$sum = abs(@$_GET['sum']);

//------------------------------------------------------------------------------


function getFileUrl($file) 
{
    if(!$file) return 0;
    return WDCPREFIX . '/'.$file->path . $file->name;
}

try 
{
    if(!$login) throw new Exception('No login param');
    if(!$sum) throw new Exception('No sum param');
    
    $userObj = new users();
    $userObj->GetUser($login);
    
    if($userObj->uid <= 0) {
        throw new Exception("Not find user with login: {$login}");
    }
    
    $doc = new DocGenBill();
    $results['generateBankInvoice'] = getFileUrl($doc->generateBankInvoice($userObj->uid, $login, $sum));
    
} 
catch (\Exception $e) 
{
    $message = $e->getMessage();
    $results['Error Message'] = iconv('cp1251','utf-8',$message);
}   





//------------------------------------------------------------------------------


array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;