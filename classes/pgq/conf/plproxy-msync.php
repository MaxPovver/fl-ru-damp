<?php

require_once dirname(__FILE__) . '/../../config.php';

$db_conf = $GLOBALS['pg_db'];
define("PGQ_DB_CONN", "host=".$db_conf['master']['host']." port=".$db_conf['master']['port']." dbname=".$db_conf['master']['name']." user=".$db_conf['master']['user']." password=".$db_conf['master']['pwd']);
define('SYSDAEMON', (stripos($_SERVER['OS'], 'WINDOWS')!==false ? 'Win' : '').'SystemDaemon');

require_once ABS_PATH . '/classes/pgq/api/PGQConsumer.php';

$Config["LOGLEVEL"] = NOTICE;
$Config["LOGFILE"] = ABS_PATH . '/classes/pgq/logs/plproxy-msync.pgq';
$Config["DELAY"] = 5;

?>