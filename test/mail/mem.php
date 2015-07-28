<?php

//php /Applications/MAMP/htdocs/visualpharm/fl/beta/test/mail/mem.php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/memBuff2.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");



//------------------------------------------------------------------------------


$time_start = microtime(true); 


//------------------------------------------------------------------------------

$page = 0;
$uids = array();
$page_size = 200;

//$key = md5(__FILE__);

$membuf = new memBuff();
//$membuf->setOption(Memcached::OPT_COMPRESSION, false);

//$membuf->delete($key);
//$membuf->flushGroup('newsletter_freelancers');
//$all_users = $membuf->get($key);

//print_r($all_users);
//exit;

/*
$membuf->set('test','abs');
$membuf->append('test','def');

print_r($membuf->get('test'));
exit;
*/


//if(empty($all_users))
if(false)
{
    $all_indexs = array();
    
    while ( $users = freelancer::GetPrjRecps($error, ++$page, $page_size, $uids) ) {
        
        $all_users = array();
        
        foreach($users as $user)
        {
            for($i = 0; $i < 20 ;$i++)
            {
                $all_users[$user['uid'] . '_' . $i] = $user;
            }
        }
        
        $all_indexs += array_fill_keys(array_keys($all_users),$page);
        //exit;
        
        //array_
        
        /*
        $json_data = json_encode($all_users);
        $json_data = substr($json_data,0,-1);
        
        if($page > 1)
        {
            $json_data = substr_replace($json_data ,",",0,1);
            
            //print_r($json_data);
            //print_r(PHP_EOL);
            //exit;
            
            $res = $membuf->append(__FILE__, $json_data);
            
            //print_r($page);//$membuf->getResultMessage());
            
            //print_r((int)$membuf->getOption(Memcached::OPT_COMPRESSION));
            print_r(PHP_EOL);
            exit;
        }
        else
        {
            $membuf->set(__FILE__, $json_data, 86400, 'newsletter_freelancers');
            
            //print_r($membuf->getResultMessage());
            //print_r(PHP_EOL);
            //exit;
        }
        
        if($page > 4) break;
        */
        
        //$json_data = json_encode($all_users);
        //$json_data = substr($json_data,0,-1);
        
        
        //print_r($json_data);
        //exit;
        
        //$key = md5(__FILE__ . $page_size . $page);
        
        //$key = md5(__FILE__) . '-' . $page_size . '-' . $page;
        //$membuf->add($key, $all_users, 86400, 'newsletter_freelancers'); 
        
        //exit;
        
        //unset($all_users);
    }
    
    //$membuf->append(__FILE__, '}');
    
    
    $membuf->set('newsletter_freelancers_page', $page, 86400,'newsletter_freelancers'); 
    $membuf->set('newsletter_freelancers_index', $all_indexs, 86400,'newsletter_freelancers'); 
    
        //$all_indexs
}


$total_indexs = $membuf->get('newsletter_freelancers_index');

print_r(count($total_indexs));
print_r(PHP_EOL);
exit;


//------------------------------------------------------------------------------


$total_pages = $membuf->get('newsletter_freelancers_page');
$cnt = 0;

/*
if($total_pages) for($page = 1; $i <= $total_pages; $i++)
{
    $all_users = array();
    $key = md5(__FILE__) . '-' . $page_size . '-' . $page;
    $all_users = $membuf->get($key);
    $cnt += count($all_users);
    unset($all_users);
}
*/

//------------------------------------------------------------------------------



/*
$items = array(
    'key1' => 'value1',
    'key2' => 'value2',
    'key3' => 'value3'
);
$membuf->setMulti($items);
$result = $membuf->getMulti(array('key1', 'key3', 'badkey'), $cas);
var_dump($result, $cas);
exit;

*/

//array_walk($list, 'make_list_keys', 'news_item_');




$keys = array();

if($total_pages) for($page = 1; $page <= 40/*$total_pages*/; $page++)
{
    $keys[] = md5(__FILE__) . '-' . $page_size . '-' . $page . (defined('SERVER')?SERVER:'');
}


/*
print_r($keys);
exit;
*/

$all_users = $membuf->getMulti($keys,$cas);

//$test = $membuf->get($keys[0]);
//print_r(count($test));

//print_r($membuf->getResultMessage());
//print_r(PHP_EOL);
//exit;



$cnt = count($all_users);

//print_r($cnt);
//print_r(PHP_EOL);
//exit;


//------------------------------------------------------------------------------

/*
$json_data = $membuf->get(__FILE__);

print_r($json_data);
exit;


$all_users = json_decode($json_data);

$cnt = count($all_users);
*/

//------------------------------------------------------------------------------


//$time_start = microtime(true); 


//------------------------------------------------------------------------------



//$total_pages = $membuf->get('newsletter_freelancers_page');



//$cnt = $total_pages;



//$all_users = $membuf->get($key);

//$json = json_encode($all_users);

//$cnt = count($all_users);

//$cnt = (strlen($json)/1024)/1024;


//------------------------------------------------------------------------------

$time_end = microtime(true);
$execution_time = number_format($time_end - $time_start,5);

print_r('execution_time = ' . $execution_time . ' sec');
print_r(PHP_EOL);

print_r('size = ' . $cnt);
print_r(PHP_EOL);

//print_r('key = ' . $key);
//print_r(PHP_EOL);

print_r($membuf->getStats());
print_r(PHP_EOL);

exit;

//------------------------------------------------------------------------------