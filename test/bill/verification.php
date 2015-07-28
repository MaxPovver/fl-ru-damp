<?php
require_once($_SERVER['DOCUMENT_ROOT'] ."/classes/session_Memcached.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");


$login = 'danil5';

$session = new session();
$session->UpdateVerification($login);