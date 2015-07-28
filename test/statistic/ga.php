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
require_once(ABS_PATH . '/classes/statistic/StatisticFactory.php');



/*
$ga = StatisticFactory::getInstance('GA');
$ga->queue('event', array(
    'uid' => rand(1,100),
    'cid' => md5('solt-uid-' . rand(1,100)),
     'category' => 'Freelancer',
     'action' => 'registration'
));
*/



$cid = md5('test');
$data = array(
    'category' => 'Freelancer',
    'action' => 'registration'
);

try {        
        
    $ga = StatisticFactory::getInstance('GA', array('cid' => $cid, 'sc' => 'start', 'cd5' => 888));
    
    $res = $ga->call('event', $data);
    
    
    $url = $ga->getLastRequest()->getBaseUrlWithQuery();
    
    print_r($url);exit;
    
    
    print_r($res->getCoreResponse()->getHeaders());
    print_r($res->getCoreResponse()->getContent());
    exit;

} catch(Exception $e) {
    
    
    print_r($e->getMessage());
}


exit;