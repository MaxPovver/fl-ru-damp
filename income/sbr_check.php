<?php

define('NO_CSRF', 1);
$request = $_POST;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pskb.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
if($_GET['key'] != pskb::KEY_CHECK_AUTH) exit(); // Авторизация
pskb::listenRequest('superCheck');
exit();