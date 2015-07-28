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



function smarty_core_assemble_plugin_filepath($params, &$smarty)
{
    static $_filepaths_cache = array();

    $_plugin_filename = $params['type'] . '.' . $params['name'] . '.php';
    if (isset($_filepaths_cache[$_plugin_filename])) {
        return $_filepaths_cache[$_plugin_filename];
    }
    $_return = false;

    foreach ((array)$smarty->plugins_dir as $_plugin_dir) {

        $_plugin_filepath = $_plugin_dir . DIRECTORY_SEPARATOR . $_plugin_filename;

        // see if path is relative
        if (!preg_match("/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/", $_plugin_dir)) {
            $_relative_paths[] = $_plugin_dir;
            // relative path, see if it is in the SMARTY_DIR
            if (@is_readable(SMARTY_DIR . $_plugin_filepath)) {
                $_return = SMARTY_DIR . $_plugin_filepath;
                break;
            }
        }
        // try relative to cwd (or absolute)
        if (@is_readable($_plugin_filepath)) {
            $_return = $_plugin_filepath;
            break;
        }
    }

    if($_return === false) {
        // still not found, try PHP include_path
        if(isset($_relative_paths)) {
            foreach ((array)$_relative_paths as $_plugin_dir) {

                $_plugin_filepath = $_plugin_dir . DIRECTORY_SEPARATOR . $_plugin_filename;

                $_params = array('file_path' => $_plugin_filepath);
                require_once(SMARTY_CORE_DIR . 'core.get_include_path.php');
                if(smarty_core_get_include_path($_params, $smarty)) {
                    $_return = $_params['new_file_path'];
                    break;
                }
            }
        }
    }
    $_filepaths_cache[$_plugin_filename] = $_return;
    return $_return;
}



?>
