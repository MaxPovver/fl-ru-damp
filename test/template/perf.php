<?php
//php /var/www/_beta/html/test/mail/perf.php

//!!!!!!!
$where_document_root = '/../../';


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');


if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . $where_document_root), '/');
} 

$path = $_SERVER['DOCUMENT_ROOT'];

require_once($path . "/classes/config.php");
require_once($path . "/classes/template.php");

//require_once($path . "/classes/smail.php");
//require_once($path . "/classes/projects.php");
//require_once($path . "/classes/freelancer.php");


$time_start = microtime(true); 

for($i=0; $i<(100 * 5000); $i++)
{
    
/*    
$html = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/new_projects/project_layout.tpl.php', 
                array(
                    'projects' => '%MESSAGE%',
                    'host' => $GLOBALS['host'],
                    'title' => '%TITLE%',
                    'unsubscribe_url' => '%UNSUBSCRIBE_URL%'
                )
        );
*/
    
$html = Template::render(
        $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/new_projects/project.tpl.php',
        array(
            'utm_param' => '?utm_source=newsletter4' . rand(1,100),
            'url'   => $GLOBALS['host'],
            'name'  => 'Название',
            'descr' => 'Описание',
            'host' => $GLOBALS['host'],
            'project_kind' => 1,
            'project_pro_only' => TRUE,
            'project_verify_only' => TRUE,
            'project_urgent' => TRUE,
            'price' => '5000',//rand(1,10000000),
            'end_date' => '',
            'create_date' => ''
        )
    );
}


$time_end = microtime(true);
$execution_time = number_format($time_end - $time_start,5);///60;

//print_r(strlen($html));
//print_r(PHP_EOL);
print_r($execution_time . ' sec');
print_r(PHP_EOL);

exit;