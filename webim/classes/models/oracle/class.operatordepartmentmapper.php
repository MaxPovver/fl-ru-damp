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

class OperatorDepartmentMapper extends BaseMapper {
  function deleteDepartment($departmentid) {
    $this->db->Query('DELETE FROM "{operatordepartment}" WHERE "departmentid" = :id', array("id" => $departmentid));
  }
  
  function deleteByOperatorId($operatorid) {
    $this->db->Query('DELETE FROM "{operatordepartment}" WHERE "operatorid" = :id', array("id" => $operatorid));
  }
  
  function hasDepartments($operatorid) {
      $query = 'SELECT COUNT(*) "cnt" FROM "{' . $this->getTableName() . '}" WHERE "operatorid"=:id';
    
      try {
        $this->db->Query($query, array("id" => $operatorid));
      } catch (Exception $e) {

        $total = 0;
      }
      
      $this->db->nextRecord();
      $row = $this->db->getRow();
      return $row['cnt'] > 0;
  }
  
  function enumDepartmentsWithOperator($operatorid, $locale) {
    $sql = '
    	SELECT d.*, dl.*,
    	CASE
    		WHEN EXISTS(
    				SELECT * 
					FROM "{operatordepartment}" od 
       				WHERE 
       					od."operatorid"=:operatorid AND od."departmentid"=d."departmentid"
       		) THEN 1
       		ELSE 0
       	END  "isindepartment" 
      FROM 
      	"{department}" d 
      INNER JOIN 
      	"{departmentlocale}" dl 
      ON d."departmentid"=dl."departmentid" 
      WHERE "locale"=:locale';
     
    try {
        $this->db->Query($sql, array('locale' => $locale, 'operatorid' => $operatorid));
    } catch (Exception $e) {

        return array();
    }
    
    return $this->db->getArrayOfRows(); 
  }

//  function enumDepartmentsWithOnllineStatus($locale) {
//    $sql = "SELECT *, 
//      EXISTS(SELECT * FROM {operatordepartment} od 
//        INNER JOIN {operatorlastaccess} la ON od.operatorid=la.operatorid 
//        WHERE la.status = :status 
//        AND od.departmentid=d.departmentid
//        AND (WM_UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - WM_UNIX_TIMESTAMP(la.lastvisited)) < :delta) as isonline
//      FROM {department} d 
//      INNER JOIN {departmentlocale} dl ON d.departmentid=dl.departmentid 
//      WHERE locale=:locale";
//    try {
//      $this->db->Query($sql, array('status' => OPERATOR_STATUS_ONLINE, 
//        'delta' => ONLINE_TIMEOUT, 
//        'locale' => $locale));
//      return  $this->db->getArrayOfRows();
//    } catch (Exception $e) {

//      return array();
//  
//    }
//  }

  function enumOnlineDepartments($locale) {
    $sql = "SELECT *
      FROM {department} d 
      INNER JOIN {operatordepartment} od 
      ON od.departmentid = d.departmentid
      INNER JOIN {operatorlastaccess} la 
      ON od.operatorid=la.operatorid 
      INNER JOIN {departmentlocale} dl 
      ON d.departmentid=dl.departmentid 
      WHERE la.status = :status 
      AND od.departmentid=d.departmentid
      AND WM_UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - WM_UNIX_TIMESTAMP(la.lastvisited) < :delta
      AND locale=:locale";
    try {
      $this->db->Query($sql, array('status' => OPERATOR_STATUS_ONLINE, 
        'delta' => ONLINE_TIMEOUT, 
        'locale' => $locale));
      return  $this->db->getArrayOfRows();
    } catch (Exception $e) {

      return array();
    }
  }

  public function enumAvailableDepartmentsForOperator($operatorId, $locale, $shouldCheckDepartments) {
    $params = array();
    
    if ($shouldCheckDepartments) {
      $departmentsSql = '
      	AND EXISTS(
      		SELECT * FROM "{'.$this->getTableName().'}" od 
      		WHERE od."operatorid"=:operatorid AND od."departmentid"=d."departmentid"
      	)';
      $params['operatorid'] = $operatorId;
    } else {
      $departmentsSql = '';
    }
    
    $sql = '
    		SELECT * FROM "{department}" d 
    		INNER JOIN "{departmentlocale}" dl 
          	ON d."departmentid" = dl."departmentid" 
          	WHERE "locale" = :locale ' . $departmentsSql;
    
    $params['locale'] = $locale;
    
    try {
        $this->db->Query($sql, $params);
        return $this->db->getArrayOfRows(); 
    } catch (Exception $e) {

        return array();
    }
  }
  
  public function isOperatorInDepartment($operatorid, $departmentid) {
    try {
        $this->db->Query('SELECT * FROM "' . $this->getTableName() . '" WHERE "operatorid"=:operatorid AND "departmentid"=:departmentid'
                , array('operatorid' => $operatorid, 'departmentid' => $departmentid));
        return $this->db->getNumRows() > 0; 
    } catch (Exception $e) {

        return false;
    }
  }
  
  public function enumDepartmentKeysByOperator($operatorid) {
  	$query = '
  		SELECT 
  			d."departmentkey", d."departmentid"
  		FROM  
  			"{operatordepartment}" od
  		INNER JOIN
  			"{department}" d
  		ON
  			od."departmentid" = d."departmentid"
  		WHERE
  			od."operatorid" = :operatorid
  		';
  	try {
  		$this->db->Query($query, array("operatorid" => $operatorid));
  		return $this->db->getArrayOfRows(); 
  	} catch (Exception $e) {

  		return false;
  	}
  	
  }
}
?>