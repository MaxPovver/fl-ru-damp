<?php
ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' ); //???
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

$user_obj = new users();

var_dump($user_obj->CountAll());


exit;