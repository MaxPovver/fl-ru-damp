<?php 

$g_page_id = "0|90";
$rpath = "../../";
$promo = false;
$wizard_page = 1;
header("Location: /404.php"); 
exit;
// сюда можно попасть только по ссылкам на главной иил есть мы уже были в мастере 
if (!isset($_SERVER['HTTP_REFERER']) || !(preg_match('~registration/employer.php|wizard/registration~', $_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']))) {
    if(!isset($_COOKIE['visited_wizard'])) { // Если человек уже был в мастере то не перенаправляем. Если первый раз и идет через ссылку. То перенаправляем на главную
        setcookie('nfastpromo_x', '', time() - 3600, '/');
        header("Location: /#b-promo_clients"); 
        exit;
    }
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard_registration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard_billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_employer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
session_start();
ob_start();

$wizard = new wizard_registration();
$wizard_folder = true;
$role = __paramInit('int', 'role', 'role', false);

if ($role == 2) $page_search_work = 1;

if($role) {    
    $_SESSION['wiz_role'] = $role;
    $wizard->setRole($role);
} else if($wizard->getRole() > 0) {
    $role = $wizard->getRole();
    $_SESSION['wiz_role'] = $role;
} else {
    $role = $_SESSION['wiz_role'];
}

if($role == 2) {
    $wizard->init(wizard_registration::REG_FRL_ID, new step_freelancer());
    $js     = array(
        "/scripts/wizard/wizard_freelancer.js",
        "/scripts/wizard/wizard.js",
        "attachedfiles.js",
        "tawl_bem.js"
    );
    foreach($js as $j) $js_file[] = $j;
    $content = "tpl.freelancer.php";
} else {
    $wizard->init(wizard_registration::REG_EMP_ID, new step_employer());
    $js     = array(
        "/scripts/wizard/wizard_employer.js",
        "/scripts/wizard/wizard.js",
        "attachedfiles.js",
        "tawl_bem.js"
        //"/scripts/b-combo/b-combo-dynamic-input.js",
        //"/scripts/b-combo/b-combo-multidropdown.js",
        //"/scripts/b-combo/b-combo-autocomplete.js",
        //"/scripts/b-combo/b-combo-calendar.js",
        //"/scripts/b-combo/b-combo-manager.js"
    );
    foreach($js as $j) $js_file[] = $j;
    
    $content = "tpl.employer.php";
}

$action = __paramInit('string', 'action', 'action', false);
    
switch($action) {
    // обрабатываем случай когда будущий фрилансер сразу открыл несколько проектов в отдельных вкладках и отвечает на них
    case "create_offer":
        if ($role == 2 && $wizard->getPosition() !== 1) {
            $wizard->setNextStep(1);
        }
        break;
    // Выход из мастера
    case "clear":
        $wizard->steps[$wizard->getPosition()]->clearSessionStep();
        header("Location: /wizard/registration/");
        exit;
        break;
    case "exit":
        $wizard->exitWizard();
        exit;
        break;
    case "next":
        $complited = __paramInit('int', 'complited', 'complited', 0);
        $wizard->setCompliteStep($complited > 0 ? true : false);
        $pos = $wizard->getPosition() + 1;
        do {
            $wizard->setNextStep($pos);
            // Если больше, то какой то непорядок явно, на всякий случай перекидываем к последнему активному шагу
            if($pos > count($wizard->steps) || $wizard->isStep($pos)) {
                $pos = $wizard->getPosition(); 
                break; 
            }
            $pos++;
        } while($wizard->isStep($pos));

        header("Location: /wizard/registration/"); 
        exit;
    default:
        break;
}

if(!empty($wizard)) {
    if(!$wizard->isAccess()) {
        include $rpath."403.php";
        exit;
    }
    $step = __paramInit('int', 'step', 'step', false);
    
    if($step) { 
        $wizard->setNextStep($step);
    }
}

$js_file[] = "/css/block/b-eye/b-eye.js";
$js_file[] = "/scripts/b-combo/b-combo-phonecodes.js";

$header = $rpath . "header.php";
$footer = $rpath . "footer.html";
ob_start();
include ($rpath . "template2.php");

?>