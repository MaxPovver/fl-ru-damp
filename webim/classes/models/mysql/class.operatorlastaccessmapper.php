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

class OperatorLastAccessMapper extends BaseMapper {
	
	public function __construct(DBDriver $db, $model_name) {
 		parent::__construct($db, $model_name, array("lastvisited"), false, "operatorid");	 	
  	}
  	  	
  	public function countOnlineOperators($departmentkey = null, $locale = null) {
      $hash = array('status' => OPERATOR_STATUS_ONLINE, 'delta' => ONLINE_TIMEOUT);
      
      if (!empty($departmentkey)) {
        $departmentsql = " AND (NOT EXISTS (SELECT * FROM {department}) OR EXISTS (SELECT * FROM {operatordepartment} od INNER JOIN {department} d ON d.departmentid=od.departmentid WHERE od.operatorid=la.operatorid AND d.departmentkey=:departmentkey))";
        $hash['departmentkey'] = $departmentkey;
      } else {
        $departmentsql = '';
      }

      if (!empty($locale) && preg_match('/^\w+$/', $locale)) {
        $localesql = " AND (locales IS NULL OR locales LIKE '%".$locale."%')"; //TODO  use placeholders
      } else {
        $localesql = '';
      }
      
      // TODO make it work for bitrix
      $sql = "SELECT COUNT(*) as cnt FROM {".$this->getTableName()."} la 
              WHERE status = :status 
              AND (unix_timestamp(CURRENT_TIMESTAMP) - unix_timestamp(lastvisited)) < :delta ".$localesql.$departmentsql;
      
      try {        
        $this->db->Query($sql, $hash);
        $this->db->nextRecord();
        $cnt = $this->db->getRow('cnt');
        
        return $cnt;
      } catch (Exception $e) {

        return array();
      }  
  	}
    
    public function getOnlineOperatorIdsWithDepartments($locale) {
      // TODO won't work for bitrrix
      $sql = "SELECT o.operatorid, d.*, dl.*   
            FROM {operatorlastaccess} as la
            INNER JOIN {operator} o
            ON la.operatorid = o.operatorid
            LEFT OUTER JOIN {operatordepartment} od
            ON o.operatorid = od.operatorid
            INNER JOIN 
            {department} d
            ON d.departmentid=od.departmentid
            INNER JOIN {departmentlocale} dl
            ON dl.departmentid=d.departmentid
          WHERE
            dl.locale=:locale
            AND la.status = :status 
            AND (unix_timestamp(CURRENT_TIMESTAMP) - unix_timestamp(la.lastvisited)) < :delta
          ORDER BY d.departmentid, o.operatorid";
          
      try {
        $this->db->Query($sql, 
        array('status' => OPERATOR_STATUS_ONLINE, 
              'delta' => ONLINE_TIMEOUT, 
              'locale' => $locale));
              
        $arr = $this->db->getArrayOfRows();
        

        return $arr;
      } catch (Exception $e) {

        return array();
      }  
    }
  	
  	public function getOnlineOperatorIds($operatorIdToSkip = null) {
  	  $query = "SELECT operatorid   
    			FROM {operatorlastaccess} as la 
    			WHERE
    			la.status = :status 
          AND (unix_timestamp(CURRENT_TIMESTAMP) - unix_timestamp(la.lastvisited)) < :delta";

      $query .= " AND (la.operatorid <> :operatorid OR :operatorid IS NULL);"; 

      try {
        $this->db->Query($query, 
        array('status' => OPERATOR_STATUS_ONLINE, 
              'delta' => ONLINE_TIMEOUT, 
              'operatorid' => $operatorIdToSkip));
              
        $arr = $this->db->getArrayOfRows();
        $ids = array();
        foreach ($arr as $r) {
          $ids[] = $r['operatorid'];
        }
        return $ids;
      } catch (Exception $e) {

        return array();
      }	
  	}
}
?>