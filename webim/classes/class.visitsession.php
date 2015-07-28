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
require_once('class.visitedpage.php');

define("VISIT_SESSION_TIMEOUT", 300); // seconds

class VisitSession {
  private static $instance = NULL;

  static function GetInstance() {
    if (self::$instance == NULL) {
      self::$instance = new VisitSession();
    }
    return self::$instance;
  }

  private function createVisitSession($ip, $remotehost, $useragent, $userid, $visitorid, $visitorname, $fllogin, $partnerref) {
    $hashTable = array(
      'ip' => $ip,
      'remotehost' => $remotehost,
      'useragent' => $useragent,
      'userid' => $userid,
      'visitorid' => $visitorid,
      'visitorname' => $visitorname,
      'fl_login' => $fllogin,
      'created' => null ,
      'updated' => null ,
      'partnerref' => $partnerref
       
    );
    
    return MapperFactory::getMapper("VisitSession")->save($hashTable);
  }

  private function getActiveSessionForVisitor($visitorid) {
    return MapperFactory::getMapper("VisitSession")->getActiveSessionForVisitor($visitorid);
  }
  
  function UpdateVisitSession($visitsessionid, $params = array()) {
    $paramsHash = array('updated' => null , 'visitsessionid' => $visitsessionid);
    $paramsHash = array_merge($paramsHash, $params);
    MapperFactory::getMapper("VisitSession")->save($paramsHash);
  }

  function UnsetVisitSession() {
    unset($_SESSION['visitsessionid']);
  }

  private static function isValidSession($session) {
    return isset($session) && isset($session['visitsessionid']) && 
           isset($session['updated']) && 
           ((getCurrentTime() - $session['updated']) < VISIT_SESSION_TIMEOUT);
  }

   

  private function getCurrentSession() {
    $session = null;

    if (isset($_SESSION['WEBIM_VISIT_SESSION_ID']) && !empty($_SESSION['WEBIM_VISIT_SESSION_ID'])) {
      $session = $this->GetVisitSessionById($_SESSION['WEBIM_VISIT_SESSION_ID']);
    }

    if (empty($session) || !self::isValidSession($session)) {
      $visitorInfo = GetVisitorFromRequestAndSetCookie();
      $session = $this->getActiveSessionForVisitor($visitorInfo['id']);
    }
    return $session;
  }

  // FIXME: check the case when visitor opened several chat windows from different installations
  function updateCurrentOrCreateSession() {

    $session = $this->getCurrentSession();    

    $visitorInfo = GetVisitorFromRequestAndSetCookie();
    $fl_login    = isset( $_SESSION['login'] ) ? $_SESSION['login'] : '';
    
    if (empty($session)) {
      $ip = Browser::GetExtAddr();
      $remoteHost = isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : null;
      $useragent  = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null; 

      $userid = null;
      
      $sessionId = $this->createVisitSession($ip, $remoteHost, $useragent, $userid,
                                             $visitorInfo['id'], $visitorInfo['name'], $fl_login, $visitorInfo['partnerref']);
    } else {      
      $sessionId = is_array($session)? $session['visitsessionid']: $session;
      $this->UpdateVisitSession( $sessionId, array('fl_login' => $fl_login, 'visitorname' => $visitorInfo['name']) );
    } 
    
    VisitedPage::GetInstance()->addOrUpdateFromSession($sessionId);
    $_SESSION['WEBIM_VISIT_SESSION_ID'] = $sessionId;
    return $sessionId;
  }

  function GetVisitSessionByPageId($pageid) {
    $vistedpage = VisitedPage::GetInstance()->GetVisitedPageById($pageid);
    return VisitSession::GetInstance()->GetVisitSessionById($vistedpage['visitsessionid']);
  }

  function GetVisitSessionTime($visitSessionId) {
    return MapperFactory::getMapper("VisitedPage")->getVisitTimeByVisitSessionId($visitSessionId);
  }

  function EnumVisitedPagesBySessionId($visitSessionId) {
    return MapperFactory::getMapper("VisitedPage")->enumByVisitSessionId($visitSessionId);
  }

  function GetVisitSessionById($visitsessionid) {
    return MapperFactory::getMapper("VisitSession")->getById($visitsessionid);
  }

   

  static function GetPartnerReference($visitSessionId) { // TODO do we really need it?
    $visitSession = self::GetInstance()->GetVisitSessionById($visitSessionId);
    return isset($visitSession) ? $visitSession['partnerref'] : null;
  }
  
  public function GetFirstPage($visitSessionId) {
      $row = MapperFactory::getMapper("VisitedPage")->getFirstBySessionId($visitSessionId);

      return $row;
  }

  protected static function getVisitorCurrentPageFilename($visitorid) {
    return ONLINE_FILES_DIR . DIRECTORY_SEPARATOR .
      substr(md5($visitorid), 0, 1) . DIRECTORY_SEPARATOR . 
      md5($visitorid) . ".current_page";
  }
  
    public function setVisitSessionCurrentPage($visitorid, $url, $title) {
        $file = self::getVisitorCurrentPageFilename($visitorid);
        
        // папка online в мэмкэш --------------------
        //create_basedir( $file );
        //file_put_contents( $file, serialize(array($url, $title)), LOCK_EX );
        $GLOBALS['mem_buff']->set( $file, serialize(array($url, $title)), 3600 * 6 );
    }
  
  public function deleteVisitSessionCurrentPageFile($visitorid) {
    $file = self::getVisitorCurrentPageFilename($visitorid);
    
    // папка online в мэмкэш --------------------
    /*if(file_exists($file)) {
      unlink($file);  
    }*/
    $GLOBALS['mem_buff']->delete( $file );
  }
  
  public function getVisitSessionCurrentPage($visitorid) {
    $file = self::getVisitorCurrentPageFilename($visitorid);
    
    // папка online в мэмкэш --------------------
    /*if(!file_exists($file))
      return null;
      
    $str = file_get_contents($file);*/
    $str = $GLOBALS['mem_buff']->get( $file );
    
    if(empty($str))
      return null;
      
    $data = @unserialize($str);
    if(!is_array($data) || count($data) != 2)
      return null;
     
    return $data;
  }
}
?>
