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

class InvitationMapper extends BaseMapper {
	public function __construct(DBDriver $db, $model_name) {
		parent::__construct($db, $model_name, array());
	}
  	
  	public function updateInvitationMessageByThreadId($threadid, $invitemessageid) {
  	   $query = "UPDATE {invitation} SET invitemessageid=? WHERE threadid=?";
  	   try {
  	     $this->db->Query($query, array($invitemessageid, $threadid));
  	     return true;
  	   } catch (Exception $e) {

  	     return false;
  	   }
  	}
  	
  	public function getByVisitedPageId($visitedpageid) {
  	  $sql = "SELECT i.*
              FROM {invitation} as i
              LEFT JOIN {visitedpage} as p
              ON 
                p.invitationid = i.invitationid
              WHERE
                p.visitedpageid = ?";
      try {
      	$this->db->Query($sql, $visitedpageid);
      	$this->db->nextRecord();
      	return $this->db->getRow();
      } catch (Exception $e) {

      	return null;
      }
  	}
}
?>