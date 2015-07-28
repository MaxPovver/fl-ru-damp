<?php

/*
 * Генерация архива докуметвом по БС
 * Запускать каждые 1-2 минуты
 * 
 * https://beta.free-lance.ru/mantis/view.php?id=28916
 */

//ini_set('display_errors',1);
//error_reporting(E_ALL ^ E_NOTICE);

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');


require_once(__DIR__ . "/../classes/config.php");
require_once(__DIR__ . "/../siteadmin/reserves/models/ReservesArchiveModel.php");

//------------------------------------------------------------------------------

ReservesArchiveModel::model()->generateArchive();
exit;