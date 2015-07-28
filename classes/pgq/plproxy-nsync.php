<?php
define('IS_PGQ',       1);
define('IN_STDF',      1);
define('DEBUG_DAEMON', 0);

require_once "conf/plproxy-nsync.php";
require_once ABS_PATH . "/classes/globals.php";
require_once ABS_PATH . "/classes/memBuff" . (defined('USE_MEMCACHED') ? 2 : 1) . ".php";
require_once ABS_PATH . "/classes/DB.php";
require_once ABS_PATH . "/classes/messages.php";
require_once ABS_PATH . "/classes/pmail.php";

class PGQDaemonPlproxy extends PGQConsumer {
	
	private $cur_txid = 0;
	private $cur_events = 0;
	private $crash = array();
	private $crashflag = FALSE;
    
    protected $_dbMaster  = NULL;
    protected $_dbProxy   = NULL;
    protected $_logExInfo = '';
    protected $_binds     = array();

	public function config( )
	{
	    global $Config;

		if( $this->log !== null )
			$this->log->notice("Reloading configuration (HUP) from '%s'", $Config["LOGLEVEL"]);
		
		$this->loglevel = $Config["LOGLEVEL"];
		$this->logfile  = $Config["LOGFILE"];
		$this->delay    = $Config["DELAY"];

        $this->_dbProxy  = new DB('plproxy');
        $this->_dbMaster = new DB('master');
        $this->_dbProxy->connect(TRUE);
        $this->_dbMaster->connect(TRUE);
        
        $this->_memBuff  = new memBuff;
        
		$this->crash = $this->_dbProxy->row("SELECT * FROM nsync_crash ORDER BY id DESC LIMIT 1");
		if ($this->crash['cmd_done'] == 't') {
			$this->crash = array();
		}
	}

	public function  __destruct() {
		if ($this->crashflag) {
			$this->_dbProxy->query("INSERT INTO nsync_crash(txid, crash_time, cmd_executed) VALUES(?, NOW(), ?)", $this->cur_txid, $this->cur_events);
		}
	}

    public function process_event(&$event) {
		$r    = FALSE;
        $time = microtime(TRUE);
        
        $this->_logExInfo = '';

		if ($event->txid != $this->cur_txid) {
			$this->cur_txid = $event->txid;
			$this->cur_events = 0;
		}

		if ( !empty($this->crash) ) {
			if (($this->crash['txid'] == $this->cur_txid) && ($this->cur_events < $this->crash['cmd_executed'])) {
				$this->cur_events++;
				return PGQ_EVENT_OK;
			} else {
				$this->_dbProxy->query("UPDATE nsync_crash SET cmd_done = TRUE WHERE id = {$this->crash['id']}");
				$this->crash = array();
			}
		}

		switch ($event->type) {
		
			// новое сообщение
			case 'messages_add': {
				$r = $this->_dbProxy->query("SELECT messages_add(?i, ?, ?, ?, ?, ?, ?, ?, ?b)",
					(int) $event->data['SHARDKEY'],
					$event->data['IN_ID'],
					$event->data['IN_FROM'],
					$event->data['IN_TO'],
					$event->data['IN_TIME'],
					$event->extra1,
					$event->data['IN_SKIP'],
					$event->extra2,
                    $event->data['IN_NO_MOD']
				);
                
                if ( $event->data['IN_NO_MOD'] == 'false' ) {
                    // Поставить сообщение в очередь модерирования
                    $this->_dbProxy->query( 'SELECT messages_moder_add(?i, ?i)', $event->data['IN_FROM'], $event->data['IN_ID'] );
                }
                
                $this->_memBuff->delete(messages::MEMBUFF_CHAT_PREFIX . $event->data['IN_TO']);
				break;
			}
            

			// массовая рассылка (старый вариант! больше не использовать, скоро удалю!)
			case 'messages_masssend': {
				$r = $this->_dbProxy->query("SELECT messages_masssend(?i, ?, ?, ?, ?, ?, ?::integer[])",
					(int) $event->data['SHARDKEY'],
					$event->data['IN_ID'],
					$event->data['IN_FROM'],
					$event->extra1,
					$event->data['IN_TIME'],
					$event->extra2,
					$event->extra3
				);
				break;
			}
            
            // для совместимости
            case 'clear_memcache_msg_count': {
                $r = TRUE;
                break;
            }
            
			// кого оставить в избранных
			case 'teams_leave': {
				$r = $this->_dbProxy->query("SELECT teams_leave(?i, ?i, ?)",
					(int) $event->data['SHARDKEY'],
					(int) $event->data['IN_UID'],
					$event->data['IN_TARGET']
				);
				break;
			}
			
			// все остальные функции для которых не нужно особой обработки
			default: {
				$r = $this->_dbProxy->query("SELECT {$event->type}('".implode($event->data, "','")."')");
			}
			
		}
		
		if ($event->type) {
			$time = round(microtime(TRUE) - $time, 4);
            $notice  = "Sync function {$event->type} [id:{$event->id},txid:{$event->txid},time:{$time}]... ";
            $notice .= ( $r? "Success": "Error" ) . ($this->_logExInfo? " ({$this->_logExInfo})": "");
            $this->log->notice($notice);
		}
		
		if ($r) {
			$this->cur_events++;
			return PGQ_EVENT_OK;
		} else {
			$this->crashflag = TRUE;
			$this->stop();
		}
		
    }
	
	
}

new PGQDaemonPlproxy("plproxy-nsync", "nsync", $argc, $argv, PGQ_DB_CONN);
?>
