<?php
ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/session_Memcached.php");

echo '<p>1. $_SESSION</p>';
print_r($_SESSION);

$login = 'nopro1';
$session = new session();
$s = $session->get($login);
$session_data = $session->read($s['sid']);
echo '<p>2. $session_data</p>';
print_r($session_data);

$session_data = preg_replace(
        "/;pro_last\|(?:s:0:\"\"|s:[0-9]{2}:\".*\"|b\:0|N)/U",
        ";pro_last|s:9:\"777-999-5\"",$session_data
    );

echo '<p>3. $session_data</p>';
print_r($session_data);

$session->set($s['sid'],$session_data,7200);
$session_data = $session->read($s['sid']);

echo '<p>5. $session_data</p>';
print_r($session_data);

echo '<p>6. $_SESSION</p>';
print_r($_SESSION);