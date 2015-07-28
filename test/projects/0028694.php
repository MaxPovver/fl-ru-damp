<?php

//##0028694

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_helper.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");

//------------------------------------------------------------------------------


$rows = $DB->rows("SELECT * FROM landing_projects ORDER BY id DESC");

if ($rows) {
    foreach ($rows as $row) {
        print_r($row);
        echo '<br/>';
    }
}

exit;