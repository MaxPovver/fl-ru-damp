<?php

//!!!!!!!
$where_document_root = '/../../';


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

define('LIB_PATH', dirname(__FILE__) . '/ga_libs/' );

spl_autoload_register(function ($class) {
    
    
    $class =  LIB_PATH . str_replace('\\', '/', $class) . '.php';
    
    //print_r($class);
    //print_r("\n");
    
    require_once $class; 
});

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . $where_document_root), '/');
} 

$path = $_SERVER['DOCUMENT_ROOT'];

//require_once($path . "/classes/config.php");
//require_once($path . "/test/mail/ga_libs/ss-ga/ss-ga.class.php");
//require_once($path . "/classes/smail.php");
//require_once($path . "/classes/projects.php");
//require_once($path . "/classes/freelancer.php");

//use ga_libs\UniversalAnalytics AS test1;

/*
$ssga = new ssga('UA-49016158-1');
$ssga->set_event('email', 'open', 777777);
$result = $ssga->send();


print_r($result);
exit;
*/


//print_r(uniqid());
//exit;



$user_id = rand(2005, 2014);
$value = rand(10000,50000);



$years = array(
    '2005 год',
    '2006 год',
    '2007 год',
    '2008 год',
    '2009 год',
    '2010 год',
    '2011 год',
    '2012 год',
    '2013 год',
    '2014 год',
    'менее недели назад'
);










$ua = new \UniversalAnalytics\UA(array(
    'v' => 1,
    'tid' => 'UA-49048745-1',//'UA-49016158-1',
    'cid' => '35009a79-1a05-49d7-b876-2b884d0f825b'//md5($user_id),
));


foreach($years as $year)
{

$label = iconv('cp1251', 'utf-8', $year);

$request = $ua->event(array(
    'category' => iconv('cp1251', 'utf-8','≈жедневна€ рассылка проектов по фрилансерам 2'),//'email',
    'action' => iconv('cp1251', 'utf-8','ќтправлено'),//'sended',//'open',
    'label' => $label,
    'value' => rand(10000,50000)
))
->track();

$response = $request->send();

}

//var_dump($response);


//var_dump($request->attributes);

exit;


/*

http://www.google-analytics.com/collect?v=1&tid=UA-49016158-1&cid=777777&t=event&ec=email&ea=open&el=777777&cs=newsletter&cm=email&cn=062413&cm1=1

 */