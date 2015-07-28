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




function smarty_function_debug($params, &$smarty)
{
    if (isset($params['output'])) {
        $smarty->assign('_smarty_debug_output', $params['output']);
    }
    require_once(SMARTY_CORE_DIR . 'core.display_debug_console.php');
    return smarty_core_display_debug_console(null, $smarty);
}



?>
