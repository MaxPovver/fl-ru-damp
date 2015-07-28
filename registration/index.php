<?php
define('IS_USER_ACTION', 1);
$g_page_id = "0|42";
$registration_page = $registration_folder = true;
$footer_registration = true;
$hide_banner_top = true;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard.php"); 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");

session_start();

$uid = get_uid(false);
$_SESSION["requestedRole"] = $requestedRole = __paramInit("string", "type");
$_user_action = (isset($_REQUEST['user_action']) && $_REQUEST['user_action'])?substr(htmlspecialchars($_REQUEST['user_action']), 0, 100):'';

// Зарегистрированным в той же роли пользователям доступ к странице закрыт
if($uid) {
    if (    
              (($_SESSION['role'][0] == 1)&&($requestedRole == "empl"))
           || (($_SESSION['role'][0] == 0)&&($requestedRole != "empl"))
       ) {
             include $_SERVER['DOCUMENT_ROOT']."/403.php";
             exit;
       }
}/**/

// Фрилансер хочет ответить на проект, запоминаем проект, чтобы после рег/авт перекинуть его сразу на него
$from_prj = isset($_GET['from_prj'])?intval($_GET['from_prj']):0;
if ($from_prj) {
    $url_prj = getFriendlyUrl('project', $from_prj);
    $_SESSION['ref_uri'] = $url_prj.'#new_offer';
    $_SESSION['ref_uri2'] = $from_prj;
}

$redirectUriPlain = urldecode($_SESSION['ref_uri']);
if ($hash = __paramInit('string', 'hash', 'hash')) {
    if (in_array($_user_action, array('add_vacancy', 'add_project'))) {
        $kind = $_user_action == 'add_vacancy' ? 4 : 1;
        $redirectUriPlain = '/public/?step=1&kind=' . $kind;
    }
    if (strpos($redirectUriPlain, 'hash=') === false) {
        $redirectUriPlain .= (strpos($redirectUriPlain, '?') === false ? '?' : '&') . 'hash='.$hash;
    }
}
$redirectUri = urlencode($redirectUriPlain);




$action = __paramInit('string', null, 'action');
$registration = new registration();
$registration->listenerAction($action);
$action = $registration->getNextAction();




if ($requestedRole == "empl") {
    $registration->role = registration::ROLE_EMPLOYER;
}

if(__paramInit('string', null, 'action')!='registration' || ($registration->error['captcha'] && __paramInit('string', null, 'action')=='registration') ) {
  unset($_SESSION['reg_captcha_num']);
  $registration->setFieldInfo('captchanum', uniqid('',true));
  $captcha = new captcha($registration->captchanum);
  $captcha->setNumber();
} else {
  $registration->setFieldInfo('captchanum', $_SESSION['reg_captcha_num']);
  $captcha = new captcha($registration->captchanum);
}
unset($_SESSION['login_generated']);

// Пользовательское сообщение (Alert)
$alert_message = '';
if ($_user_action == 'add_project') {
    $alert_message = 'Зарегистрируйтесь или авторизуйтесь как Работодатель, чтобы опубликовать проект.';
    $registration->role = registration::ROLE_EMPLOYER;
} elseif ($_user_action == 'add_vacancy') {
    $alert_message = 'Зарегистрируйтесь или авторизуйтесь как Работодатель, чтобы разместить вакансию.';
    $registration->role = registration::ROLE_EMPLOYER;
} elseif ($_user_action == 'add_contest') {    
    $alert_message = 'Зарегистрируйтесь или авторизуйтесь как Работодатель, чтобы устроить конкурс.';
    $registration->role = registration::ROLE_EMPLOYER;
} elseif ($_user_action == 'add_order') {
    $alert_message = 'Зарегистрируйтесь или авторизуйтесь как Работодатель, чтобы предложить заказ.';
    $registration->role = registration::ROLE_EMPLOYER;
} elseif (intval($from_prj)) {
    $alert_message = 'Зарегистрируйтесь или авторизуйтесь как Фрилансер, чтобы ответить на проект.';    
} elseif($_user_action == 'toppayed') {
  $alert_message = "Зарегистрируйтесь или авторизуйтесь как Фрилансер, чтобы разместить объявление в Карусели.";
} elseif($_user_action == 'masssending') {
  $alert_message = 'Зарегистрируйтесь или авторизуйтесь, чтобы отправить рассылку по каталогу фрилансеров.';
  $registration->role = registration::ROLE_EMPLOYER;
} elseif($_user_action == 'tu') {
  $alert_message = 'Зарегистрируйтесь или авторизуйтесь как Работодатель, чтобы заказать услугу.';  
}

//Сохраняем параметры редиректа для формы 2ого 
//этапа авторизация если такой случится
$_SESSION['2fa_redirect'] = array(
    '_user_action' => $_user_action,
    'redirectUri' => $redirectUri
);


$header = "../header.php";
$footer = "../footer.html";

$full_content = "tpl.registrate.php";

$css_file = array('/css/block/b-captcha/b-captcha.css');
$js_file = array(
    '/scripts/wizard/wizard.js',
    '/css/block/b-eye/b-eye.js',
    
    //@todo: если элемент выбора роли будет перенесен в Zend From 
    //то нужно эти JS перенести в класс элемента RadioBox
    'ElementsFactory' => 'form/ElementsFactory.js',
    'ElementRadioBox' => 'form/RadioBox.js'
);




if (!strpos($_SERVER['HTTP_REFERER'],'/welcome/customer/4/') && 
    !strpos($_SERVER['HTTP_REFERER'],'/welcome/freelancer/2/') && 
    !strpos($_SERVER['HTTP_REFERER'],'/registration/')) {
    unset($_SESSION['from_welcome_wizard']);
}

$from_welcome_wizard = isset($_SESSION['from_welcome_wizard'])?$_SESSION['from_welcome_wizard']:false;

$customer_wizard = isset($_SESSION['customer_wizard']) && $requestedRole == 'empl';


include ("../template3.php");