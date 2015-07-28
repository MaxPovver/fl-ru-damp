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


define('ON_PAGE', 20);

$type = 'users_all';
$uid = get_uid(false);

$string_query = 'Алексей';
$page = 1;
$filter = false;

$search = new searchExt($uid);
$search->setUserLimit(ON_PAGE);
$search->addElement($type, true, ON_PAGE);
$search->searchByType($type, $string_query, $page, $filter);
$elements = $search->getElements();
$element = $elements[$type]; 


//echo '<pre>error: ', $element->getEngine()->GetLastError(), '</pre>';
//echo '<pre>warn : ', $element->getEngine()->GetLastWarning(), '</pre>';


print_r($element->results);
exit;


/*
$frls = $element->results;
$size = $element->total;
$works = $element->works;*/