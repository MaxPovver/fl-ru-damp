<?php

/*
 * Запускать:
 * 0 0 * * 1,3,5
 * 
 * Рассылка по:
 * 
 * - ответившим на проекты
 * - исполнителям проектов
 * - работодателям проектов
 * 
 * выбирает получателей по кварталам (3мес)
 * каждый запуск на 3 мес в прошлое
 * см. статус и отчет в таблице projects_spam_interval
 * игнорирует тех кому уже слали, хранит тут projects_spam_is_send
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

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");

$log = new log('hourly_projects_newsletter/'.SERVER.'-%d%m%Y[%H].log', 'w');
$log->writeln('------------ BEGIN hourly (start time: ' . date('d.m.Y H:i:s') . ') -----');


//if((int)date('H') == 1) {
    
    //$mail = new smail();
    //$log->TRACE();

//}

//------------------------------------------------------------------------------

$mail = new smail();

//------------------------------------------------------------------------------

//ответившим на проекты (пока самый тяжелый)
$log->TRACE( $mail->sendFrlOffer() );

//------------------------------------------------------------------------------

//исполнителям проектов
$log->TRACE( $mail->sendFrlProjectsExec() );

//------------------------------------------------------------------------------

//работодателям проектов
$log->TRACE( $mail->sendEmpPrjFeedback() );

//------------------------------------------------------------------------------



$log->writeln('------------ END hourly    (total time: ' . $log->getTotalTime() . ') ---------------');