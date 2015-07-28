<?php
require_once( "../config/pgq_php_config.php");
require_once( ABS_PATH."/classes/pgq/api/PGQConsumer.php" );
//$Config["LOGLEVEL"] = DEBUG;
$Config["LOGFILE"] = ABS_PATH."/classes/pgq/logs/banner_log.pgq";
$Config["DELAY"] = 100; //в секундах

?>