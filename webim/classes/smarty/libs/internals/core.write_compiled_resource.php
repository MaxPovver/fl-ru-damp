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



function smarty_core_write_compiled_resource($params, &$smarty)
{
    if(!@is_writable($smarty->compile_dir)) {
        // compile_dir not writable, see if it exists
        if(!@is_dir($smarty->compile_dir)) {
            $smarty->trigger_error('the $compile_dir \'' . $smarty->compile_dir . '\' does not exist, or is not a directory.', E_USER_ERROR);
            return false;
        }
        $smarty->trigger_error('unable to write to $compile_dir \'' . realpath($smarty->compile_dir) . '\'. Be sure $compile_dir is writable by the web server user.', E_USER_ERROR);
        return false;
    }

    $_params = array('filename' => $params['compile_path'], 'contents' => $params['compiled_content'], 'create_dirs' => true);
    require_once(SMARTY_CORE_DIR . 'core.write_file.php');
    smarty_core_write_file($_params, $smarty);
    return true;
}



?>
