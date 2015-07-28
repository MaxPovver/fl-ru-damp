<?php

ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once("../classes/config.php");
require_once("../classes/log.php");
$log = new log('massend-test-'.SERVER.'-%d%m%Y[%H].log', 'w');

$log->writeln('------------ BEGIN hourly (start time: ' . date('d.m.Y H:i:s') . ') -----');

require_once '../classes/stdf.php';
require_once("../classes/spam.php");

$spam = new spam();
$log->TRACE( $spam->frlLowFundsOffers2() );