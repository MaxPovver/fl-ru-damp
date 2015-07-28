<?php

/**
 * Консьюмер для отправки статистики
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
require_once(ABS_PATH . '/classes/statistic/StatisticFactory.php');


$config["LOGLEVEL"] = NOTICE;
$config["DELAY"]    = 5;


class PGQDaemonStatistic extends PGQConsumer 
{
    const LOG_PATH = '/classes/pgq/logs/statistic/';
    const LOG_FORMAT = "[%s] data = {%s}. Message: %s";

    protected $logMonth = NULL;
    
    public function config() 
    {
        global $config;
        
        if ($this->log !== null) {
            $this->log->notice("Reloading configuration (HUP)");
        }
        
        $this->loglevel = $config["LOGLEVEL"];
        $this->delay = $config["DELAY"];
        
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
        
        if (!$this->logMonth || $this->logMonth != $month) {
            
            $this->logfile = ABS_PATH . self::LOG_PATH . sprintf("{$this->cname}-%s%s.pgq", $month, date('Y'));
            
            if ($this->logMonth) {
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
    
    public function process_event(&$event) 
    {
        $message = false;
        
        $cid = (string) (@$event->data['cid']);
        
        try {
           
            $options = array(
                'cid' => $cid,
                'sc' => 'start',
                'cd1' => $cid 
            );
           
            if (isset($event->data['uid']) && 
                $event->data['uid'] > 0) {
            
                $options['cd5'] = $event->data['uid'];
            }
            unset($event->data['uid']);
            
            
            $ga = StatisticFactory::getInstance('GA', $options);
            

            switch ($event->type) {
                
                case 'service_payed':
                    $is_emp = (boolean) (@$event->data['is_emp']);
                    $label = (string) @$event->data['label'];
                    $ammount = floatval(@$event->data['ammount']);
                    
                    $ga->serviceWasPayed($is_emp, $label, $ammount, $cid);
                    
                    break;
                
                case 'project_answer':
                    $project_kind_ident = (string) (@$event->data['project_kind_ident']);
                    $offer_count = (int) (@$event->data['offer_count']);
                    $is_pro = (boolean) (@$event->data['is_pro']);
                    $offer_id = @$event->data['offer_id'];
                    
                    $ga->projectAnwer($cid, $project_kind_ident, $offer_count, $is_pro);
                    
                    break;
                
                case 'newsletter_projects_open_hit':
                    $type = (int) (@$event->data['type']);
                    $label = (string) (@$event->data['label']);
                    $timestamp = (int) (@$event->data['timestamp']);
                    
                    if ($type == 1) {
                       $ga->newsletterNewProjectsOpenHitEmp($label, $timestamp);
                    } else {
                       $ga->newsletterNewProjectsOpenHitFrl($label, $timestamp);
                    }
                    
                    break;
                
                //Обрабатываем методы которые поддерживает $ga инстанс
                //Обычно это типовые методы типа GA::event
                default:
                    unset($event->data['cid']);
                    
                    $ga->call($event->type, $event->data);
                    
                    break;
            }
            
            
            //Запись событий в лог
            if (is_object($ga) && method_exists($ga, 'getLastRequest')) {
                require_once(ABS_PATH . "/classes/log.php");                                                                                                                                                                                                                                  
                $log = new log('statistic/'.SERVER.'-%d%m%Y.log');
                
                
                $suffix = '';
                if (isset($offer_id) && !empty($offer_id)) {
                    $suffix = " offer_id={$offer_id}";
                }
                
                $log->writeln(date('d.m.Y H:i:s') . ' - ' . $ga->getLastRequest()->getBaseUrlWithQuery() . $suffix);
            }
            
        } catch (Exception $e) {
            $message = 'Ошибка: ' . $e->getMessage();
        }
        
        if ($message) {
            $data = '';
            foreach ($event->data as $key => $value) {
                $data .= $key . '=' . $value . '; ';
            }
            
            $log_message = sprintf(self::LOG_FORMAT, $event->type, $data, $message);
            $this->log->notice(iconv('CP1251', 'UTF-8', $log_message));
            
            //Повторить через 60 сек
            //$event->retry_delay = 60;
            //return PGQ_EVENT_RETRY;
        }
        
        return PGQ_EVENT_OK;
    }
}

new PGQDaemonStatistic("statistic", "statistic", $argc, $argv, PGQ_DB_CONN);