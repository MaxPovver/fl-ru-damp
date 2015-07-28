<?php

define('IS_PGQ',         1);
define('IS_EXTERNAL',    1);
define('DEBUG_DAEMON',   0);


require_once "conf/masssend.php";
require_once ABS_PATH . "/classes/stdf.php";
require_once ABS_PATH . "/classes/messages.php";
require_once ABS_PATH . "/classes/spam.php";
require_once ABS_PATH . "/classes/pmail.php";


class PGQDaemonMasssend extends PGQConsumer {

    const MASSSEND_POST_QUEUE_SIZE = 2000;
    const MASSSEND_BIND_CACHE_KEY  = 'masssend_binds_data';
    
    /**
     * Дополнительная информация для log файла
     * 
     * @var string
     */
    protected $_logExInfo = '';
    /**
     * Массив хранящий связи между рассылкой личных сообщений и рассылкой почты
     * @see self::bind()
     * 
     * @var array
     */
    protected $_binds     = array();
    

    public function config() {
        global $Config;

        if( $this->log !== null ) {
            $this->log->notice("Reloading configuration (HUP) from '%s'", $Config["LOGLEVEL"]);
        }
        
        $this->loglevel = $Config["LOGLEVEL"];
        $this->logfile  = $Config["LOGFILE"];
        $this->delay    = $Config["DELAY"];

        $this->_dbProxy  = new DB('plproxy');
        $this->_dbMaster = new DB('master');
    }
    
    
    /**
     * Сохраняет или возвращает данные связывающие рассылку в личку с рассылкой в почту. Данные актуальны с 
     * момента начала рассылки и до ее окончания. Данные хранятся в переменной (т.к. скрипт постоянно запущен) и
     * дублируются в memcahce на случай отключения скрипта во время рассылки. Если во время рассылки в личку 
     * остановить скрипт и перегрузить memcahce, то данные данные потеряются и рассылка в почту не пойдет.
     * Если сделать то же во время рассылки на почту, есть вероятность отправить части пользователей повторные
     * письма.
     * 
     * @param  integer $messId  id личного сообщения
     * @param  array   $data    массив с данными для сохранения или NULL если данные нужно получить
     * @return array            если $data == NULL, возвращает массив данных для $messId
     */
    public function bind($messId, $data = NULL) {
        $cache = new memBuff;
        $idx   = -1;
        if ( empty($this->_binds) ) {
            $this->_binds = $cache->get(self::MASSSEND_BIND_CACHE_KEY);
            if ( !is_array($this->_binds) ) {
                $this->_binds = array();
            }
        }
        if ( count($this->_binds) >= 100 ) {
            array_shift($this->_binds);
        }
        for ( $i=count($this->_binds)-1; $i>=0; $i-- ) {
            if ( $this->_binds[$i]['__messId'] == $messId ) {
                $idx = $i;
                break;
            }
        }
        if ( is_null($data) ) {
            return ($idx >= 0)? $this->_binds[$idx]: array();
        } else {
            $data['__messId'] = $messId;
            if ( $idx >= 0 ) {
                $this->_binds[$idx] = $data;
            } else {
                $this->_binds[] = $data;
            }
            $cache->set(self::MASSSEND_BIND_CACHE_KEY, $this->_binds, 3600 * 48);
        }
    }
    

    public function process_event(&$event) {
        $r    = FALSE;
        $time = microtime(TRUE);
        $this->_logExInfo = '';

        switch ($event->type) {
        
            // ----------------------------------------------------------------------------------------------------
            // -- подготовка сообщения к массовой рассылке                                                        -
            // ----------------------------------------------------------------------------------------------------
            case 'masssend': {
                $res = $this->_dbProxy->val(
                        "SELECT masssend(?, ?, ?, ?, ?, ?)", 
                        (int) $event->data['shardkey'], 
                        $event->data['message_id'], 
                        $event->data['curtime'], 
                        $event->data['from_id'], 
                        $event->extra1, 
                        $event->data['attachments']
                );
                
                // создаем автоматическую папку для массовой рассылки личного менеджера
                if ( 
                    in_array( $event->data['from_id'], $aPmUserUids ) 
                    || SERVER === 'local' || SERVER === 'beta' || SERVER === 'alpha' 
                ) {
                    $nStamp = strtotime( $event->data['posted_time'] );
                    $sName  = date( 'd.m.Y H:i:s', $nStamp );
                    $sYear  = date( 'Y', $nStamp );

                    $nFolderId = $this->_dbProxy->val( 
                        'SELECT mess_pm_folder_add(?i, ?, ?i, ?i, ?i)', 
                        $event->data['from_id'], $sName, $sYear, $event->data['message_id'], $event->data['masssend_id'] 
                    );
                }
                
                if ( $res && $event->data['mail_func'] ) {
                    
                    /*
                    $senderNode = $this->_dbProxy->val(
                        "SELECT plproxy.eq_real_nodes('usercluster', ?i, ?i)", 
                        (int) $event->data['shardkey'],
                        $event->data['from_id']
                    );
                     */
                    //if ( $senderNode == 't' ) {
                        $mail = new pmail;
                        // Тут не реальная отправка, а инициализация новой очереди, необходимых таблиц 
                        // и переменных в базе spam для последующей отправки по email. См. mail.send() с пустым in_recipient.
                        $mailId = call_user_func(array($mail, $event->data['mail_func']), $event->data['message_id'], 0, NULL);
                        $this->bind($event->data['message_id'], array('mailId' => $mailId, 'mailFunc' => $event->data['mail_func']));
                    //}
                }
                $r = !$this->_dbProxy->error;
                $this->_logExInfo = ($event->data['mail_func']? 'with mail': 'without mail');
                break;
            }

            
            // ----------------------------------------------------------------------------------------------------
            // -- заполняет очередь списком получателей                                                           -
            // ----------------------------------------------------------------------------------------------------
            case 'masssend_bind': {
                $res = $this->_dbProxy->val(
                    "SELECT masssend_bind(?, ?, ?, ?)", 
                    (int) $event->data['shardkey'], 
                    $event->data['message_id'], 
                    $event->data['from_id'], 
                    $event->extra1
                );
                $r = !$this->_dbProxy->error;
                break;
            }
            
            // ----------------------------------------------------------------------------------------------------
            // -- рассылает адресатам из очереди сообщение в личные сообщения и запускает рассылку в почу,        -
            // -- если она была указана в функции masssend                                                        -
            // ----------------------------------------------------------------------------------------------------
            case 'masssend_commit': {
                $this->_dbProxy->val(
                    "SELECT masssend_commit(?, ?, ?)", 
                    (int) $event->data['shardkey'], 
                    $event->data['message_id'],
                    $event->data['from_id']
                );
                if ( !$this->_dbProxy->error ) {
                    // $mem = new memBuff();
                    // $mem->flushGroup('msgsCnt');
                    /*
                    $senderNode = $this->_dbProxy->val(
                        "SELECT plproxy.eq_real_nodes('usercluster', ?i, ?i)", 
                        (int) $event->data['shardkey'],
                        $event->data['from_id']
                    );*/
                    //if ( $senderNode == 't' ) {
                        $bind = $this->bind($event->data['message_id']);
                        if ( $bind['mailId'] ) {
                            $this->_dbProxy->query(
                                "SELECT pgq.insert_event('masssend', 'masssend_post', ?)", 
                                "message_id={$event->data['message_id']}&from_id={$event->data['from_id']}&last_received=0" .
                                "&mail_id={$bind['mailId']}&mail_func={$bind['mailFunc']}"
                            );
                        }
                    //}
                }
                $r = !$this->_dbProxy->error;
                break;
            }

            
            // ----------------------------------------------------------------------------------------------------
            // рассылка почты с помощью метода указанного в функции masssend. метод должен быть объвлен в         -
            // классе pmail                                                                                       -
            // ----------------------------------------------------------------------------------------------------
            case 'masssend_post': {
                $mail   = new pmail;
                $count = 0;
                $sender = (int) $event->data['from_id'];
                $messId = (int) $event->data['message_id'];
                $mailId = (int) $event->data['mail_id'];
                $mailFunc = $event->data['mail_func'];
                $last_received = (int)$event->data['last_received'];
                $bind = $this->bind($messId);
                
                // проверка от дублей на всякий.
                if($last_received < (int)$bind['last_received']) {
                    $r = true;
                    $this->_logExInfo = 'Duplicate event!';
                    break;
                }
                
                $users = $this->_dbProxy->col('SELECT user_id FROM masssend_queue(?i, ?i, ?i, ?i)',
                                              $sender, $messId, $last_received, self::MASSSEND_POST_QUEUE_SIZE);
                if($users) {
                    call_user_func(array($mail, $mailFunc), $messId, $mailId, $users);
                    $count = count($users);
                    $last_received = $users[$count - 1];
                    $bind['last_received'] = $last_received;
                    $this->bind($messId, $bind);
                    if ( $count >= self::MASSSEND_POST_QUEUE_SIZE ) {
                        $this->_dbProxy->query(
                            "SELECT pgq.insert_event('masssend', 'masssend_post', ?)", 
                            "message_id={$messId}&from_id={$sender}&last_received={$last_received}" .
                            "&mail_id={$mailId}&mail_func={$mailFunc}"
                        );
                    }
                }
                
                $r = !$this->_dbProxy->error;
                $this->_logExInfo = "{$count} email(s)";
                break;
            }
            
            // ----------------------------------------------------------------------------------------------------
            // -- Подготовка адресатов с помощью sql запроса. На тот случай, если нельзя подготовить список       -
            // -- получателей заранее, например из-за низкого таймаута скрипта или нельзя заставлять пользователя -
            // -- ждать. Запрос выполняется на master и полученные пользователи передаются в masssend_bind        -
            // -- по spam::MASSSEND_BIND_QUEUE_SIZE за операцию. Важно! чтобы в запросе колонка uid шла первой в select.                   -
            // -- Если запрос выполнится неудачно, plproxy-nsync работу не останавливает!                         -
            // ----------------------------------------------------------------------------------------------------
            case 'masssend_sql': {
                $res = $this->_dbMaster->query($event->extra1);
                if ( $res ) {
                    $i = 0;
                    $c = 0;
                    $uids = array();
                    while ( $row = pg_fetch_row($res) ) {
                        $uids[] = $row[0];
                        if ( ++$i == spam::MASSSEND_BIND_QUEUE_SIZE ) {
                            $this->_dbProxy->query(
                                "SELECT masssend_bind(?i, ?i, ?a)",
                                $event->data['message_id'], 
                                $event->data['from_id'], 
                                $uids
                            );
                            $i = 0;
                            unset($uids);
                            $uids  = array();
                        }
                        $c++;
                    }
                    if ( $i ) {
                        $this->_dbProxy->query(
                            "SELECT masssend_bind(?i, ?i, ?a)",
                            $event->data['message_id'], 
                            $event->data['from_id'], 
                            $uids
                        );
                        unset($uids);
                    }
                    $this->_dbProxy->query("SELECT masssend_commit(?i, ?i)", $event->data['message_id'], $event->data['from_id']);
                    $this->_logExInfo = "{$c} users";
                } else {
                    $this->_logExInfo = "query error";
                }
                $r = !$this->_dbProxy->error;
                break;
            }
            
            // ----------------------------------------------------------------------------------------------------
            // -- Если messages::Masssending() пытается начать рассылку пока еще не закончилась предыдущая, то мы 
            // -- перекладываем рассылку в очередь чтобы она подождала. Тут мы делаем попытку снова начать новую 
            // -- рассылку. Грубо говоря еще раз messages::Masssending() только через какое то время.
            // ----------------------------------------------------------------------------------------------------
            case 'masssend_queue': {
                $this->_dbProxy->query( 
                    'SELECT masssend(?, ?, ?, ?, ?, ?, ?)', 
                    $event->data['from_id'], $event->extra1, $event->data['attachments'], 
                    $event->data['masssend_id'], $event->data['posted_time'], $event->data['mail_func'], $event->extra2 
                );
                $r = !$this->_dbProxy->error;
                break;
            }
        }
        
        if ($event->type) {
            $time = round(microtime(TRUE) - $time, 2);
            $mem  = round(memory_get_usage() / 1048576, 2);
            $notice  = "{$event->type} [id:{$event->id},txid:{$event->txid},time:{$time},mem:{$mem}MB]... ";
            $notice .= ( $r? "Success": "Error" ) . ($this->_logExInfo? " ({$this->_logExInfo})": "");
            $this->log->notice($notice);
        }

        if ($r) {
            return PGQ_EVENT_OK;
        } else {
            $this->stop();
            return PGQ_ABORT_BATCH;
        }
        
    }
    
    
}

new PGQDaemonMasssend("masssend", "masssend", $argc, $argv, PGQ_DB_CONN);

