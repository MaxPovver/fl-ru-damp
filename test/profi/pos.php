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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

//------------------------------------------------------------------------------


$results = array();
if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);


//------------------------------------------------------------------------------

$login = @$_GET['login'];

isAllowProfi();

$user = new users();
$user->GetUser($login);

if ($user->uid > 0) {
    
    $rating = new rating($user->uid, $user->is_pro, $user->is_verify, $user->is_profi);
    $r_data = $rating->data;
    
    $user_profs = professions::GetProfessionsByUser($user->uid);
    
    if ($user_profs) {
        foreach ($user_profs as $up) {
            $rating_pos[] = professions::GetCatalogPosition($user->uid, $user->spec_orig, $r_data['total'], $up, $user->is_pro == 't');
        }
        
        if($rating_pos) {
            $results['rating_total'] = $r_data['total'];
            foreach ($rating_pos as $pos) {
                $results[iconv('CP1251','UTF-8',$pos['prof_name'])] = $pos['pos'];
            }
        }
    }
}


//------------------------------------------------------------------------------


array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;