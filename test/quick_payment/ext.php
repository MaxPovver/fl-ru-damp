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
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/quick_payment/quickPaymentPopupFactory.php");


/*
$quickExtPaymentPopup = quickExtPaymentPopup::getInstance();


var_dump($quickExtPaymentPopup);
exit;
 */


$list = quickPaymentPopupFactory::getModelsList();

if ($list) {
    foreach ($list as $process) {
        $object = quickPaymentPopupFactory::getInstance($process);
        print_r(get_class($object) . PHP_EOL);
    }
}






exit;


$process = 'carusel';

$object = quickPaymentPopupFactory::getInstance($process);

print_r($object->render());
exit;

