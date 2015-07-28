<?php
/**
 * Скрипт обновления в 0 поля подписки на рассылку 
 * для работодателей до 2014 года
 */


//ini_set('display_errors',1);
//error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

$path = $_SERVER['DOCUMENT_ROOT'];

require_once($path . "/classes/config.php");

//------------------------------------------------------------------------------

require_once($path . "/classes/log.php");
require_once($path . "/classes/multi_log.php");
$log = new log('empproj/'.SERVER.'-%d%m%Y[%H].log', 'w');

//------------------------------------------------------------------------------

$DB = $GLOBALS['DB'];

$H = (int)date('H'); //текущий час
$start_hour = 1; //час начала

//индекс по расписанию
$key = $start_hour - $H;


//расписание обновлений
$schedule = array(
    0 => "reg_date >= '2013-01-01' AND reg_date < '2014-01-01'",
    1 => "reg_date >= '2012-01-01' AND reg_date < '2013-01-01'",
    2 => "reg_date >= '2011-01-01' AND reg_date < '2012-01-01'",
    3 => "reg_date >= '2010-01-01' AND reg_date < '2011-01-01'",    
    4 => "reg_date >= '2009-01-01' AND reg_date < '2010-01-01'",
    5 => "reg_date >= '2008-01-01' AND reg_date < '2009-01-01'",
    6 => "reg_date >= '2007-01-01' AND reg_date < '2008-01-01'",
    7 => "reg_date < '2007-01-01'"
    
    /*
    0 => "reg_date >= '2012-01-01' AND reg_date < '2014-01-01'",//176779
    1 => "reg_date >= '2010-01-01' AND reg_date < '2012-01-01'",//155680 
    2 => "reg_date >= '2008-01-01' AND reg_date < '2010-01-01'",//111534 
    3 => "reg_date >= '2006-01-01' AND reg_date < '2008-01-01'",
    4 => "reg_date < '2006-01-01'"
     */
);

//Нет ничего в расписании
if(!isset($schedule[$key])) exit;

$log->writeln('------------ BEGIN hourly (start time: ' . date('d.m.Y H:i:s') . ') -----');

if($key == 0)
{
   $log->TRACE(
           
   $DB->query('
        DROP INDEX IF EXISTS "ix employer/is_spm_subscr";
    ')
           
    );
    $log->writeln("DROP INDEX: ix employer/is_spm_subscr");
}

//На момент обновлений в БД 457235 записей
$log->TRACE(
        
$DB->query("
    UPDATE employer 
    SET 
        subscr = subscr & B'1111111111110111' --12 бит вырубаем ежедневную рассылку
    WHERE 
        {$schedule[$key]} 
    AND subscr & B'0000000000001000' = B'0000000000001000'
    AND is_banned = B'0';
")
        
);
$log->writeln("UPDATE EXECUTED FOR: {$schedule[$key]}");
        
        
if($key == (count($schedule)-1))
{
$log->TRACE(
        
$DB->query("
CREATE INDEX \"ix employer/is_spm_subscr\"
  ON employer
  USING btree
  (uid)
 WHERE 
 subscr & B'0000000000001000' = B'0000000000001000'
 AND is_banned = B'0'::\"bit\";    
")

 );
 $log->writeln("CREATE INDEX: ix employer/is_spm_subscr");
}


$log->writeln('------------ END hourly    (total time: ' . $log->getTotalTime() . ') ---------------');
exit;