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

class DepartmentLocaleMapper extends BaseMapper {
  function getDepartmentLocale($departmentid, $locale) {
      return array_shift($r = $this->makeSearch("departmentid = ? AND locale = ?", array($departmentid, $locale), null, 1));  
  }
}
?>