<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mem_storage.php");


//------------------------------------------------------------------------------


$results = array();

$profiler = new profiler();
$profiler->start('read_frl_mem');


//------------------------------------------------------------------------------


$ms = new MemStorage('newsletter_freelancer');


//------------------------------------------------------------------------------

/*
$profiler->start('read_frl_db');

$page = 0;
$cnt = 0;

while ( $users = freelancer::GetPrjRecps($error, ++$page, 200) ) {
    $cnt += count($users);
    unset($users);
}

$profiler->stop('read_frl_db');

$results['Read all from db cnt'] = $cnt;
$results['read_frl_db execution_time (sec)'] = number_format($profiler->get('read_frl_db'),5);
*/


//------------------------------------------------------------------------------


$profiler->clear();


$profiler->start('read_frl_mem');
$cnt = 0;

sleep(1);

/*
while ( $users = $ms->getData() ) {
    $cnt += count($users);
    sleep(1);
    unset($users);
}
*/

$profiler->stop('read_frl_mem');

//$results['getData'] = print_r($ms->getData(),true);
$results['isExistData'] = (int)$ms->isExistData();
$results['-'] = TRUE;
$results['Read all from mem cnt'] = $cnt;
$results['read_frl_mem execution_time (sec)'] = number_format($profiler->get('read_frl_mem'),5);
$results['getDebugInfo'] = $ms->getDebugInfo();



$results['-'] = TRUE;
$memBuff = new memBuff();
$results['memBuff'] = $memBuff->get('mem_storage-newsletter_freelancer-pages');
$results['getMemBuff'] = $ms->getMemBuff()->get('mem_storage-newsletter_freelancer-pages');




//------------------------------------------------------------------------------


//$profiler->stop('read_frl_mem');

//$results['ID found in mem page'] = $ms->isExistItem(333);



//------------------------------------------------------------------------------

/*
$results += array(
    'read_frl_mem execution_time (sec)' => number_format($profiler->get('read_frl_mem'),5)
);
*/

//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    if($key == '-') 
        $value = '------------------------------------------------------------------------------' . PHP_EOL;
    else
        $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;