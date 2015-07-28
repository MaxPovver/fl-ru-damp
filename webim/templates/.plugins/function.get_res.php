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
function smarty_function_get_res($params, &$smarty) {
  if (!isset($params['code'])) {
    return ''; 
  }
  return Resources::Get($params['code'], $params, $smarty->_tpl_vars['current_locale']);
}
?>