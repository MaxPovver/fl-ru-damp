<?
$registration_folder = true;
$footer_registration = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
session_start();
$uid = get_uid(false);
$validUser = true;
if ($uid) {
    if (    
              (($_SESSION['role'][0] == 1)&&($_SESSION["requestedRole"] == "empl"))
           || (($_SESSION['role'][0] == 0)&&($_SESSION["requestedRole"] != "empl"))
       ) {
           $validUser = false;
       } else {
       	   $validUser = true;
       }
} else {
    if (empty($_SESSION['email'])) {
        $validUser = false;
    }
}
// Зарегистрированным пользователям доступ к странице закрыт пользователи которые не регистрировались в текущей сессии тоже
if(!$validUser) {
    include $_SERVER['DOCUMENT_ROOT']."/403.php";
    exit;
}

$registration = new registration();
$registration->listenerAction(__paramInit('string', null, 'action'));

$is_suspect = isset($_SESSION['suspect']) ? $_SESSION['suspect'] : false;
$no_attempts = isset($_SESSION['activate_resend_attempts']) && $_SESSION['activate_resend_attempts'] == 0;
$allow_resend_mail = !$is_suspect && !$no_attempts;

$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.complete.php";

$js_file = array('registration/complete.js');

include ("../template2.php");
?>