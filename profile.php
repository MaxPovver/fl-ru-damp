<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

session_start();

if (get_uid(0)) {
    header_location_exit('/users/' . $_SESSION['login'] . '/setup/main/');
} else {
    header_location_exit('/403.php');
}