<?php
/**
 * Обработка новых рассылок 
 */

ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once("classes/config.php");
require_once("classes/log.php");
require_once("classes/mailer.php");

$log = new log('massend/massend-trace-'.SERVER.'-%d%m%Y[%H].log', 'w');

$log->writeln('------------ BEGIN hourly_spam (start time: ' . date('d.m.Y H:i:s') . ') -----');

$mailer = new mailer();

$log->TRACE( $mailer->digestSend() );
$log->TRACE( $mailer->getMailerSend() );

$log->writeln('------------ END hourly_spam    (total time: ' . $log->getTotalTime() . ') ---------------');