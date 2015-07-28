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


$config["LOGLEVEL"] = NOTICE;
$config["LOGFILE"]  = ABS_PATH . '/classes/pgq/logs/docgen.pgq';
$config["DELAY"]    = 5;


class PGQDaemonDocgen extends PGQConsumer 
{
    private $_mainDb = NULL;
        
    public function config() 
    {
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

        
        switch ($event->type) 
        {
            case 'generateById': {
                
                
                
                break;
            }
            
        }
        
        
        
        
        $this->log->notice(print_r($event->data,true));
       
        
        return PGQ_EVENT_OK;
    }
}

new PGQDaemonDocgen("docgen", "docgen", $argc, $argv, PGQ_DB_CONN);