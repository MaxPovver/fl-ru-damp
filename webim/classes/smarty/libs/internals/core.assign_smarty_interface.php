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



function smarty_core_assign_smarty_interface($params, &$smarty)
{
        if (isset($smarty->_smarty_vars) && isset($smarty->_smarty_vars['request'])) {
            return;
        }

        $_globals_map = array('g'  => 'HTTP_GET_VARS',
                             'p'  => 'HTTP_POST_VARS',
                             'c'  => 'HTTP_COOKIE_VARS',
                             's'  => 'HTTP_SERVER_VARS',
                             'e'  => 'HTTP_ENV_VARS');

        $_smarty_vars_request  = array();

        foreach (preg_split('!!', strtolower($smarty->request_vars_order)) as $_c) {
            if (isset($_globals_map[$_c])) {
                $_smarty_vars_request = array_merge($_smarty_vars_request, $GLOBALS[$_globals_map[$_c]]);
            }
        }
        $_smarty_vars_request = @array_merge($_smarty_vars_request, $GLOBALS['HTTP_SESSION_VARS']);

        $smarty->_smarty_vars['request'] = $_smarty_vars_request;
}



?>
