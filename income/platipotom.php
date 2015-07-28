<?php

define('NO_CSRF', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/platipotom.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");

$platipotom = new platipotom();
$log = new log('platipotom/%d%m%Y.log');
$log->writeln('----- ' . date('d.m.Y H:i:s'));
$log->writevar($_GET);

$result = $platipotom->order();

$log->writeln('----- ' . (isset($result['status']) && $result['status'] == 1 ? 'SUCCESS' : 'FAIL'));
$log->writeln();

header('Content-Type: application/json');
echo json_encode($result);