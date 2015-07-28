<?php
/**
 * —траница каталога типовых услуг дл€ главной страницы
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' ); //???
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoTags.php");

$g_page_id = "0|992";
$rpath="../";

// begin настройки layout

$grey_tservice = true; // включает в верхнем меню "b-menu_head" пункт "типовые услуги" (@see ../header.new.php)
$stretch_page = true;
$showMainDiv  = true;

// ‘ормируем JS внизу страницы
define('JS_BOTTOM', true);
//$js_file[] = "/tservices_categories_js.php";
$js_file[] = "tservices/tservices_catalog.js";
//$css_file    = array('portable.css');

$content = $_SERVER['DOCUMENT_ROOT']."/tu/tpl.catalog.php";
$header = "../header.php";
$footer = "../footer.html";

// /end настройки layout

session_start();

// begin логика основной части контента
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/controllers/TServiceCatalogController.php');

$module = new CModule('tu');
$module->setBasePath(dirname(__FILE__));
$controller = new TServiceCatalogController('t-service-catalog', $module);
$controller->init(); // инициализаци€ контролера
$controller->run('index');
// /end логика основной части контента


//ѕоказываем в шапке слайдер платных мест
$main_page = true;

$page_title = SeoTags::getInstance()->getTitle();
$page_descr = SeoTags::getInstance()->getDescription();
$page_keyw = SeoTags::getInstance()->getKeywords();


// отрисовка страницы
include ($_SERVER['DOCUMENT_ROOT']."/template3.php");