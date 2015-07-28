<?php
define('DEBUG_DAEMON', 0); // чтобы протестить работу самого демона, а не конcюмера.
define('CONFIGURATION', 'conf/win_pgq_test.php');

// Куда-нибудь запихать.
require("../config.php");
define('SYSDAEMON', (stripos($_SERVER['OS'], 'WINDOWS')!==false ? 'Win' : '').'SystemDaemon');
$Config["LOGFILE"] = 'logs/'.basename($argv[0]).'.log';
$con_dst = "host=".$pg_db['slave']['host']." port=".$pg_db['slave']['port']." dbname=".$pg_db['slave']['name']." user=".$pg_db['slave']['user']." password=".$pg_db['slave']['pwd'];
$con_src = "dbname=freelance port=5432 host=beta.free-lance.ru user=freelance password=RhjkbxmzYjhf";
/////

require("api/PGQConsumer.php");

class PGQDaemonExample extends PGQConsumer
{
	public function config( )
	{
		unset($Config);
		if( $this->log !== null )
			$this->log->notice("Reloading configuration (HUP) from '%s':", CONFIGURATION);
		global $Config;
    require(CONFIGURATION);
		$this->loglevel = $Config["LOGLEVEL"];
		$this->logfile  = $Config["LOGFILE"];
		$this->delay    = $Config["DELAY"];
	}
	
	public function process_event( &$event ) 
	{
		$this->log->notice("Starting process event");
		ob_start();
		print_r($event);
		$str = ob_get_clean();
		$this->log->notice($str);

		return PGQ_ABORT_BATCH;
	}
}

$daemon = new PGQDaemonExample( "wintestC", "wintestQ", $argc, $argv, $con_src );
?>