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

class ConfigMapper extends BaseMapper {
	
	public function __construct(DBDriver $db, $model_name) {
 		parent::__construct($db, $model_name, array(), false, "configkey");	 	
  	}
  	
  	public function enumPairs() {
  	  $r = $this->getAll();
  	  $result = array();
  	  foreach($r as $v) {
  	    $result[$v['configkey']] = $v['configvalue'];
  	  }
  	  
  	  return $result;
  	}
  	
  	
}
?>