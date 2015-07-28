<?php
/**
 * Модуль для обработки очереди "mail_simple" консюмером "mail_simple_cons".
 * Данная очередь предназначена для отправки уведомлений по e-mail из
 * группы типа "отправить как можно быстрее и как можно меньшему количеству получателей",
 * например, в блогах, личке, сообществах, когда одно событие (комментарий в блоге, например)
 * рассчитано не более, чем на 10 получателей.
 */
define('IS_PGQ', 1);

define('DEBUG_DAEMON', 0);
define('CONFIGURATION', dirname(__FILE__) . '/conf/mail_cons.php');
require(CONFIGURATION);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php");

/**
 * Консюмер -- обработчик событий очереди "mail_simple".
 * Обрабатывает целиком пакет событий, то есть общий принцип в том,
 * чтобы в process_event() собрать все необходимые данные (чаще -- ид. сообщений)
 * в массив $_lastBatch['Имя_метода'], (в качестве имени пакета нужно использовать
 * имя метода класса smail, отвечающего за обработку события и отправку уведомления).
 * А потом в finish_batch() одним махом отправить все сообщения по адресатам.
 */
class PGQMailSimpleConsumer extends PGQConsumer
{
    /**
     * Собирает в себя данные из текущей пачки событий.
     * Сбрасывается в NULL после обработки пачки.
     * 
     * @var array
     */
    private $_lastBatch = NULL;

    /**
     * Размер текущего пакета (для дальнейшего контроля).
     * 
     * @var integer
     */
    private $_lastBatchSize = 0;
    
    
    private $_mainDb = NULL;


	/**
	 * Метод наследуется от PGQConsumer и запускается при start или restart методах.
	 */
	public function run() {
		if (DEBUG_DAEMON) {
			parent::run();
			return;
		}
		// $this->_reCreateCheck();
		parent::run();
	}
	
	/**
     * Инициализирует необходимые параметры из массива $Config, определенного в
     * файле CONFIGURATION. Вызывается при старте демона, или по команде reload.
     */
    public function config()
    {
        unset($Config);
        if($this->log !== null)
            $this->log->notice("Reloading configuration (HUP) from '%s':", CONFIGURATION);
        global $Config;
        require CONFIGURATION;
        $this->loglevel = $Config["LOGLEVEL"];
        $this->logfile  = $Config["LOGFILE"];
        $this->delay    = $Config["DELAY"];
		$this->restart_events_interval = $Config["RESTART_EVENTS_INTERVAL"];
		$this->restart_events_count    = $Config["RESTART_EVENTS_COUNT"];
	}

    /**
     * Обработка одного события, вызывается из PGOConsumer::process_event().
     * Собираем данные в $this->_lastBatch.
     */
    public function process_event(&$event)
    {
        if($event->type) {
            $this->_lastBatch[$event->type][] = count($event->data) > 1 ? $event->data : $event->data['id'];
            $this->_lastBatchSize++;
        }
        return PGQ_EVENT_OK;
    }

    /**
     * Обработка $this->_lastBatch и завершение текущей пачки.
     *
     * @overridden PGOConsumer::finish_batch()
     */
    protected function finish_batch($batch_id)
    {
        if($this->_lastBatch) {
            $this->log->notice('Получен пакет (%d событий).', $this->_lastBatchSize);
            $sm = new pmail();
            $this->force_connect();
            
            foreach($this->_lastBatch as $sender=>$data) {
                // Рабочие значания $sender: BlogNewComment, CommuneNewComment.
                if(!$data) continue;
                $this->log->notice('%s: %d сообщений на входе.', $sender, count($data));
                $this->log->notice('%s: %d писем обработано.', $sender, $sm->$sender($data, $this->pg_src_con));
            }
        }

        $this->_lastBatchSize = 0;
        $this->_lastBatch = NULL;

        return parent::finish_batch($batch_id);
    }
    
    
    protected  function force_connect() {
        global $DB;
        
        if (!$this->_mainDb) {
            //$this->log->notice('CONNECT: Force new connection to main DB');
            $this->_mainDb = $DB->connect(TRUE);
        }
        
        return $this->_mainDb;
    }
    
    
	/**
	 * При запуске консьюмера проверяет, созданы ли консьюмер и очередь в схеме таблиц pgq.
	 * Если созданы, то проверяет не достигло ли количество событий "критического" значения.
	 * Если достигнуты или очереди с консьюмером не существует, то они создаются.
	 *
	 * "Критические" значения это: если скопившееся количество событий с момента последней их обработки
	 * больше $Config["RESTART_EVENTS_COUNT"] или время между последним созданным событием и временем
	 * последней их обработки больше $Config["RESTART_EVENTS_INTERVAL"]. Подробнее conf/mail_cons.php
	 *
	 * Метод наследуется от PGQConsumer и запускается при start или restart методах.
	 */
    private function _reCreateCheck() {
		$restart = TRUE;
		if ($this->check()) {
		    if ($this->connect() === FALSE) return FALSE;
			$restart = FALSE;
			if ($this->restart_events_count > 0) {
				$sql = "
					SELECT
						COUNT(*)
					FROM
						pgq.event_template
					WHERE
						ev_time > (
							SELECT
								sub_active
							FROM
								pgq.subscription
							WHERE
								sub_queue = (SELECT queue_id FROM pgq.queue WHERE queue_name = '{$this->qname}')
						)
				";
				$res = pg_query($this->pg_src_con, $sql);
				$row = pg_fetch_row($res);
				$restart = ($row[0] > $this->restart_events_count);
			}
			if (!$restart && $this->restart_events_interval > 0) {
				$sql = "
					SELECT
						extract('epoch' from (NOW() - sub_active - interval '{$this->restart_events_interval} seconds'))
					FROM
						pgq.subscription
					WHERE
						sub_queue = (SELECT queue_id FROM pgq.queue WHERE queue_name = '{$this->qname}')
				";
				$res = pg_query($this->pg_src_con, $sql);
				$row = pg_fetch_row($res);
				if ($row[0] > 0) $restart = TRUE;
			}
			if ($restart) {
				$this->unregister();
				$this->drop_queue();
			}
		} else {
			if ($this->connect() === FALSE) return FALSE;
		}
		if ($restart) {
			$this->create_queue();
			$this->register();
			$sql = "
				UPDATE
					pgq.queue
				SET
					queue_ticker_max_count = 500,
					queue_ticker_max_lag = '00:00:05'::interval -- пять секунд, чтобы успеть изменить комментарий :)
				WHERE 
					queue_name = '{$this->qname}'
			";
			pg_query($this->pg_src_con, $sql);
			$this->log->notice("mPGQMailSimpleConsumer.run(): консьюмер {$this->cname} создан, очередь {$this->qname} создана.");
		} else {
			$this->log->notice("PGQMailSimpleConsumer.run(): создание очереди {$this->qname} не требуется.");
		}
		$this->disconnect();
    }

}

$daemon = new PGQMailSimpleConsumer('mail_simple_cons', 'mail_simple', $argc, $argv, PGQ_DB_CONN);
?>
