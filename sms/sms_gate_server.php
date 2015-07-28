<?php
define('NOCSRF', true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sms_gate.php");

$sms_server = new sms_gate_server();
$sms_server->listener($_REQUEST);

exit();