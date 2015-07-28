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

require_once(dirname(__FILE__).'/../../classes/class.adminurl.php');


function smarty_function_get_admin_url($params, &$smarty) {

  return AdminURL::getInstance()->getURL($params['link_name'], NULL, isset($params['is_with_param_postfix']) ? $params['is_with_param_postfix'] == 'true' : false);
}
?>