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
require_once(ABS_PATH . '/classes/Backup/Factory.php');

//------------------------------------------------------------------------------


//print_r(get_include_path());
//exit;


$filepath = 'users/DO/DOWNshifter/reserves/685/f_432548836a60bcd1.pdf';

//создаем указанный обьект дл€ работы с сервисом
$backupServiceInstance = Backup_Factory::getInstance(
        $BACKUP_SERVICE['type'], 
        $BACKUP_SERVICE['options']);

//https://betadav.free-lance.ru/users/DO/DOWNshifter/reserves/685/f_432548836a60bcd1.pdf
//$backupServiceInstance->create($filepath);

$backupServiceInstance->createContainer('deleted');