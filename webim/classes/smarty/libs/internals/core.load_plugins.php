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




// $plugins

function smarty_core_load_plugins($params, &$smarty)
{

    foreach ($params['plugins'] as $_plugin_info) {
        list($_type, $_name, $_tpl_file, $_tpl_line, $_delayed_loading) = $_plugin_info;
        $_plugin = &$smarty->_plugins[$_type][$_name];

        
        if (isset($_plugin)) {
            if (empty($_plugin[3])) {
                if (!is_callable($_plugin[0])) {
                    $smarty->_trigger_fatal_error("[plugin] $_type '$_name' is not implemented", $_tpl_file, $_tpl_line, __FILE__, __LINE__);
                } else {
                    $_plugin[1] = $_tpl_file;
                    $_plugin[2] = $_tpl_line;
                    $_plugin[3] = true;
                    if (!isset($_plugin[4])) $_plugin[4] = true; 
                }
            }
            continue;
        } else if ($_type == 'insert') {
            
            $_plugin_func = 'insert_' . $_name;
            if (function_exists($_plugin_func)) {
                $_plugin = array($_plugin_func, $_tpl_file, $_tpl_line, true, false);
                continue;
            }
        }

        $_plugin_file = $smarty->_get_plugin_filepath($_type, $_name);

        if (! $_found = ($_plugin_file != false)) {
            $_message = "could not load plugin file '$_type.$_name.php'\n";
        }

        
        if ($_found) {
            include_once $_plugin_file;

            $_plugin_func = 'smarty_' . $_type . '_' . $_name;
            if (!function_exists($_plugin_func)) {
                $smarty->_trigger_fatal_error("[plugin] function $_plugin_func() not found in $_plugin_file", $_tpl_file, $_tpl_line, __FILE__, __LINE__);
                continue;
            }
        }
        
        else if ($_type == 'insert' && $_delayed_loading) {
            $_plugin_func = 'smarty_' . $_type . '_' . $_name;
            $_found = true;
        }

        
        if (!$_found) {
            if ($_type == 'modifier') {
                
                if ($smarty->security && !in_array($_name, $smarty->security_settings['MODIFIER_FUNCS'])) {
                    $_message = "(secure mode) modifier '$_name' is not allowed";
                } else {
                    if (!function_exists($_name)) {
                        $_message = "modifier '$_name' is not implemented";
                    } else {
                        $_plugin_func = $_name;
                        $_found = true;
                    }
                }
            } else if ($_type == 'function') {
                
                $_message = "unknown tag - '$_name'";
            }
        }

        if ($_found) {
            $smarty->_plugins[$_type][$_name] = array($_plugin_func, $_tpl_file, $_tpl_line, true, true);
        } else {
            // output error
            $smarty->_trigger_fatal_error('[plugin] ' . $_message, $_tpl_file, $_tpl_line, __FILE__, __LINE__);
        }
    }
}



?>
