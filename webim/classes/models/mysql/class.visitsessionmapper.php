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
require_once (dirname(__FILE__) . '/class.basemapper.php');

class VisitSessionMapper extends BaseMapper {
  
	public function __construct(DBDriver $db, $model_name) {
 		parent::__construct($db, $model_name, array("created", "updated"));
 	}
  	
  	public function getAliveVisitors() {
  	  return $this->getVisitors();
  	}
    
  	public function getDeadVisitors() {
  	  return $this->getVisitors(true);
  	}
  	
    private function getVisitors($dead = false) {
      $min_delta = VISITED_PAGE_TIMEOUT;
      $max_delta = VISITED_PAGE_TIMEOUT * 3;
    
      $sql = "
                SELECT
                    s.ip,
                    s.useragent,
                    s.visitorid,
                    s.visitorname,
                    s.visitsessionid,
                    s.bitrixsessionid,
                    p.visitedpageid,
                    p.uri,
                    p.state,
                    p.referrer,
                    UNIX_TIMESTAMP(p.opened) as opened,
                    UNIX_TIMESTAMP(p.updated) as updated                    
                FROM 
                    {visitsession} as s, 
                    {visitedpage} as p
                WHERE 
                    s.visitsessionid = p.visitsessionid
                    AND";
      if(!$dead) {                
        $sql .= "   (
                      p.state = :state
                      AND 
                      p.updated >= FROM_UNIXTIME(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - :maxdelta)
                    )";
      } else {
        $sql .= "   (
                      (
                         p.state<>:state
                         AND 
                         p.updated >= FROM_UNIXTIME(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - :mindelta)
                      )
                      OR 
                      (
                         p.updated < FROM_UNIXTIME(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - :mindelta)
                         AND 
                         p.updated > FROM_UNIXTIME(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - :maxdelta)
                      )
                    )";
      }
      $sql .= " ORDER BY opened ASC";
      try {
        $this->db->Query($sql, array("state" => VISITED_PAGE_OPENED, "mindelta" => $min_delta, "maxdelta" => $max_delta));
        return $this->db->getArrayOfRows();
      } catch (Exception $e) {

        return array();
      }
    }
  	
    public function getByVisitedPageId($visitedpageid) {
  	  $sql = "
      			SELECT s.*, ".$this->getDateColumnsSql("s")."
      			FROM {visitsession} as s
          		LEFT JOIN {visitedpage} as p
      			ON s.visitsessionid = p.visitsessionid
      			WHERE p.visitedpageid=?";
  	  
  	  try {
  	  	$this->db->Query($sql,$visitedpageid);
  	  	$this->db->nextRecord();
  	  	return $this->db->getRow();
  	  } catch (Exception $e) {

  	  	return null;
  	  }
  	}
  	
    public function getActiveSessionForVisitor($visitorid) {
      $result = array_shift($r = $this->makeSearch(
        	"visitorid = ?
      		AND(unix_timestamp(CURRENT_TIMESTAMP)-unix_timestamp(updated)) < ?",
                array($visitorid, VISIT_SESSION_TIMEOUT),
        	null,
                1
        )
      );

      return $result ? $result['visitsessionid'] : null;
    }
    
    public function getByVisitorId($visitorid) {
      return array_shift($r = $this->makeSearch(
      		"visitorid = ?",
            $visitorid, 
            null,
            1,
            null,
            null,
            array("updated", "DESC")
         )
      );
    }

    public function cleanupVisitLogs() {

      $keepingDataPeriod = 2 * 24 * 60 * 60; // seconds 

      $sql = 'DELETE  
                {visitedpage}
              FROM
                {visitsession}, {visitedpage}
              WHERE 
                ({visitsession}.visitsessionid = {visitedpage}.visitsessionid)
                AND {visitsession}.hasthread = 0
                AND {visitsession}.updated < FROM_UNIXTIME(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - ' . $keepingDataPeriod . ')';

      try {
        $this->db->Query($sql);
      } catch (Exception $e) {

      }

      $sql = 'DELETE
                {visitsession}
              FROM
                {visitsession}, {thread}
              WHERE 
                {visitsession}.hasthread = 0
                AND {visitsession}.updated < FROM_UNIXTIME(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - ' . $keepingDataPeriod . ')';

      try {
        $this->db->Query($sql);
      } catch (Exception $e) {

      }
    }
}
?>