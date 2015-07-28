<?php

/**
 * Мастер регистрации
 */

define('IS_WELCOME_WIZARD', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yii/tinyyii.php');
require_once(__DIR__ . '/controllers/FreelancerController.php');
require_once(__DIR__ . '/controllers/CustomerController.php');

//$js_file[] = '';

$action = __paramInit('string_no_slashes', 'action', 'action', 'index');
$controller_name = __paramInit('string_no_slashes', 'controller', 'controller', 'freelancer');
$controller_name = in_array($controller_name, array('freelancer','customer'))?$controller_name:'freelancer';

$module = new CModule('welcome');
$module->setBasePath(dirname(__FILE__));

$class_name = ucfirst($controller_name) . "Controller";
if (class_exists($class_name)) {
   $controller = new $class_name($controller_name, $module);
   $controller->init($action); // инициализация контролера
   $controller->run($action); 
} else {
   header("Location: /404.php"); 
   exit;
}

//@todo: Отключает лишнюю обертку в template3
$stretch_page = true;
$registration_page = $registration_folder = true;
$footer_registration = true;
$hide_banner_top = true;

$content = "tpl.index.php";
include (ABS_PATH . '/template3.php');