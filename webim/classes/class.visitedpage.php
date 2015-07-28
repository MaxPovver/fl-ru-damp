<?php
/* 
 * 
 * Данный файл является частью проекта Веб Мессенджер.
 * 
 * Все права защищены. (c) 2005-2009 ООО "ТОП".
 * Данное программное обеспечение и все сопутствующие материалы
 * предоставляются на условиях лицензии, доступной по адресу
 * http://webim.ru/license.html
 * 
 */
?>
<?php 
require_once('common.php');
require_once('class.thread.php');
require_once('class.invitation.php');

class VisitedPage  {
  const VISITED_PAGE_FILE_EXT = "page";
  const CLOSED_VISITED_PAGE_FILE_EXT = "closedpage";
  const VISITSESSION_FILENAME_EXT = "visitsession";
  
  private $alive_visitors = array();
  private $dead_visitors = array();

  private static $instance = NULL;

  static function GetInstance() {
    if (self::$instance == NULL) {
      self::$instance = new VisitedPage();
    }
    return self::$instance;
  }

  function CreateVisitedPage($visitsessionid, $uri, $referrer, $title = null) {
    $time = time();
    $hashTable = array(
      'visitsessionid' => $visitsessionid,
      'uri' => $uri,
      'referrer' => $referrer,
      'opened' => $time,
      'updated' => $time,
      'state' => VISITED_PAGE_LOADING,
      'invitationid' => NULL,
      'title' => $title
       
    );
	
    $id = md5($visitsessionid.$uri.$time);
    self::writeToFile($id, $hashTable);
    self::appendDataToVisitSessionFile($visitsessionid, $hashTable);
	
    return $id;
  }
  
  static function getVistedPageFilename($id, $closed = false) {
    return TRACKER_FILES_DIR . DIRECTORY_SEPARATOR . 
      substr(md5($id), 0, 1) . DIRECTORY_SEPARATOR . 
      $id . "." . ($closed ? self::CLOSED_VISITED_PAGE_FILE_EXT : self::VISITED_PAGE_FILE_EXT);
  }
  
  static function getVisitSessionFilename($visitsessionid) {
    return TRACKER_FILES_DIR . DIRECTORY_SEPARATOR . 
      substr(md5($visitsessionid), 0, 1) . DIRECTORY_SEPARATOR . 
      $visitsessionid . "." . self::VISITSESSION_FILENAME_EXT;
  }

  static function writeToFile($id, $data, $closed = false) {
    $filename = self::getVistedPageFilename($id, $closed);
    // папка online в мэмкэш --------------------
    //create_basedir($filename);
    //return @file_put_contents($filename, serialize($data), LOCK_EX) !== false;
    if ( !($aKeys = $GLOBALS['mem_buff']->get('TRACKER_FILES_DIR')) ) {
        $aKeys = array();
    }
    
    $aKeys[$filename] = 1;
    
    $mtime  = time();
    $bRret1 = $GLOBALS['mem_buff']->set( 'TRACKER_FILES_DIR', $aKeys, 3600 );
    $bRret2 = $GLOBALS['mem_buff']->set( 
        $filename, 
        serialize( array_merge( $data, array('my_mtime' => $mtime) ) ), 
        VISITED_PAGE_TIMEOUT * 3 
    );
    
    return ( $bRret1 && $bRret2 );
  }
 
  static function appendDataToVisitSessionFile($visitsessionid, $data) {
    if(empty($visitsessionid)) {
      return false;
    }

    $filename = self::getVisitSessionFilename($visitsessionid);
    // папка online в мэмкэш --------------------
    //create_basedir($filename);
    //return @file_put_contents($filename, serialize($data) . "\n", FILE_APPEND);
    if ( !($sData = $GLOBALS['mem_buff']->get($filename)) ) {
        $sData = '';
    }
    
    if ( !($aKeys = $GLOBALS['mem_buff']->get('TRACKER_FILES_DIR')) ) {
        $aKeys = array();
    }
    
    $aKeys[$filename] = 1;
    
    $mtime  = time();
    $bRret1 = $GLOBALS['mem_buff']->set( 'TRACKER_FILES_DIR', $aKeys, 3600 );
    $bRret2 = $GLOBALS['mem_buff']->set( $filename, $sData . serialize($data) . "\n", VISITSESSION_FILE_TTL );
    
    return ( $bRret1 && $bRret2 );
  }
   
  static function overwriteDataToVisitSessionFile($visitsessionid, $data) {
    if(empty($visitsessionid)) {
      return false;
    }
    
    $filename = self::getVisitSessionFilename($visitsessionid);
    
    // папка online в мэмкэш --------------------
    //create_basedir($filename);
    
    $to_write = implode("\n", array_map("serialize",$data)); // так и было

    // папка online в мэмкэш --------------------
    //return @file_put_contents($filename, $to_write. "\n", LOCK_EX);
    
    if ( !($aKeys = $GLOBALS['mem_buff']->get('TRACKER_FILES_DIR')) ) {
        $aKeys = array();
    }
    
    $aKeys[$filename] = 1;
    
    $bRret1 = $GLOBALS['mem_buff']->set( 'TRACKER_FILES_DIR', $aKeys, 3600 );
    $bRret2 = $GLOBALS['mem_buff']->set( $filename, $to_write. "\n", VISITSESSION_FILE_TTL );
    
    return ( $bRret1 && $bRret2 );
  }
  
  function enumVisitedPagesByVisitSessionId($visitsessionid) {
    $filename = self::getVisitSessionFilename($visitsessionid);
    
    // папка online в мэмкэш --------------------
    /*if(!file_exists($filename)) {
      return array();	
    }*/
    if ( !($sData = $GLOBALS['mem_buff']->get($filename)) ) {
    	return array();
    }

    $visitedpages = array();
    
    // папка online в мэмкэш --------------------
    //$lines = file($filename);
    $lines = explode( "\n", $sData );
    
    foreach ($lines as $line) {
      $data = unserialize($line);
      if($data === false || !isset($data['visitedpageid']) || !isset($data['visitsessionid']) || empty($data['visitsessionid']))
        continue;
        	
      $visitedpages[$data['visitedpageid']] = $data;	
    }

    $result = array_values($visitedpages);

    self::overwriteDataToVisitSessionFile($visitsessionid, $result); //compact file
    return $result;

  }
  
  function UpdateVisitedPage($visitedpageid, $params = array()) {
    $visitedpage = $this->GetVisitedPageById($visitedpageid);
    
    if(!is_array($visitedpage)) {
      return false;
    }	

    $paramsHash = array('updated' => time() , "visitedpageid" => $visitedpageid, "state" => VISITED_PAGE_OPENED);
    
    $paramsHash = array_merge($visitedpage, $paramsHash);
    $paramsHash = array_merge($paramsHash, $params);
    
    // папка online в мэмкэш --------------------
    //self::writeToFile($visitedpageid, $paramsHash);

    self::appendDataToVisitSessionFile($paramsHash['visitsessionid'], $paramsHash);
 
    if($paramsHash['state'] == VISITED_PAGE_CLOSED) {
      // папка online в мэмкэш --------------------
      //rename(self::getVistedPageFilename($visitedpageid), self::getVistedPageFilename($visitedpageid, true));
      $GLOBALS['mem_buff']->delete( self::getVistedPageFilename($visitedpageid) );
      self::writeToFile( $visitedpageid, $paramsHash, true );
    }
    // папка online в мэмкэш --------------------
    else {
        self::writeToFile($visitedpageid, $paramsHash);
    }
  }
  
  function addOrUpdateFromSession($visitsessionid) {
    if(!session_id()) {
      session_start();
    }
   	
    $vpm = MapperFactory::getMapper("VisitedPage");
   	
    if(isset($_SESSION['user_stats'], $_SESSION['user_stats']['visited_pages']) && is_array($_SESSION['user_stats']['visited_pages']) ) {
          foreach ($_SESSION['user_stats']['visited_pages'] as $k => $vp) {
            if(isset($vp['visitedpageid'])) {
              continue;
            }
            
            $title = isset($_SESSION['titles'], $_SESSION['titles'][$vp['url']]) ? $_SESSION['titles'][$vp['url']] : null;
            if(WEBIM_ENCODING != 'UTF-8') {
              $title = smarticonv('utf-8', 'cp1251', $title);
	    }
	  		
	  		
            $visitedpageid = $vpm->save(array(
              'visitsessionid' => $visitsessionid,
      	      'uri' => $vp['url'],
      	      'referrer' => $vp['referrer'],
              'timespent' => $vp['time'],
              'pagetitle' => $title,
              'opened' => null ,
              'updated' => null ,
              'state' => VISITED_PAGE_LOADING
            )); 
            
            $_SESSION['user_stats']['visted_pages'][$k]['visitedpageid'] = $visitedpageid;
          }
    }
  }
  
  
  function GetVisitedPageById($visitedpageid) {
    $filename = self::getVistedPageFilename($visitedpageid);
    $closed_filename = self::getVistedPageFilename($visitedpageid, true);
  
    // папка online в мэмкэш --------------------
    /*if(file_exists($filename)) {
      $exists_filename = $filename;
    } else if(file_exists($closed_filename) ) {
      $exists_filename = $closed_filename;
    } else {
      return NULL;
    }

    $data = @file_get_contents($exists_filename);
    if($data === false) {
      return NULL;
    }*/
    if ( ($data = $GLOBALS['mem_buff']->get($filename)) === false ) {
        if ( ($data = $GLOBALS['mem_buff']->get($closed_filename)) === false ) {
            return NULL;
        }
    }

    $result = unserialize($data);
    if($result === false || !isset($result['visitsessionid']) || empty($result['visitsessionid'])) {
      return NULL;
    }

    return $result;
  }

  function SetInvitationState($visitedpageid, $state) {



    $visitedpage = $this->GetVisitedPageById($visitedpageid);
    MapperFactory::getMapper("Invitation")->save(array(
    	  'state' => $state,
        'invitationid' => $visitedpage['invitationid']
      )
    );
  }

  function HasPendingInvitation($visitedpageid) {


    $invitation = Invitation::GetInstance()->GetInvitationByVisitedPageId($visitedpageid);
    $thread = $this->GetInvitationThread($visitedpageid);

    return !empty($invitation) && !empty($invitation['invitemessageid']) && 
           !empty($thread) && $thread['state'] == STATE_INVITE;
  }

  function IsInChat($visitedpageid) {
    $thread = $this->GetInvitationThread($visitedpageid);
    return !empty($thread) && $thread['state'] != STATE_CLOSED;
  } 

  function GetInvitationThread($visitedpageid) {
    $invitation = Invitation::GetInstance()->GetInvitationByVisitedPageId($visitedpageid);

    if (!empty($invitation['threadid'])) {
      return Thread::getInstance()->GetThreadById($invitation['threadid']);
    }
    
    return NULL;
  }
  
    /**
     * Получить посетителей
     * 
     * Аналог retrieveVisitors только для работы с мэмкэш
     * 
     * @return bool true - успех, false - провал
     */
    function retrieveVisitorsFromMemBuff() {
        $min_delta = VISITED_PAGE_TIMEOUT;
        $max_delta = VISITED_PAGE_TIMEOUT * 3;
        
        $time = time();
        
        $this->alive_visitors = array();
        $this->dead_visitors = array();
        
        if ( !($aKeys = $GLOBALS['mem_buff']->get('TRACKER_FILES_DIR')) ) {
            return false;
        }
        
        $aKeysForeach = $aKeys;
        
        foreach ( $aKeysForeach as $sKey => $nFake ) {
        	$file = array_pop( explode(DIRECTORY_SEPARATOR, $sKey) );
        	$ext  = pathinfo( $file, PATHINFO_EXTENSION );
        	$id   = substr($file, 0, strlen($file)-strlen($ext)-1);
        	
    	    if ( ($data = $GLOBALS['mem_buff']->get($sKey)) === false ) {
    	    	unset( $aKeys[$sKey] );
    	    	continue;
    	    }
    	    
    	    $data = unserialize( $data );
            
        	if ( !empty($data['my_mtime']) && $ext == self::VISITED_PAGE_FILE_EXT && $data['my_mtime'] >= ($time - $min_delta) ) {
                $vp = $this->GetVisitedPageById( $id );
                
                if ( empty($vp) ) {
                    continue;
                }
                
                $vp['visitedpageid']       = $id;
                $this->alive_visitors[$id] = $vp;
        	}
        	elseif (
                !empty($data['my_mtime']) && 
                (( $ext == self::CLOSED_VISITED_PAGE_FILE_EXT && $data['my_mtime'] >= ($time - $min_delta) ) 
                || ( $data['my_mtime'] < ($time - $min_delta) && $data['my_mtime'] > ($time - $max_delta) ))
            ) {
                $vp = $this->GetVisitedPageById( $id );
                
                if ( empty($vp) ) {
                    continue;
                }
                
                $vp['visitedpageid'] = $id;
                
                if ( isset($this->alive_visitors[$id]) ) {
                    unset($this->alive_visitors[$id]);
                }
                
                $this->dead_visitors[$id] =$vp;
            }
            elseif ( $ext != self::VISITSESSION_FILENAME_EXT ) {
                unset( $aKeys[$sKey] );
            }
        }
        
        $GLOBALS['mem_buff']->set( 'TRACKER_FILES_DIR', $aKeys, 3600 );
        
        return true;
    }
  
  function retrieveVisitors() {
    $min_delta = VISITED_PAGE_TIMEOUT;
    $max_delta = VISITED_PAGE_TIMEOUT * 3;
  
    $time = time();
    
    $this->alive_visitors = array();
    $this->dead_visitors = array();

    $dh = @opendir(TRACKER_FILES_DIR);
    if(!$dh) 
      return false;
	
    while(($dir = readdir($dh)) !== false) {
      if($dir != "." && $dir != ".." && ($sdh = @opendir(TRACKER_FILES_DIR . DIRECTORY_SEPARATOR . $dir)) !== false) {
        while (($file = readdir($sdh)) !== false) {
          if($file != "." && $file != "..") {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
		
            $id = substr($file, 0, strlen($file)-strlen($ext)-1);
            $filename = TRACKER_FILES_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file;
            $stat = @stat($filename);
            if ($stat === false) {
              continue;
            }
            $mtime = $stat[9];
		
            if($ext == self::VISITSESSION_FILENAME_EXT) {
              if($mtime < ($time - VISITSESSION_FILE_TTL)) {
                @unlink($filename);
              }
              continue;
            }
		
            if($ext == self::VISITED_PAGE_FILE_EXT && $mtime >= ($time - $min_delta)) {
              $vp = $this->GetVisitedPageById($id);
              if (empty($vp)) {
                continue;
              }

	            $vp['visitedpageid'] = $id;
              $this->alive_visitors[$id] = $vp;
            } else if(
              ($ext == self::CLOSED_VISITED_PAGE_FILE_EXT && $mtime >= ($time - $min_delta)) ||
              ($mtime < ($time - $min_delta) && $mtime > ($time - $max_delta))  
            ) {              
              $vp = $this->GetVisitedPageById($id);
              if (empty($vp)) {
                continue;
              }

              $vp['visitedpageid'] = $id;
              if(isset($this->alive_visitors[$id])) {
                unset($this->alive_visitors[$id]);
              }
              $this->dead_visitors[$id] =$vp;
            } else {
              @unlink($filename);
            }
          }
        }
      }
    }

    return true;
  }
  
  function getAliveVisitors() {
    return array_values($this->alive_visitors);
  }
 
  function getDeadVisitors() {
    return array_values($this->dead_visitors);
  }

}
?>
