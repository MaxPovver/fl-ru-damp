<?php
define('IS_PGQ', 1);
require_once dirname(__FILE__) . '/conf/share.php';

class PGQDaemonShare extends PGQConsumer {


	public function config() {
		global $config;
		if($this->log !== null) {
			$this->log->notice("Reloading configuration (HUP)");
		}
		$this->loglevel = $config["LOGLEVEL"];
		$this->logfile  = $config["LOGFILE"];
		$this->delay    = $config["DELAY"];
	}
    

    public function process_event(&$event) {

        $r = FALSE;

        switch ( $event->type ) {

            case 'memcache_delete': {
                require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/memBuff.php';
                $memBuff = new memBuff;
                $memBuff->delete($event->data['key']);
                $r = TRUE;
                break;
            }

            case 'memcache_flush_group': {
                require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/memBuff.php';
                $memBuff = new memBuff;
                $memBuff->flushGroup($event->data['key']);
                $r = TRUE;
                break;
            }

            case 'static_compress.createBatchBySeed' :
                $GLOBALS['DEBUG_VAR'] = array();
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/static_compress.php');
                $memBuff = new memBuff();
                $memBuff->set('eto.kostyl.inache.tupit.set.v.createBatch', 1, 1);
                $stc = new static_compress();
                $r = !($error = $stc->createBatchBySeed($event->data['seed']));
                break;
        }

		if ( $event->type ) {
			$this->log->notice("Function {$event->type}... " . ($r ? 'Success': 'Error '.$error));
			$this->log->notice(base64_decode($event->data['seed']));
			$this->log->notice(var_export($GLOBALS['DEBUG_VAR']));
		}

    	return PGQ_EVENT_OK;

    }


}

$daemon = new PGQDaemonShare("share", "share", $argc, $argv, PGQ_DB_CONN);
