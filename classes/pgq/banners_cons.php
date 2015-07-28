<?php
define('DEBUG_DAEMON', 0); // чтобы протестить работу самого демона, а не конcюмера.

require("conf/banners_cons.php");

class PGQDaemonBanners extends PGQConsumer
{
	public function config( )
	{
	    global $Config;
		//unset($Config);
		if( $this->log !== null )
			$this->log->notice("Reloading configuration (HUP) from '%s'", $Config["LOGLEVEL"]);
		
		$this->loglevel = $Config["LOGLEVEL"];
		$this->logfile  = $Config["LOGFILE"];
		$this->delay    = $Config["DELAY"];
	}

    /**
     * Обработка пачки событий (вызывается демоном)
     *
     * @param integer $batch_id
     * @return boolean
     */
    public function process_batch($batch_id) {
        $events = $this->preprocess_batch($batch_id);
        
        if( $events === False) {
            $this->log->verbose("PGQDaemonBanners.preprocess_batch got not events (False).");
            return False;
        }
        
        /**
         * Event processing loop!
         */
        $abort_batch = False;
        
        $sql = "PREPARE updBanners (int, int, int) AS 
        		UPDATE ban_stats1 set views = views + $2, clicks = clicks + $3 WHERE banner_id = $1;\n";
        
        foreach( $events as $event ) {
            if( $abort_batch ) break;
            
            $id = $event->data['id'];
		    $type = $event->type;
		    
		    if ($type == 'click'){
		        $bans[$id]['click']++;
		    } elseif ($type == 'view'){
		        $bans[$id]['view']++;
		    }
            $this->log->verbose("PGQDaemonBanners.process_batch type %s event %d of batch %d ",
                            $type, $event->id, $batch_id);
            $this->log->verbose("PGQDaemonBanners.process_batch processed event %d of batch %d",
                            $event->id, $batch_id);
        }
        
        if ($bans){
            foreach ($bans as $ikey=>$ban){
                $sql .= "EXECUTE updBanners($ikey, ".zin($ban['view']).", ".zin($ban['click']).");\n";
            }
            
            $sql .= "DEALLOCATE updBanners;";
            
            $result = pg_query( DBConnect(), $sql );
            $this->log->debug( "UPDATE : %s ", $sql );
            
            if( $result === False ) 
            {
            	$this->log->error( "Unable to update : %s ", $sql );
            	$abort_batch = True;
            }
        } else {
            $this->log->verbose("PGQDaemonBanners.process_batch has no banners");
        }
        
        return $this->postprocess_batch($batch_id, $abort_batch);
    }
	
}

$daemon = new PGQDaemonBanners( "banners_consumer", "banners", $argc, $argv, PGQ_DB_CONN);
?>