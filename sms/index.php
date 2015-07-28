<?php
define('NOCSRF', true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sms_gate.php");

$sms_gate = new sms_gate_listener();
$sms_gate->listener($_REQUEST, 'sms');

exit();