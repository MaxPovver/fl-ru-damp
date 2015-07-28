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
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_ext.php");


//------------------------------------------------------------------------------


//------------------------------------------------------------------------------

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//------------------------------------------------------------------------------


$string_query = @$_GET['find'];
$string_query = iconv('UTF-8','CP1251', $string_query);

$host = @$_GET['host'];
$port = @$_GET['port'];

if (!$port) {
    $port = 3312;
}

//------------------------------------------------------------------------------

$types = array(
    'works',
    'messages',
    'commune',
    'notes',
    'users_all',//нужны права админа!!!
    'users_ext',
    //'users_test',
    'users_simple',
    'users',
    'tservices'
);

define('ON_PAGE', 1);

$uid = 1;
$filter = null;
$page = 1;

$search = new searchExt($uid);
//$search->SetServer($CFG_META['sphinx']['host'], $CFG_META['sphinx']['port']);
$search->setUserLimit(ON_PAGE);

if ($types) {
    foreach ($types as $type) {
        
        $searchElement = $search->addElement($type, true, ON_PAGE);
        
        if($host) {
            $searchElement->SetServer($host, $port);
        }
        
        $search->searchByType($type, $string_query, $page, $filter);
        $elements = $search->getElements();
        $element = $elements[$type];

        print_r("{$type}: {$element->total}" . PHP_EOL);
    }
}


exit;