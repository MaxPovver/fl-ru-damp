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

class DepartmentMapper extends BaseMapper {
  public function enumDepartments($locale) { // TODO check for oracle inner vs outer joins
    $sql = "SELECT dl.*, d.* FROM {".$this->getTableName()."} d 
        LEFT OUTER JOIN {departmentlocale} dl
        ON d.departmentid=dl.departmentid 
        AND locale=:locale";
    try {
        $this->db->Query($sql, array('locale'=>$locale));
         return $this->db->getArrayOfRows(); 
    } catch (Exception $e) {

        return array();
    }
  }
  
  public function getByDepartmentKey($key) {
      return array_shift($r = $this->makeSearch("departmentkey = ?", $key, null, 1));  
  }
  
  public function departmentsExist() {
    $sql = "SELECT * FROM {".$this->getTableName()."} LIMIT 1";
    try {
        $this->db->Query($sql);
         return $this->db->getNumRows() > 0; 
    } catch (Exception $e) {

        return false;
    }
  }
  
}
?>