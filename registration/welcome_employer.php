<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
session_start();

// страницу могут смотреть только зарегистрированые работодатели
if (!get_uid(0) || !is_emp()) {
    include $_SERVER['DOCUMENT_ROOT']."/403.php";
    exit;
}

$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.welcome_employer.php";

include ("../template2.php");
?>