<?php
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

define('IS_OPENED', true); 

if (!$_SERVER['DOCUMENT_ROOT']) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');

$log = new log('hourly_sbr/'.SERVER.'-%d%m%Y[%H].log', 'w');
$log->writeln('------------ BEGIN hourly_sbr (start time: ' . date('d.m.Y H:i:s') . ') -----');

/**
 * ѕровер€ем сделки на просрочку 
 */
$sbr_meta = new sbr_meta();
$sbr_meta->renewalWorkStagesByFrozen();
//if(date('G') == 1) {
$sbr_meta->checkStageOvertime();
//}

if (date('H') == 0 || date('H') == 1) {
    pskb::checkExpired();
}

if (date('H') % 4 == 0 ) { // раз в 4 часа
    pskb::checkBankCovered();
    pskb::checkStagePayoutsCompleted();
}
// ƒолжен отработать все сделки до 15 декабр€
$log->TRACE( pskb::fillingSuperCheck() );

if (date('H') == 23) {
    pskb::prolongPaused();
}