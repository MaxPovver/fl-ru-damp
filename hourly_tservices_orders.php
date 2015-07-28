<?php

/*
 * «апускать каждый час
 * 
 * –ассылка уведомлений о 
 * наличии новых заказов дл€ исполнителей за 24 и 72 часа
 * возможности оставить отзыв после завершени€ заказа за 24 и 72 часа.
 * 
 */

//ini_set('display_errors',1);
//error_reporting(E_ALL ^ E_NOTICE);

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    //@todo: укажите вместо '' относительное положение doc_root например '/../' 
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(dirname(__FILE__) . ''), '/');
} 

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/multi_log.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_smail.php");

$log = new log('hourly_tservices_orders/'.SERVER.'-%d%m%Y.log');
$log->writeln('------------ BEGIN hourly (start time: ' . date('d.m.Y H:i:s') . ') -----');



//------------------------------------------------------------------------------


try 
{
    $tservices_smail = new tservices_smail();
    $log->TRACE( $tservices_smail->inactiveOrders() );
    $log->TRACE( $tservices_smail->noneFeedbackOrders() );
} 
catch (Exception $e) 
{
    $log->TRACE($e->getMessage());
}


//------------------------------------------------------------------------------



$log->writeln('------------ END hourly    (total time: ' . $log->getTotalTime() . ') ---------------');