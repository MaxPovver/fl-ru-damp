<?php

/**
 * Консьюмер для возврата средств через ЯД MWS API
 */

ini_set('display_errors',1);
error_reporting(1);

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
require_once(ABS_PATH . '/classes/billing/BillPayback.php');


$config["LOGLEVEL"] = NOTICE;
//@todo: есть ротация логов см ниже
//$config["LOGFILE"]  = ABS_PATH . '/classes/pgq/logs/payback.pgq';
$config["DELAY"]    = 5;


class PGQDaemonPayback extends PGQConsumer 
{
    const LOG_PATH   = '/classes/pgq/logs/payback/';
    const LOG_FORMAT = "[%s] id = %d. Message: %s";

    protected $logMonth = NULL;
    
    private $_mainDb = NULL;
        
    public function config() 
    {
        global $config;
        
        if ($this->log !== null) {
            $this->log->notice("Reloading configuration (HUP)");
        }
        
        $this->loglevel = $config["LOGLEVEL"];
        $this->delay    = $config["DELAY"];
        
        //первый запуск ротации
        $this->logRotate();
        //$this->logfile  = $config["LOGFILE"];
    }

    //--------------------------------------------------------------------------
    
    /**
     * Метод ротации логов по месяцам
     */
    public function logRotate()
    {
        $month = date('m');
        
        if(!$this->logMonth || $this->logMonth != $month) {
            
            $this->logfile = ABS_PATH . self::LOG_PATH . sprintf("{$this->cname}-%s%s.pgq", $month, date('Y'));
            
            if($this->logMonth) {
                $this->log->logfile = $this->logfile;
                $this->log->reopen();
            }
            
            $this->logMonth = $month;
        }
    }    
    
    
    //--------------------------------------------------------------------------
    

    public function process() 
    {
        //обязательный вызов для ротации логов
        $this->logRotate();
        
        parent::process();
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
        $id = intval(@$event->data['id']);
        
        try
        {
            $is_repeat = !BillPayback::getInstance()->doPayback($id);
            if(!$is_repeat) $message = 'Средства возвращены.';
        }
        catch(BillPaybackException $e)
        {
            $message = 'Ошибка: ' . $e->getMessage();
            $is_repeat = $e->isRepeat();
        }
        
        if($message)
        {
            $this->log->notice(iconv('CP1251', 'UTF-8', sprintf(
                    self::LOG_FORMAT, 
                    $event->type, 
                    $id, 
                    $message)));
        }
        
        if($is_repeat)
        {
            $DB->query("SELECT pgq.insert_event('payback', ?, ?)", 
                    $event->type, 
                    http_build_query($event->data));
        }
        
        return PGQ_EVENT_OK;
    }
}

new PGQDaemonPayback("payback", "payback", $argc, $argv, PGQ_DB_CONN);