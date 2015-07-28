<?php

//ќтключаем запуск основного приложени€
//и инклудим библиотеки сами ниже 
//чтобы создать минимальное окружение
define('IN_STDF', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/config.php');                                                                                                                                                                              
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/globals.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/memBuff' . (defined('USE_MEMCACHED') ? 2 : 1) . '.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');   
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DB.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yii/tinyyii.php');
require_once(__DIR__ . '/controllers/DownloadController.php');

session_start();

//ѕока такой хак чтобы отдельно 
//конфиг не делать дл€ миниокружени€
//так в DAV там хост другой
if (is_release()) {
    $host = HTTP_PREFIX . 'www.fl.ru';
} elseif (is_beta()) {
    $host = HTTP_PREFIX . 'beta.fl.ru';
} elseif (!is_local()) {
    $host = HTTP_PREFIX . 'alpha.fl.ru';
}

$path = __paramInit('string', 'path', 'path');

$module = new CModule('download');
$module->setBasePath(dirname(__FILE__));
$controller = new DownloadController('download', $module);
$controller->init($path); // инициализаци€ контролера
$controller->run('index'); // запуск обработчика