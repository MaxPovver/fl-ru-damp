<?php
/*
 * Скрипт рассылки о новых проектах для работодателей
 * Запускать раз в сутки!
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

$log = new log('hourly_newsletter_emp/'.SERVER.'-%d%m%Y[%H].log', 'w');
$log->writeln('------------ BEGIN hourly (start time: ' . date('d.m.Y H:i:s') . ') -----');

//if((int)date('H') == 1) {
    
    $mail = new smail();
    $log->TRACE( $mail->EmpNewProj() );

//}

$log->writeln('------------ END hourly    (total time: ' . $log->getTotalTime() . ') ---------------');