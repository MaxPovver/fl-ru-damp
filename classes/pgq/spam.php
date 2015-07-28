<?php

/**
 * Сколько дисконектов подряд ждать от почтового сервера, чтобы остановить консьюмер 
 */
define('NO_CONNECT_STOP', 10);
/**
 * Как часто запускать очистку очереди ( mail.clear() )
 */
define('CLEAR_INTERVAL',  86400);
/**
 * Время хранение таблиц при их неактивности
 */
define('STORE_INTERVAL',  259200);
/**
 * Интервал через который проверяется очередь сообщений
 */
define('CHECK_INTERVAL',  20);
/**
 * Интервал через который проверяется состояние очереди почтового сервера 
 */
define('MAILQ_INTERVAL',  300);
/**
 * Количество писем в очереди почтового сервера при привышении которой отключаются
 * рассылки с приоритетом > 1 (массовая рассылка)
 */
define('MAILQ_MASS_STOP', 300);
/**
 * Сколько писем отсылать за один вызов proccess_event 
 */
define('EMAILS_AMOUNT',   2000);
/**
 * Нагрузка процессора за последнюю минуту при привышении которой отключаются
 * рассылки с приоритетом > 1 (массовая рассылка)
 */
define('LOAD_AVG_S1',     5);
/**
 * Нагрузка процессора за последнюю минуту при привышении которой отключаются
 * любая рассылка
 */
define('LOAD_AVG_S2',     40);
/**
 * Количество сообщений в одной таблице очереди после которого создается новая таблица
 */
define('EMAILS_QUEUE_LIMIT',     1000000);
/**
 * Константа для classes/stdf.php 
 */
define('IS_PGQ',          1);

require_once dirname(__FILE__) . '/conf/spam.php';


class PGQDeamonSpam extends PGQConsumer {
	/**
     * Ресурс подключения к smtp серверу
     * 
     * @var resource
     */
    protected $smtp;
	/**
     * Ресурс подключения к базе данных
     * 
     * @var resource
     */
    protected $DB;
    /**
     * Количество секунд прошедщих с последнего вызова mail.next_batch()
     * 
     * @var integer 
     */
    protected $sleepTime  = 0;
    /**
     * Количество секунд прошедщих с последнего вызова mail.clear()
     * 
     * @var integer 
     */
    protected $clearTime  = 0;
    /**
     * Количество секунд прошедщих с последнего подсчета количества писем в очереди
     * почтового сервера
     * 
     * @var integer 
     */
    protected $mailqTime  = 0;
    /**
     * Количество писем в очереди почтового сервера с прошедшей проверки
     * 
     * @var integer 
     */
    protected $mailqCount = 0;
    /**
     * Количество дисконектов почтового сервера
     * 
     * @var integer 
     */
    protected $noConnect  = 0;
    /**
     * Нагрузка на процессора за последнюю минуту
     * 
     * @var float
     */
    protected $load       = 0;
	
	
    public function config() {
		global $config;
		if($this->log !== null) {
			$this->log->notice("Reloading configuration (HUP)");
		}
		$this->loglevel  = $config["LOGLEVEL"];
		$this->logfile   = $config["LOGFILE"];
		$this->delay     = $config["DELAY"];
		$this->clearTime = time();
        $this->smtp      = new smtp;
        //$this->smtp2     = new smtp2;
        $this->DB        = new DB('spam');

        $this->DB->query("SELECT mail.val('max_queue_emails', ?)", EMAILS_QUEUE_LIMIT);
        $this->DB->query("SELECT mail.val('max_table_time', ?)", STORE_INTERVAL);
	}
	
    
    public function process() {
        if ( time() - $this->sleepTime > CHECK_INTERVAL ) {
            $this->sleepTime = time();
            $this->DB->query("SELECT mail.task('work')");
        }
        parent::process();
    }
    
	
    public function process_event(&$event) {

        // проверяем нагрузку процессора за последнюю минуту:
        // если она больше LOAD_AVG_S1 - выключаем массовую рассылку, только сообщения с приоритетом <= 1
        // если она больше LOAD_AVG_S2 - выключаем рассылку полностью
        // имеет смысл только если рассылка идет с localhost
        $load = sys_getloadavg();
        if ( $this->load >= LOAD_AVG_S1 && $load[0] < LOAD_AVG_S1 ) {
            $this->log->notice("Processor load is normal.");
        }
        $this->load = $load[0];
        if ( $this->load >= LOAD_AVG_S2 ) {
            $this->log->notice("Processor load is very high! Work temporary stopped.");
            sleep(10);
            $this->DB->query("SELECT mail.task(?)", $event->type);
            return PGQ_EVENT_OK;
        }
        if ( $this->load >= LOAD_AVG_S1 ) {
            $this->log->notice("Processor load is high!");
            $masssend = FALSE;
        } else if ( $this->mailqCount < MAILQ_MASS_STOP ) {
            $masssend = TRUE;
        }

        // проверяем количество сообщение в очереди с интервалом MAILQ_INTERVAL
        // если сообщений больше чем MAILQ_MASS_STOP, то массовая рассылка отключает,
        // рассылаются сообщения только с приоритетом <= 1
        // имеет смысл только если рассылка идет с localhost
        if ( time() - $this->mailqTime > MAILQ_INTERVAL ) {
            $this->mailqTime = time();
            $mailqCount = 0;
            if ( preg_match('/([0-9]+)/', exec('find /var/spool/postfix/active -type f | wc -l'), $o) ) {
                $mailqCount = $o[1];
            }
            if ( $mailqCount >= MAILQ_MASS_STOP ) {
                $this->log->notice("Postfix queue is too big ($mailqCount mails).");
                $masssend = FALSE;
            } else if ( $this->mailqCount >= MAILQ_MASS_STOP && $mailqCount < MAILQ_MASS_STOP ) {
                $this->log->notice("Postfix queue is normal ($mailqCount mails).");
                if ( $this->load < LOAD_AVG_S1 ) {
                    $masssend = TRUE;
                }
            }
            $this->mailqCount = $mailqCount;
        }
        
        switch ( $event->type ) {

            case 'work': {
                
                $successfully = 0;
                $processed    = 0;
                //$queues       = array();

                if ( !$this->smtp->Connect() ) {
                    $this->log->notice("Error: Doesn't connect to SMTP server");
                    if ( $this->noConnect > NO_CONNECT_STOP ) {
                        $this->log->notice("Error: Too many disconnects from STMP server. Consumer stopped.");
                        $this->stop();
                        return PGQ_EVENT_FAILED;
                    }
                    $this->noConnect++;
                    sleep(5);
                    $this->DB->query("SELECT mail.task('work')");
                    return PGQ_EVENT_OK;
                }
                $this->noConnect = 0;
                
                $res = $this->DB->query("SELECT * FROM mail.next_batch(?i, ?i)", EMAILS_AMOUNT, ($masssend? NULL: 1));
                
                while ( $row = pg_fetch_assoc($res) ) {
                    ///// Тестовая хрень smtp2.php (jb) ////////////////////////////////////////////////
                    if ( $row['mime'] == 'SMTP2' ) {
                        //$this->log->notice("SMTP2");
                        $smtp2 = new smtp2;
                        $s = array();
                        $r = array();
                        if ( empty($row['extra']) ) {
                            $exts = array();
                        } else {
                            $exts = preg_split('/(?<!&)&(?!&)/', $row['extra']);
                        }
                        $exts[] = 'recipient='.$smtp2->encodeEmail($row['recipient']);
                        for ( $i=0; $i<count($exts); $i++ ) {
                            $ext = explode('=', $exts[$i], 2);
                            if ( count($ext) == 2 ) {
                                $s[] = "/\%\%\%{$ext[0]}\%\%\%/i";
                                $r[] = str_replace('&&', '&', $ext[1]);
                            }
                        }
                        $message = preg_replace($s, $r, $row['message']);
                        $success = $smtp2->mail($row['sender'], $row['recipient'], $message);
                        //$this->DB->query("SELECT mail.val(?, ?)", "mailid:{$row['priority']}", $row['id']);
                        //$successfully = $successfully + (int) $success;
                        //$processed++;
                        //$smtp2->close();
                        unset($smtp2);
                    } else {
                    ///////////////////////////////////////////////////////////////////////////////////
                    if ( $row['sender'] ) {
                        $this->smtp->from = $row['sender'];
                    }
                    $this->smtp->recipient = $row['recipient'];
                    $this->smtp->subject   = $row['subject'];
                    if ( $row['extra'] ) {
                        $s = array();
                        $r = array();
                        $exts = preg_split('/(?<!&)&(?!&)/', $row['extra']);
                        for ( $i=0; $i<count($exts); $i++ ) {
                            $ext = explode('=', $exts[$i], 2);
                            if ( count($ext) == 2 ) {
                                $s[] = "/%{$ext[0]}%/i";
                                $r[] = str_replace('&&', '&', $ext[1]);
                            }
                        }
                        $this->smtp->message = preg_replace($s, $r, $row['message']);
                    } else {
                        $this->smtp->message = $row['message'];
                    }
                    $files = array();
                    if ( !empty($row['files']) && $row['files'] != '{}' ) {
                        $row['files'] = $this->DB->array_to_php($row['files']);
                        if ( $row['files'] ) {
                            foreach ( $row['files'] as $file ) {
                                $files[] = new CFile($file);
                            }
                            $files = $this->smtp->CreateAttach($files);
                        }
                    }
                    $success = $this->smtp->SmtpMail($row['mime'], $files);
                    
                    ////////////////////////////////////////////////                
                    }
                    ////////////////////////////////////////////////
                    
                    //$this->DB->query("UPDATE mail.recipients SET success = ?b, sended = CURRENT_TIMESTAMP WHERE id = ?i", $success, $row['id']);
                    $this->DB->query("SELECT mail.val(?, ?)", "mailid:{$row['priority']}", $row['id']);
                    $successfully = $successfully + (int) $success;
                    //$queues[]     = $row['queue_id'];
                    $processed++;
                }
                
                $this->smtp->Close();
                
                
                /*if ( $queues ) {
                    $this->DB->query("UPDATE mail.queue SET last_batch = CURRENT_TIMESTAMP WHERE id IN (?l)", $queues);
                }*/
                
                if ( $processed >= EMAILS_AMOUNT ) {
                    $this->DB->query("SELECT mail.task('work')");
                } else {
                    if ( $this->clearTime + CLEAR_INTERVAL < time() ) {
                        $this->clearTime = time();
                        $this->DB->query("SELECT mail.task('clear')");
                    }
                }

                $this->log->notice("{$processed} email(s) sended. {$successfully} success.");                
                
                $this->sleepTime = time();
                
                break;
            }

            case 'clear': {

                if ( $this->load < LOAD_AVG_S1 ) {
                    $c = $this->DB->val("SELECT mail.clear()");
                    $this->log->notice("Queue cleared success.");
                }
                break;
                
            }
            
        }
        
        return PGQ_EVENT_OK;
		
    }
	
}


new PGQDeamonSpam('spam', 'spam', $argc, $argv, PGQ_DB_CONN);
