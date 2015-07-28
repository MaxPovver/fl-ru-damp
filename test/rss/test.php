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
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");


$projects_for_xml  = new_projects::getProjectsForXml('1 month');
new_projects::joobleGenerateRss('upload/jooble.xml', $projects_for_xml);
new_projects::indeedGenerateRss('upload/indeed.xml', $projects_for_xml);
new_projects::trovitGenerateRss('upload/trovit.xml', $projects_for_xml);        

print_r('done!');
exit;