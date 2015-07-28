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



//------------------------------------------------------------------------------


$page = 0;
$page_size = 200;
$cnt = 0;

$ms = new MemStorage('newsletter_freelancer');
$ms->clear();


//------------------------------------------------------------------------------

$profiler->start('fill_frl_mem');

//------------------------------------------------------------------------------


while ( $users = freelancer::GetPrjRecps($error, ++$page, $page_size) ) {
    
    //@todo: fill more more for testing
    for($i = 0; $i < 1; $i ++)
    {
        $to_storage = array();
        
        foreach($users as $user)
        {
            $to_storage[$user['uid'] . '-' . $i] = $user;
       
            $cnt++;
        }
        
        $ms->addData($to_storage);
        unset($to_storage);
    }
}


//------------------------------------------------------------------------------

$profiler->stop('fill_frl_mem');

//------------------------------------------------------------------------------


$results['Total cnt'] = $cnt;
$results['getData'] = print_r($ms->getData(),true);
$results['Read 1th page. Items count'] = count($ms->getData());
$results['getDebugInfo'] = $ms->getDebugInfo();


//------------------------------------------------------------------------------

$results += array(
    'fill_frl_mem execution_time (sec)' => number_format($profiler->get('fill_frl_mem'),5)//,
    //'get_frl_idx execution_time (sec)' => number_format($profiler->get('get_frl_idx'),5)
);

//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;