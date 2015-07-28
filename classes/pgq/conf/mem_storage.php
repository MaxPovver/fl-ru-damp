<?php

ini_set('display_errors',0);
error_reporting(0);

require_once dirname(__FILE__) . '/../../stdf.php';
//require_once dirname(__FILE__) . '/../../config.php';
$db_conf = $GLOBALS['pg_db'];

define('SYSDAEMON', (stripos($_SERVER['OS'], 'WINDOWS')!==false ? 'Win' : '').'SystemDaemon');
define('DEBUG_DAEMON', 0);

define("PGQ_DB_CONN", 
       "host=".$db_conf['master']['host'].
       " port=".$db_conf['master']['port'].
       " dbname=".$db_conf['master']['name'].
       " user=".$db_conf['master']['user'].
       " password=".$db_conf['master']['pwd']);


require_once ABS_PATH . '/classes/pgq/api/PGQConsumer.php';

require_once ABS_PATH . "/classes/freelancer.php";
require_once ABS_PATH . "/classes/mem_storage.php";



$Config["LOGLEVEL"] = NOTICE;

$Config["LOGFILE"]  = ABS_PATH . '/classes/pgq/logs/mem_storage.pgq';

$Config["DELAY"]    = 5;

/**
 * Время, в секундах, между последней обработкой какого-нибудь события в pgq и его запуском
 * через которое вся очередь будет создана заново.
 *
 * @var integer
 */
//$Config["RESTART_EVENTS_INTERVAL"] = 1800;
/**
 * Количество событий при превышении которого при запуске pgq
 * вся очередь будет создана заново
 *
 * @var integer
 */
//$Config["RESTART_EVENTS_COUNT"] = 50;
