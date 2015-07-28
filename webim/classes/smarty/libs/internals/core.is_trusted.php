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




 // $resource_type, $resource_name

function smarty_core_is_trusted($params, &$smarty)
{
    $_smarty_trusted = false;
    if ($params['resource_type'] == 'file') {
        if (!empty($smarty->trusted_dir)) {
            $_rp = realpath($params['resource_name']);
            foreach ((array)$smarty->trusted_dir as $curr_dir) {
                if (!empty($curr_dir) && is_readable ($curr_dir)) {
                    $_cd = realpath($curr_dir);
                    if (strncmp($_rp, $_cd, strlen($_cd)) == 0
                        && substr($_rp, strlen($_cd), 1) == DIRECTORY_SEPARATOR ) {
                        $_smarty_trusted = true;
                        break;
                    }
                }
            }
        }

    } else {
        // resource is not on local file system
        $_smarty_trusted = call_user_func_array($smarty->_plugins['resource'][$params['resource_type']][0][3],
                                                array($params['resource_name'], $smarty));
    }

    return $_smarty_trusted;
}



?>
