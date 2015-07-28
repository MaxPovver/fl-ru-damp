<?php

define('NO_CSRF', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/cardpay.php');
$cardpay = new cardpay();
$cardpay->checkdeposit($_POST);