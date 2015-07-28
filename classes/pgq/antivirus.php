<?php
define('IS_PGQ', 1);
require_once dirname(__FILE__) . '/conf/antivirus.php';


class PGQDeamonAntivirus extends PGQConsumer {

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
		if ($event->type == 'check' && $event->data['id']) {
			$file = new CFile($event->data['id']);
			$file->antivirus();
			if ($file->virus == 0) {
				$status = 'Clear.';
			} else if ($file->virus == 1) {
				$status = 'Infected. ' . $file->virusName . '.';
			} else if ($file->virus == 2) {
				$status = 'Skipped by archive restrictions.';
			} else if ($file->virus == 16) {
                $status = 'No need check. Skipped.';
            } else {
				$status = 'Error.';
			}
			$this->log->notice("File \"{$file->name}\" ({$file->table}) ({$file->id}). {$status}");
		}
		return PGQ_EVENT_OK;
    }

}

$daemon = new PGQDeamonAntivirus('antivirus', 'antivirus', $argc, $argv, PGQ_DB_CONN);

