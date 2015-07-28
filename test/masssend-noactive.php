<?php

ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/smail.php';

$mail = new smail;
$count = $mail->InactiveUsers();
echo "{$count} users\n";