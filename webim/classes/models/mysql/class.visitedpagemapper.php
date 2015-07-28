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

class VisitedPageMapper extends BaseMapper {
	
	public function __construct(DBDriver $db, $model_name) {
 		parent::__construct($db, $model_name, array("opened", "updated"));	 	
  	}
  	
  	public function getVisitTimeByVisitSessionId($visitsessionid) {
  	  return $this->makeSearch("visitsessionid=?", 
  	    $visitsessionid,
  	    "max(unix_timestamp(updated)) as timeend,
         min(unix_timestamp(opened)) as timestart,
         unix_timestamp(CURRENT_TIMESTAMP) as curtime,
         max(unix_timestamp(updated)) - min(unix_timestamp(opened)) as diff"
  	  );
  	}
  	
  	public function enumByVisitSessionId($visitsessionid) {
  	  return $this->makeSearch("visitsessionid=?",
  	    $visitsessionid,
  	    "uri,
         referrer,
         unix_timestamp(updated) as updatedtime,
         unix_timestamp(opened) as openedtime,
         (unix_timestamp(updated) - unix_timestamp(opened)) as sessionduration
        "
  	  );
  	}
  	
  	public function getFirstBySessionId($visitsessionid) {
  	  $sql = "
  	  			SELECT
                    *,
                    unix_timestamp(opened) as tsopened,
                    unix_timestamp(updated) as tsupdated,
                    unix_timestamp(CURRENT_TIMESTAMP) as current
                FROM {visitedpage}
                WHERE
                    visitsessionid = ?
                HAVING 
                    opened = min(opened)
  	  ";
  	  
  	  try {
  	  	$this->db->Query($sql,$visitsessionid);
  	  	$this->db->nextRecord();
  	  	return $this->db->getRow();
  	  } catch (Exception $e) {

  	  	return null;
  	  }
  	}
  	
}
?>