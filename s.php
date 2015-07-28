<?php
/**
 * Скрипт вызывается при открытии 
 * писем рассылки новых проектов
 * 
 */

//ini_set('display_errors',1);
//error_reporting(E_ALL ^ E_NOTICE);

define('IS_EXTERNAL', 1);

date_default_timezone_set('Europe/Moscow');

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/statistic/StatisticFactory.php");

//Выключаем поддержку старых URI без параметра s - даты рассылки
if(!isset($_GET['s'])) $_GET['s'] = 0;

$hash = (string)$_GET['h'];//хеш параметров
$type = (int)$_GET['t'];//тип 0/1 фрилансер/работодатель
$timestamp = (int)$_GET['s'];//дата рассылки
$label = (string)$_GET['y'];//параметр - год регистрации юзера
//$login = (string)$_GET['l'];//уникальный ID юзера

unset($_GET['h']);
$true_hash = md5(STAT_URL_PREFIX . serialize($_GET));

if ($true_hash === $hash) {
    global $DB;
    $DB->query("SELECT pgq.insert_event('statistic', 'newsletter_projects_open_hit', ?)", 
        http_build_query(array(
            'type' => $type, 
            'label' => $label, 
            'timestamp' => $timestamp, 
            'cid' => $true_hash
        )));
}


// 1x1 transparent GIF
$GIF_DATA = array(
    chr(0x47), chr(0x49), chr(0x46), chr(0x38), chr(0x39), chr(0x61),
    chr(0x01), chr(0x00), chr(0x01), chr(0x00), chr(0x80), chr(0xff),
    chr(0x00), chr(0xff), chr(0xff), chr(0xff), chr(0x00), chr(0x00),
    chr(0x00), chr(0x2c), chr(0x00), chr(0x00), chr(0x00), chr(0x00),
    chr(0x01), chr(0x00), chr(0x01), chr(0x00), chr(0x00), chr(0x02),
    chr(0x02), chr(0x44), chr(0x01), chr(0x00), chr(0x3b)
);


header("Content-Type: image/gif");
header("Cache-Control: private, no-cache, no-cache=Set-Cookie, proxy-revalidate");
header("Pragma: no-cache");
header("Expires: Wed, 17 Sep 1975 21:32:10 GMT");
echo join($GIF_DATA);