<?php
ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/session_Memcached.php");

$action = $_GET['action'];

switch ($action) {
    case 'update':
        $login = 'nopro2';
        $session = new session();
        $session->UpdateProEndingDate($login);
        break;
    
    case 'clear':
        $_SESSION['pro_last'] = false;
        //no break
        
    case 'check':
        var_dump($_SESSION);
        break;

    default:
        break;
}