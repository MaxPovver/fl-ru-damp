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
require_once('models/generic/class.mapperfactory.php');

class Department {
  private static $instance = NULL;

  static function getInstance() {
    if (self::$instance == NULL) {
      self::$instance = new Department();
    }
    return self::$instance;
  }

  private function __construct() {
  }
  
  private function __clone() {
  }

  function save($hash, $locale) {

    $d['departmentkey'] = $hash['departmentkey'];
    $dl = array('departmentname' => $hash['departmentname'], 'locale' => $locale);
    
    $id = null;
    
    if (isset($hash['departmentid'])) { // existing department
      $d['departmentid'] = $hash['departmentid'];
      $id = $d['departmentid'];
      
      MapperFactory::getMapper("Department")->save($d);
    } else { // new department
      $id = MapperFactory::getMapper("Department")->save($d);

    }

    // check if locale exists
    $localeid = MapperFactory::getMapper("DepartmentLocale")->getDepartmentLocale($id, $locale);

    
    if (!empty($localeid)) {
      $dl['departmentlocaleid'] = $localeid['departmentlocaleid'];
    } 

    $dl['departmentid'] = $id;

    MapperFactory::getMapper("DepartmentLocale")->save($dl);
    
    return $id;
  }
  
  
  function getById($id, $locale) {
    $hash1 = MapperFactory::getMapper("Department")->getById($id);
    $hash2 = MapperFactory::getMapper("DepartmentLocale")->getDepartmentLocale($id, $locale);
    if (empty($hash2)) {
      return $hash1;
    } else {
      return array_merge($hash1, $hash2);
    }
  }

  function deleteDepartment($id) {
    $dl = MapperFactory::getMapper("DepartmentLocale")->getDepartmentLocale($id, $locale);
    MapperFactory::getMapper("Department")->delete($dl['departmentlocaleid']);

    MapperFactory::getMapper("DepartmentLocale")->delete($id);

    $od = MapperFactory::getMapper("OperatorDepartment")->deleteDepartment($id);
  }
  
}
?>