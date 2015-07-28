<?php
//TODO Файл нужен только для тестирования. Весь функционал отсюда делается в hourly.php
// Хотя проблем его вызов не принесет

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

require_once("classes/config.php");
require_once("classes/log.php");
require_once("classes/multi_log.php");
$log = new log('hourly_freelancer/'.SERVER.'-%d%m%Y[%H].log', 'w');

$log->writeln('------------ BEGIN hourly (start time: ' . date('d.m.Y H:i:s') . ') -----');

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

$mail = new smail();

//За сутки до завершения срока действия закрепления
$mail->remindFreelancerbindsProlong();

//После того, как закрепление опустилось ниже середины списка закреплений (и в списке больше одного закрепления)
$mail->remindFreelancerbindsUp();


$log->writeln('------------ END hourly_freelancer    (total time: ' . $log->getTotalTime() . ') ---------------');
