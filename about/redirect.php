<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/config.php';

if ( preg_match('/^[-_a-zA-Z0-9]+\.pdf$/', $_GET['f']) ) {
    header("Location: " . WDCPREFIX . "/about/documents/{$_GET['f']}");
} else {
    header ("Location: /404.php");
}