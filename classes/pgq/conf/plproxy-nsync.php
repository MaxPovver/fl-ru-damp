<?php

require_once dirname(__FILE__) . '/../../config.php';

$db_conf = $GLOBALS['pg_db'];
define("PGQ_DB_CONN", "host=".$db_conf['plproxy']['host']." port=".$db_conf['plproxy']['port']." dbname=".$db_conf['plproxy']['name']." user=".$db_conf['plproxy']['user']." password=".$db_conf['plproxy']['pwd']);
define('SYSDAEMON', 'SystemDaemon');

require_once ABS_PATH . '/classes/pgq/api/PGQConsumer.php';

$Config["LOGLEVEL"] = NOTICE;
$Config["LOGFILE"] = ABS_PATH . '/classes/pgq/logs/plproxy-nsync.pgq';
$Config["DELAY"] = 5;


?>