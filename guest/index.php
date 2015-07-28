<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yii/tinyyii.php');
require_once(__DIR__ . '/controllers/GuestController.php');

session_start();
 
$js_file[] = 'Guest/Guest.js';

$action = __paramInit('string', 'action', 'action', 'index');

$module = new CModule('guest');
$module->setBasePath(dirname(__FILE__));
$controller = new GuestController('guest', $module);
$controller->init($action); // инициализация контролера
$controller->run($action);

//@todo: Отключает лишнюю обертку в template3
$stretch_page = true;
$hide_banner_top = true;

$content = "tpl.index.php";
include ($_SERVER['DOCUMENT_ROOT'] . '/template3.php');