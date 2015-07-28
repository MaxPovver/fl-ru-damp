<?php
define('IS_PGQ', 1);
define('DEBUG_DAEMON', 0); // чтобы протестить работу самого демона, а не конcюмера.

require("conf/plproxy-msync.php");
require_once ABS_PATH . "/classes/globals.php";
require_once ABS_PATH . "/classes/DB.php";


class PGQDaemonMSYNC extends PGQConsumer {

	protected $_dbMaster  = NULL;
	
	public function config( )
	{
	    global $Config;

		if( $this->log !== null )
			$this->log->notice("Reloading configuration (HUP) from '%s'", $Config["LOGLEVEL"]);
		
		$this->loglevel = $Config["LOGLEVEL"];
		$this->logfile  = $Config["LOGFILE"];
		$this->delay    = $Config["DELAY"];

		$this->_dbMaster = new DB('master');
	}


    public function process_event(&$event) {
		
		$r  = FALSE;
		
		switch ($event->type) {
		
			case 'sync_freelancer': {
				$r = $this->_dbMaster->query("SELECT sync_freelancer(?i, ?, ?, ?, ?, ?, ?, ?, ?, ?b, ?b, ?b, ?, ?, ?, ?, ?, ?, ?, ?b, ?b)",
					(int) $event->data['SHARDKEY'],
					$event->data['OP'],
					$event->data['OLD_UID'],
					$event->data['IN_UID'],
					$event->extra1,
					$event->extra2,
					$event->extra3,
					$event->extra4,
					($event->data['IN_IS_BANNED']? $event->data['IN_IS_BANNED']: '0'),
					(($event->data['IN_IS_PRO'] == 'true')? TRUE: FALSE),
					(($event->data['IN_IS_PRO_TEST'] == 'true')? TRUE: FALSE),
					(($event->data['IN_IS_PRO_NEW'] == 'true')? TRUE: FALSE),
					$event->data['IN_ROLE'],
					($event->data['IN_PHOTO']? $event->data['IN_PHOTO']: ''),
					($event->data['IN_PHOTOSM']? $event->data['IN_PHOTOSM']: ''),
					$event->data['IN_SUBSCR'],
					$event->data['IN_REG_DATE'],
					(int) $event->data['IN_SPEC'],
					(int) $event->data['IN_SPEC_ORIG'],
                    (($event->data['IN_IS_TEAM'] == 'true')? TRUE: FALSE),
                    (($event->data['IN_IS_VERIFY'] == 'true')? TRUE: FALSE)
				);
				break;
			}
			
			case 'sync_employer': {
				$r = $this->_dbMaster->query("SELECT sync_employer(?i, ?, ?, ?, ?, ?, ?, ?, ?, ?b, ?b, ?b, ?, ?, ?, ?, ?, ?b, ?b)",
					(int) $event->data['SHARDKEY'],
					$event->data['OP'],
					$event->data['OLD_UID'],
					$event->data['IN_UID'],
					$event->extra1,
					$event->extra2,
					$event->extra3,
					$event->extra4,
					($event->data['IN_IS_BANNED']? $event->data['IN_IS_BANNED']: '0'),
					(($event->data['IN_IS_PRO'] == 'true')? TRUE: FALSE),
					(($event->data['IN_IS_PRO_TEST'] == 'true')? TRUE: FALSE),
					(($event->data['IN_IS_PRO_NEW'] == 'true')? TRUE: FALSE),
					$event->data['IN_ROLE'],
					($event->data['IN_PHOTO']? $event->data['IN_PHOTO']: ''),
					($event->data['IN_PHOTOSM']? $event->data['IN_PHOTOSM']: ''),
					$event->data['IN_SUBSCR'],
					$event->data['IN_REG_DATE'],
                    (($event->data['IN_IS_TEAM'] == 'true')? TRUE: FALSE),
                    (($event->data['IN_IS_VERIFY'] == 'true')? TRUE: FALSE)
				);
				break;
			}
			
			case 'sync_professions': {
				$r = $this->_dbMaster->query("SELECT sync_professions(?i, ?, ?, ?, ?, ?, ?)",
					(int) $event->data['SHARDKEY'],
					$event->data['OP'],
					$event->data['OLD_ID'],
					$event->data['IN_ID'],
					$event->extra1,
					($event->data['IN_PROF_GROUP']? $event->data['IN_PROF_GROUP']: '0'),
					($event->extra2? $event->extra2: '')
				);
				break;
			}

			case 'sync_prof_group': {
				$r = $this->_dbMaster->query("SELECT sync_prof_group(?i, ?, ?, ?, ?)",
					(int) $event->data['SHARDKEY'],
					$event->data['OP'],
					$event->data['OLD_ID'],
					$event->data['IN_ID'],
					$event->extra1
				);
				break;
			}
			
		}
		
		if ($event->type) {
			$this->log->notice("Sync function {$event->type} [id:{$event->id},txid:{$event->txid}]... " . ($r? "Success": "Error"));
		}
		
		if ($r) {
			return PGQ_EVENT_OK;
		} else {
			$this->stop();
		}
		
    }
	
	
}

$daemon = new PGQDaemonMSYNC("plproxy-msync", "msync", $argc, $argv, PGQ_DB_CONN);
?>