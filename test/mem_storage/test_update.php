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

/*
$test = array(
    11 => 'aa',
    22 => 'bb'
);

unset($test[11],$test[22]);

var_dump(isset($test));
exit;
*/

//------------------------------------------------------------------------------

$results = array();

$profiler = new profiler();
$profiler->start('update_frl_mem');


//------------------------------------------------------------------------------


$ms = new MemStorage('newsletter_freelancer');


//$results['ID found in mem page'] = $ms->isExistItem(333);

//------------------------------------------------------------------------------

//update

/*
$uid = 333;

$item = freelancer::GetPrjRecp($uid);

//print_r($item);
//exit;

$item['login'] = 'super_freelancer333';
$item['email'] = 'super_freelancer333@test.lo';


$results['updateItem'] = (int)$ms->updateItem($uid, $item);
$results['getItem'] = $ms->getItem($uid);
*/


//------------------------------------------------------------------------------

//insert
//симул€ци€ новой записи
/*
$uid = 333;
$item = freelancer::GetPrjRecp($uid);

$item['uid'] = 333333333;

$results['insertItem'] = $ms->insertItem($item['uid'], $item);

$results['getItem'] = $ms->getItem($item['uid']);
*/

//------------------------------------------------------------------------------

//delete
$uid = 333333333;

$results['deleteItem'] = (int)$ms->deleteItem($uid);
$results['getItem'] = $ms->getItem($uid);

//------------------------------------------------------------------------------

//$page = 0;

/*
$cnt = 0;

while ( $users = $ms->getData() ) {
    $cnt += count($users);
    unset($users);
}
*

/*
while ( $users = freelancer::GetPrjRecps($error, ++$page, 200) ) {
    $cnt += count($users);
    unset($users);
}
*/

//$results['Read all cnt'] = $cnt;

//------------------------------------------------------------------------------


$profiler->stop('update_frl_mem');


//------------------------------------------------------------------------------

$results += array(
    'update_frl_mem execution_time (sec)' => number_format($profiler->get('read_frl_mem'),5)
);

//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;