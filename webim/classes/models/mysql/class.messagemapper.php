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

class MessageMapper extends BaseMapper {
	public function __construct(DBDriver $db, $model_name) {
 		parent::__construct($db, $model_name, array("created"));	 	
  	}
  	
  	public function haveMessagesToAlert($threadid, $lastid) {
  	  return count(
  	     $this->makeSearch("threadid=? AND messageid > ? AND ( kind=? OR kind=? )",
  	         array($threadid, $lastid, KIND_AGENT, KIND_USER),
  	     	 "messageid"
  	       )
  	    ) > 0;
  	}
  	
  	public function getListMessages($threadid, $sinceid, $visitor = false) {
  	   $where = "threadid = :threadid and messageid > :sinceid";
  	   $query_params = array("threadid" => $threadid, "sinceid" => $sinceid);
  	   if($visitor) {
  	     $where .= " AND kind <> :kind";
  	     $query_params['kind'] = KIND_FOR_AGENT;
  	   }
  	   
  	   return $this->makeSearch($where, 
  	     $query_params, 
  	     "messageid, kind, unix_timestamp(created) as created, sendername, message, message_additional_info"
  	     );
  	}

  	public function getFirstMessage($threadid) {
  	  $result = $this->makeSearch("threadid=?", array($threadid), null, null , 1, 0, array("created", "asc"));
  	  
  	  return array_shift($result);  
  	}
  	
    public function removeHistory($threadid) {
    try {
        $this->db->query('DELETE FROM {'.$this->getTableName().'} WHERE threadid=:threadid', $threadid);
    } catch(Exception $e) {

    }
  }
}
?>