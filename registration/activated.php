<?
// сюда перенаправляем пользователя после активации аккаунта созданного через мастер
$registration_folder = true;
$footer_registration = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.activated.php";

include ("../template2.php");
?>