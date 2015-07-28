<?
$registration_folder = true;
$footer_registration = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard_registration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_employer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
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
}
unset($_SESSION["requestedRole"]);

// если регистрация через мастер
if ( !empty($_GET['m']) ) {
    $role = 0;
    if ( !empty($_GET['u']) ) {
        if ( $_GET['u'] == 'frl' ) {
            $role = wizard_registration::REG_FRL_ID;
        }
        if ( $_GET['u'] == 'emp' ) {
            $role = wizard_registration::REG_EMP_ID;
        }
    }
    if ( $role ) {
        $wizard = new wizard;
        setcookie($wizard->getCookieName('uid').$role, preg_replace('/[^a-z0-9]/', '', $_GET['m']), time() + 3600 * 24 * 180, '/', $GLOBALS['domain4cookie']);
        if ( $role == wizard_registration::REG_FRL_ID ) {
            setcookie($wizard->getCookieName('step').$role, step_freelancer::STEP_REGISTRATION_CONFIRM, time() + 3600 * 24 * 180, '/', $GLOBALS['domain4cookie']);
        }
        if ( $role == wizard_registration::REG_EMP_ID ) {
            setcookie($wizard->getCookieName('step').$role, step_employer::STEP_REGISTRATION_CONFIRM, time() + 3600 * 24 * 180, '/', $GLOBALS['domain4cookie']);
        }
    }
}

$registration = new registration();
if($registration->validActivateCode(__paramInit('string', 'code'))) {
    $code = true;
    
    $registration->listenerAction('activate_account');
} else {
    $code = false;
}

$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.activate.php";
$js_file = array( '/scripts/wizard/wizard.js' );
include ("../template2.php");
?>