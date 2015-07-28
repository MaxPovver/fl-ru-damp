<?php

define( 'IS_SITE_ADMIN', 1 );

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once(__DIR__ . '/controllers/ReservesAdminController.php');

session_start();

if (!hasPermissions('sbr') && !hasPermissions('sbr_finance')) {
    header_location_exit('/404.php');
}
 
$css_file = array(
    '/css/block/b-menu/_tabs/b-menu_tabs.css',
    '/css/block/b-label/b-label.css',
    'nav.css',
    'norisk-admin.css');

$js_file[] = 'reserves/reserves_admin.js';

$css_file[] = '/scripts/DatePicker/Source/datepicker.css';
$js_file[] = 'Locale.ru-RU-unicode.Date.js';
$js_file[] = 'DatePicker/Source/Locale.ru-RU.DatePicker.js';
$js_file[] = 'DatePicker/Source/Picker.js';
$js_file[] = 'DatePicker/Source/Picker.Attach.js';
$js_file[] = 'DatePicker/Source/Picker.Date.js';
//$js_file[] = 'DatePicker/Source/Picker.Date.Range.js';

$action = __paramInit('string', 'action', 'action', 'index');

$module = new CModule('reserves-admin');
$module->setBasePath(dirname(__FILE__));
$controller = new ReservesAdminController('reserves-admin', $module);
$controller->init($action); // инициализация контролера
$controller->run($action);

$content = "tpl.index.php";
include ($_SERVER['DOCUMENT_ROOT'] . "/template.php");