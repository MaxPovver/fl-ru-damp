<?php

require_once dirname(__FILE__) . '/../../stdf.php';
$db_conf = $GLOBALS['pg_db'];

define('SYSDAEMON', (stripos($_SERVER['OS'], 'WINDOWS')!==false ? 'Win' : '').'SystemDaemon');
define('DEBUG_DAEMON', 0);
define("PGQ_DB_CONN", "host=".$db_conf['spam']['host']." port=".$db_conf['spam']['port']." dbname=".$db_conf['spam']['name']." user=".$db_conf['spam']['user']." password=".$db_conf['spam']['pwd']);

require_once ABS_PATH . '/classes/pgq/api/PGQConsumer.php';
require_once ABS_PATH . '/classes/smtp.php';
require_once ABS_PATH . '/classes/smtp2.php';

$config["LOGLEVEL"] = NOTICE;
$config["LOGFILE"]  = ABS_PATH . '/classes/pgq/logs/spam.pgq';
$config["DELAY"]    = 5;
