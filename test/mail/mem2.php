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
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/memBuff2.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");



//------------------------------------------------------------------------------

$results = array();

$profiler = new profiler();
$profiler->start('fill_frl_mem');


//------------------------------------------------------------------------------
//Обьем занимаемый 1 милионом записей (пожатые скорей всего)
//http://monosnap.com/image/Av6zqOhHG5XTTu1jee4tIstCLkn7WY.png
//http://monosnap.com/image/uvvNljrvWdaAqKcyECSawSeKzm5xCR.png
//http://monosnap.com/image/XnPzZYgfbqvJwGKMdH3TEJzxWLnNwb.png
//http://monosnap.com/image/zaHlyosCO4bxDZOlzOv4S2vwF49OIu.png


define('LIFE_TIME', 86400);
define('FRL_KEY','newsletter_freelancer');
define('FRL_TAG','newsletter_freelancer');
define('FRL_PAGES','newsletter_freelancer_pages');
define('FRL_INDEX','newsletter_freelancer_index');

$page = 0;
$page_size = 200;
$indexs = array();
//$indexs_str = '';

$membuf = new memBuff();


//print_r($membuf->get(FRL_PAGES));
//exit;

//$membuf->flush();
//$membuf->flushGroup(FRL_TAG);
//exit;

if(!$membuf->get(FRL_PAGES))
{
    
while ( $users = freelancer::GetPrjRecps($error, ++$page, $page_size) ) {
    $to_storage = array();
    
    foreach($users as $user)
    {
        $to_storage[$user['uid']] = $user;
        $indexs[$user['uid']] = $page;
        //$indexs_str .= $user['uid'] . ':' . $page . '|';
        
        //@todo: fill more more for testing
        for($i = 0; $i < 19; $i ++)
        {
            $user['uid'] .= '_' . $i; 
            $to_storage[$user['uid']] = $user;
            $indexs[$user['uid']] = $page;
            //$indexs_str .= $user['uid'] . ':' . $page . '|';
        }
    }
    
    $key = FRL_KEY . '-' . $page_size . '-' . $page;
    $membuf->add($key, $to_storage, LIFE_TIME, FRL_TAG);

    unset($to_storage);
}

if($page > 0)
{
    $membuf->add(FRL_PAGES, $page, LIFE_TIME, FRL_TAG);
    $membuf->add(FRL_INDEX, $indexs, LIFE_TIME, FRL_TAG); 
    //$membuf->add(FRL_INDEX, $indexs_str, LIFE_TIME, FRL_TAG);
}

unset($indexs);

}//END IF

//------------------------------------------------------------------------------

$profiler->stop('fill_frl_mem');

//------------------------------------------------------------------------------

//$results['$indexs_str'] = strlen($indexs_str);

/*
$profiler->start('get_frl_idx');


//$results['index'] = count($membuf->get(FRL_INDEX));
$idxs = $membuf->get(FRL_INDEX);
$results['isset_idx'] = (int)isset($idxs[2]);


$profiler->stop('get_frl_idx');
*/

//------------------------------------------------------------------------------


$results += array(
    'fill_frl_mem execution_time (sec)' => number_format($profiler->get('fill_frl_mem'),5),
    'get_frl_idx execution_time (sec)' => number_format($profiler->get('get_frl_idx'),5)
);

//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;