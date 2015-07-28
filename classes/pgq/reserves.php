<?php

ini_set('display_errors',0);
error_reporting(0);

define('IS_PGQ', 1);
require_once __DIR__ . '/../stdf.php';
$db_conf = $GLOBALS['pg_db'];

define('SYSDAEMON', (stripos($_SERVER['OS'], 'WINDOWS')!==false ? 'Win' : '').'SystemDaemon');
define('DEBUG_DAEMON', 0);
define("PGQ_DB_CONN", 
       "host=".$db_conf['master']['host'].
       " port=".$db_conf['master']['port'].
       " dbname=".$db_conf['master']['name'].
       " user=".$db_conf['master']['user'].
       " password=".$db_conf['master']['pwd']);


require_once(ABS_PATH . '/classes/pgq/api/PGQConsumer.php');
require_once(ABS_PATH . '/classes/reserves/ReservesPayback.php');
require_once(ABS_PATH . '/classes/reserves/ReservesPayout.php');


$config["LOGLEVEL"] = NOTICE;
$config["LOGFILE"]  = ABS_PATH . '/classes/pgq/logs/reserves.pgq';
$config["DELAY"]    = 5;


class PGQDaemonReserves extends PGQConsumer 
{
    const LOG_FORMAT = "[%s] Reserve Id = %d. %s";

    private $_mainDb = NULL;
        
    public function config() {
        global $config;
        if($this->log !== null) {
            $this->log->notice("Reloading configuration (HUP)");
        }
        $this->loglevel = $config["LOGLEVEL"];
        $this->logfile  = $config["LOGFILE"];
        $this->delay    = $config["DELAY"];
    }

    //--------------------------------------------------------------------------
    
    protected function force_connect() 
    {
        global $DB;

        if (!$this->_mainDb) {
            $this->_mainDb = $DB->connect(TRUE);
        }

        return $this->_mainDb;
    }

    //--------------------------------------------------------------------------    
    
    public function process_event(&$event) 
    {
        global $DB;
        
        $this->force_connect();

        $is_repeat = false;
        $message = false;
        $reserve_id = intval(@$event->data['reserve_id']);
        
        try
        {
            switch($event->type)
            {
                case 'payback':
                    $is_repeat = !ReservesPayback::getInstance()->doPayback($reserve_id);
                    if(!$is_repeat) $message = 'Средства возвращены.';
                    break;
                      
                case 'payout':
                    $is_repeat = !ReservesPayout::getInstance()->doPayout($reserve_id);
                    if(!$is_repeat) $message = 'Средства выплачены.';
                    break;   
            }
        }
        catch(ReservesPayException $e)
        {
            $message = 'Ошибка: ' . $e->getMessage();
            $is_repeat = $e->isRepeat();
        }
        
        if($message)
        {
            $this->log->notice(iconv('CP1251', 'UTF-8', sprintf(
                    self::LOG_FORMAT, 
                    $event->type, 
                    $reserve_id, 
                    $message)));
        }
        
        if($is_repeat)
        {
            $DB->query("SELECT pgq.insert_event('reserves', ?, ?)", 
                    $event->type, 
                    http_build_query($event->data));
        }
        
        return PGQ_EVENT_OK;
    }
}

$daemon = new PGQDaemonReserves("reserves", "reserves", $argc, $argv, PGQ_DB_CONN);