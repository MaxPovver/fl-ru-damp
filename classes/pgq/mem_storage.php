<?php

/**
 * Константа для classes/stdf.php 
 */
define('IS_PGQ', 1);


require_once dirname(__FILE__) . '/conf/mem_storage.php';


class PGQDaemonMemStorage extends PGQConsumer
{
        private $_mainDb = NULL;
        
        

        public function config( )
	{
	    global $Config;

            if( $this->log !== null )
            {
                $this->log->notice("Reloading configuration (HUP) from '%s'", $Config["LOGLEVEL"]);
            }
		
            $this->loglevel = $Config["LOGLEVEL"];
            $this->logfile  = $Config["LOGFILE"];
            $this->delay    = $Config["DELAY"];
	}
	
	public function process_event( &$event ) 
	{
            $this->force_connect();

            
            switch ( $event->type ) {
                
                case 'newsletter_freelancer':
                    
                    $uid = (int)$event->data['uid'];
                    //$op  = $event->data['op'];
                    
                    $ms = new MemStorage('newsletter_freelancer');
                    
                    if($ms->isExistData())
                    {
                        $item = freelancer::GetPrjRecp($uid);
                        $page_id = $ms->isExistItem($uid);
                        
                        $this->log->notice("getDebugInfo: " . $ms->getDebugInfo());
                        $this->log->notice("BEFORE: " . print_r($ms->getItem($uid),true));

                        if($item)
                        {
                            if($page_id === FALSE)
                            {
                                //insert
                                $ms->insertItem($uid, $item);
                                $this->log->notice("insertItem {$uid}");
                            }
                            else
                            {
                                //update
                                $ms->updateItem($uid, $item, $page_id);
                                $this->log->notice("updateItem {$uid}");
                            }
                        }
                        elseif($page_id)
                        {
                            //delete
                            $ms->deleteItem($uid, $page_id);
                            $this->log->notice("deleteItem {$uid}");
                        }
                        
                        
                        $this->log->notice("AFTER: " . print_r($ms->getItem($uid),true));
                        
                    }
                    
                    break;
                
                    
                    
                    
            }
            
            return PGQ_EVENT_OK;
	}
        
        
        
        
        
        protected function force_connect() 
        {
            global $DB;

            if (!$this->_mainDb) {
                //$this->log->notice('CONNECT: Force new connection to main DB');
                $this->_mainDb = $DB->connect(TRUE);
            }

            return $this->_mainDb;
        }
}

new PGQDaemonMemStorage('mem_storage', 'mem_storage', $argc, $argv, PGQ_DB_CONN);