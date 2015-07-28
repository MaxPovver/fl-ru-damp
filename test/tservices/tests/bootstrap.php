<?php

/**
 * @author dezinger
 */
// TODO: check include path
//ini_set('include_path', ini_get('include_path'));

//ini_set('display_errors',1);
//error_reporting(E_ALL);

//ini_set('display_errors',0);
//error_reporting(0);

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../../'), '/');
} 

define('ABS_PATH', $_SERVER['DOCUMENT_ROOT']);
//$_SESSION['login'] = 'alex';


//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");

session_start();
