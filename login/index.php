<?php

/**
 * Авторизация, точка входа
 */

define('IS_USER_ACTION', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once(ABS_PATH . '/classes/yii/tinyyii.php');
require_once(__DIR__ . '/controllers/LoginController.php');


$module = new CModule('login');
$module->setBasePath(dirname(__FILE__));
$controller = new LoginController('login', $module);
$controller->init(); // инициализация контролера
$controller->run('index'); // запуск обработчика

//@todo: Отключает лишнюю обертку в template3
$stretch_page = true;
$registration_page = $registration_folder = true;
$footer_registration = true;
$hide_banner_top = true;

$content = "tpl.index.php";
include (ABS_PATH . '/template3.php');