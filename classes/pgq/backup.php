<?php

/**
 * Консьюмер работает с сервисом Azure Blob
 */

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
require_once(ABS_PATH . '/classes/Backup/Factory.php');


$config["LOGLEVEL"]         = NOTICE;//DEBUG;//NOTICE;//WARNING;//VERBOSE;//NOTICE;
$config["DELAY"]            = 5;
//$config["LOGFILE"]        = ABS_PATH . '/classes/pgq/logs/backup.pgq';


class PGQDaemonBackup extends PGQConsumer 
{
    const LOG_PATH   = '/classes/pgq/logs/';
    
    //private $_mainDb = NULL;
    
    protected $logMonth = NULL;
    
    protected $backupServiceInstance = NULL;
    

    public function config() 
    {
        global $config, $BACKUP_SERVICE;
        
        if($this->log !== null) {
            $this->log->notice("Reloading configuration (HUP)");
        }
        
        $this->loglevel = $config["LOGLEVEL"];
        $this->delay    = $config["DELAY"];
        
        //первый запуск ротации
        $this->logRotate();
        //$this->logfile  = $config["LOGFILE"];
        
        //Настройки сервиса
        if(!isset($BACKUP_SERVICE)) {
            $this->log->error('Not found backup config.');
            $this->stop();
        }

        try
        {
            //создаем указанный обьект для работы с сервисом
            $this->backupServiceInstance = Backup_Factory::getInstance(
                    $BACKUP_SERVICE['type'], 
                    $BACKUP_SERVICE['options']);
        }
        catch(Exception $e)
        {
            $this->log->error($e->getMessage());
        }
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
    
    
    /*
    protected function force_connect() 
    {
        global $DB;

        if (!$this->_mainDb) {
            $this->_mainDb = $DB->connect(TRUE);
        }

        return $this->_mainDb;
    }
    */
    
    
    //--------------------------------------------------------------------------    
    
    
    public function process_event(&$event) 
    {
        try
        {
            $filepath = @$event->data['file'];
            //$this->log->notice($filepath);

            switch ($event->type) 
            {
                case 'create': {
                    $this->backupServiceInstance->create($filepath);
                    break;
                }

                case 'delete': {
                    $this->backupServiceInstance->delete($filepath);
                    break;
                }

                case 'copy': {
                    $toFilepath = @$event->data['to'];
                    $this->backupServiceInstance->copy($filepath, $toFilepath);
                    break;
                }
                
                
                //remove?
                //rename?
            }
        }
        catch(Exception $e)
        {
            $this->log->notice($e->getMessage());
            //Если проблемы со связью то просим повторить
            if(in_array($e->getCode(), array(500,503))) {
                return PGQ_EVENT_RETRY;
            }
        }
        
        return PGQ_EVENT_OK;
    }
}

new PGQDaemonBackup('backup', 'backup', $argc, $argv, PGQ_DB_CONN);