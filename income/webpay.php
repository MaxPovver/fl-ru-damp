<?php

define('NO_CSRF', 1);

$fp = fopen('/var/tmp/webpay.log', 'a');

fwrite($fp, '-- H:i:s' . date('Y-m-d H:i:s') . ' ------' . "\n");
fwrite($fp, var_export($_GET, true) . "\n");
fwrite($fp, var_export($_POST, true) . "\n");
exit;

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/webpay.php';

if ( !empty($_POST) ) {
    $webpay = new webpay;
    $webpay->income($_POST);
}

