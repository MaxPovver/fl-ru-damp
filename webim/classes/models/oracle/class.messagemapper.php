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
      $this->makeSearch('threadid=:threadid AND messageid > :messageid AND ( kind=:kind_agent OR kind=:kind_user )',
          array("threadid" => $threadid, "lastid" => $lastid, "kind_agent" => KIND_AGENT, "kind_user" => KIND_USER),
      	 "messageid"
      )) > 0;
  }
  
  public function getListMessages($threadid, $sinceid, $visitor = false) {
    

    
    $where = '"threadid" = :threadid and "messageid" > :sinceid';
    $query_params = array("threadid" => $threadid, "sinceid" => $sinceid);
    if($visitor) {
      $where .= ' AND "kind" <> :kind';
      $query_params['kind'] = KIND_FOR_AGENT;
    }
     

    return $this->makeSearch($where, 
      $query_params, 
      '"messageid", "kind", WM_UNIX_TIMESTAMP("created") "created", "sendername", "message"',
      null, null, null, array('t."messageid"', "asc")
    );
  }

  public function getFirstMessage($threadid) {
    $result = $this->makeSearch('"threadid"=:threadid', array("threadid" => $threadid), null, null , 1, 0, array('t."created"', "asc"));
    
    return array_shift($result);  
  }
  	
  public function removeHistory($threadid) {
    try {
      $this->db->query('DELETE FROM "{'.$this->getTableName().'}" WHERE "threadid"=:threadid', array("threadid" => $threadid));
    } catch(Exception $e) {

    }
  }
}
?>