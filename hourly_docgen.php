<?php

/*
 * «апускать каждые 30 минут
 * [0,30 * * * *]
 * 
 * 
 *  рон генерации документов из очереди
 * 
 */

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    //@todo: укажите вместо '' относительное положение doc_root например '/../' 
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(dirname(__FILE__) . ''), '/');
} 

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/multi_log.php");

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DocGen/DocGenQueue.php');


//------------------------------------------------------------------------------

$docGenQueue = new DocGenQueue();

$docGenQueue->cron();