<?php


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/controllers/TServiceOrderController.php');

session_start();

$stretch_page = true;
$showMainDiv  = true;

// Формируем JS внизу страницы
define('JS_BOTTOM', true);

$css_file = array('/css/nav.css', '/css/block/b-tabs/b-tabs.css');
$js_file[] = 'mootools-form-validator.js';
$js_file[] = 'tservices/tservices_order.js';
$js_file[] = 'mAttach.js';
$js_file['tservices_order_feedback'] = 'tservices/tservices_order_feedback.js';

$content = "tpl.order.php";
$header = "../header.php";
$footer = "../footer.html";



$module = new CModule('tu');
$module->setBasePath(dirname(__FILE__));
$controller = new TServiceOrderController('t-service-order', $module);
$controller->init(); // инициализация контролера
$controller->run(__paramInit('string', 'action', 'action', 'index'));

// отрисовка страницы
include ("../template3.php");
