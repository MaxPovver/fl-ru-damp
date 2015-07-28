<?php

//ini_set('display_errors',1);
//error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

$login = $_GET['login'];

$freelancer = new freelancer();
$freelancer->GetUser($login);

if ($freelancer->uid > 0) {

    $DB->query("
        UPDATE projects_offers_answers SET
            last_offer = last_offer - interval '1 day'
        WHERE uid = ?i
    ", $freelancer->uid);

   $data = $DB->row("SELECT * FROM projects_offers_answers WHERE uid = ?i", $freelancer->uid);
   
   print_r($data);
    
} else {
    print_r('Фрилансер не найден!');
}

exit;