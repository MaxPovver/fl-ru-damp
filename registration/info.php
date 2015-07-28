<?
$registration_folder = true;
$footer_registration = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
session_start();
$uid = get_uid(false);
// Зарегистрированным пользователям доступ к странице закрыт пользователи которые не регистрировались в текущей сессии тоже
if(!$uid) {
    include $_SERVER['DOCUMENT_ROOT']."/403.php";
    exit;
}
$action = __paramInit('string', null, 'action');
$ukey   = __paramInit('string', 'ukey', null);
$registration = new registration();
if($registration->checkUserAccess($_SESSION['uid'], true) && $action == null) {
    header("Location: /users/{$_SESSION['login']}/setup/info/");
    exit;
}

$registration->listenerAction($action);


$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.info.php";
$js_file = array( '/scripts/wizard/wizard.js' );
include ("../template2.php");

?>