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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/events.php');


class testIt 
{
    function test1($data)
    {
        return $data . '-22222';
    }
    
    function test2($data)
    {
        return $data . '-33333';
    }    
}

$t = new testIt();

Events::register('test', array($t,'test1'));
Events::register('test', array($t,'test2'));

$res = Events::trigger('test', '11111', 'array');

print_r(current($res));

exit;