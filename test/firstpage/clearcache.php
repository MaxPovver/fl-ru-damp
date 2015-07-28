<?php


ini_set('display_errors',0);
//error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");

//------------------------------------------------------------------------------


$results = array();
//if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);


//------------------------------------------------------------------------------


$sql = 
"SELECT user_id
 FROM users_first_page as ufp
INNER JOIN
 freelancer f
   ON f.uid = ufp.user_id
  AND f.is_banned='0'
WHERE ufp.payed = true
  AND ufp.from_date <= now() AND ufp.from_date + ufp.to_date >= now()";

$ret = $DB->rows($sql);

$mc = new memBuff();        

if ($ret) {
    foreach($ret as $el){
        $mc->touchTag('firstpage.user' . $el['user_id']);
    }
}

//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;