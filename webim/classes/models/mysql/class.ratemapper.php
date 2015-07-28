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

class RateMapper extends BaseMapper {
    
  public function __construct(DBDriver $db, $model_name) {
 		parent::__construct($db, $model_name, array('date','deldate'));	
  }
   
  public function getByThreadidWithOperator($threadid) {
    $query = "
    			SELECT r.*, o.fullname as operator, ".$this->getDateColumnsSql('r')." 
                FROM {rate} as r INNER JOIN {operator} as o 
    			ON r.operatorid = o.operatorid 
          WHERE r.threadid = ?
    			  AND r.deldate IS NULL
    			ORDER BY r.date";

    try {
      $this->db->Query($query, $threadid);
      return  $this->db->getArrayOfRows();
    } catch (Exception $e) {

      return array();
    }		  
  }
  
  public function getByThreadid($threadid) {
     return $this->makeSearch("threadid = ?", $threadid, null, null, null, null, array("date", "ASC"));
  }    
    
  public function getByThreadidAndOperatorid($threadid, $operatorid) { 
    $r = $this->makeSearch(
    		"threadid = ? AND operatorid = ?",  
            array($threadid, $operatorid)
    );
  	return array_shift($r);
  }

  public function removeRate($rateid) {
    $sql = '
    	UPDATE {rate} SET deldate=NOW() WHERE rateid=:rateid
    ';
    
    try {
      $this->db->Query($sql, array("rateid" => $rateid));
      return  $this->db->getArrayOfRows() > 0;
    } catch (Exception $e) {

      return array();
    }
  }
}

?>
