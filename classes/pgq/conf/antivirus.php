<?php

require_once dirname(__FILE__) . '/../../stdf.php';
$db_conf = $GLOBALS['pg_db'];

define('SYSDAEMON', (stripos($_SERVER['OS'], 'WINDOWS')!==false ? 'Win' : '').'SystemDaemon');
define('DEBUG_DAEMON', 0);
define("PGQ_DB_CONN", "host=".$db_conf['master']['host']." port=".$db_conf['master']['port']." dbname=".$db_conf['master']['name']." user=".$db_conf['master']['user']." password=".$db_conf['master']['pwd']);

require_once ABS_PATH . '/classes/pgq/api/PGQConsumer.php';
require_once ABS_PATH . '/classes/CFile.php';

$config["LOGLEVEL"] = NOTICE;
$config["LOGFILE"]  = ABS_PATH . '/classes/pgq/logs/antivirus.pgq';
$config["DELAY"]    = 5;