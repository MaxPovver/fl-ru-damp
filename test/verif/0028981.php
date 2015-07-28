<?php

//https://beta.free-lance.ru/mantis/view.php?id=28981

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/payment_keys.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXI.php';
        


$wmid = '968475351930';

$wmxi = new WMXI;
$key  = array( 'file' => WM_VERIFY_KEYFILE, 'pass' => WM_VERIFY_KEYPASS );
$wmxi->Classic(WM_VERIFY_WMID, $key);
$res = $wmxi->X11($wmid, 0, 1, 0);
$res = $res->toObject();

$v = $res->certinfo->attestat->row['tid'];

print_r($v);
exit;