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

class BanMapper extends BaseMapper {
	public function __construct(DBDriver $db, $model_name) {
 		parent::__construct($db, $model_name, array("created", "till"));	 	
  	}
  	
  	public function getBanBydAddress($address) {
  	  return array_shift(
  	    $this->makeSearch('"address" = :address AND WM_UNIX_TIMESTAMP("till") > WM_UNIX_TIMESTAMP(SYSDATE)',
  	      array("address" => $address), 
  	      null, 
  	      1)
  	    );
  	}
  	public function isBanned($address) {
      $ban = $this->getBanBydAddress($address);
      return !empty($ban);
  	}
}
?>