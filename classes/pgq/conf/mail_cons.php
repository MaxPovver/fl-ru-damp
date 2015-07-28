<?php


require_once( dirname(__FILE__) . "/../../config/pgq_php_config.php");
require_once( ABS_PATH."/classes/pgq/api/PGQConsumer.php" );
$Config["LOGLEVEL"] = NOTICE;
$Config["LOGFILE"] = ABS_PATH."/classes/pgq/logs/mail_log.pgq";


$Config["DELAY"] = 5;
/**
 * Время, в секундах, между последней обработкой какого-нибудь события в pgq и его запуском
 * через которое вся очередь будет создана заново.
 *
 * @var integer
 */
$Config["RESTART_EVENTS_INTERVAL"] = 1800;
/**
 * Количество событий при превышении которого при запуске pgq
 * вся очередь будет создана заново
 *
 * @var integer
 */
$Config["RESTART_EVENTS_COUNT"] = 50;

?>