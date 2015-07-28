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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');



//------------------------------------------------------------------------------

$results = array();

//------------------------------------------------------------------------------

$reserveInstance = ReservesModelFactory::getInstance(ReservesModelFactory::TYPE_TSERVICE_ORDER);


$results['hasAfterReserveForEmpId'] = (int)$reserveInstance->hasAfterReserveForEmpId(2);

$results['hasReserveForFrlId'] = (int)$reserveInstance->hasReserveForFrlId(6);

$user = new users();
$user->GetUserByUID(6);//33);//200);
$results['isAllowEditFinance'] = (int)$reserveInstance->isAllowEditFinance($user->uid, $user->role);



//------------------------------------------------------------------------------



//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;