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




// $dir

function smarty_core_create_dir_structure($params, &$smarty)
{
    if (!file_exists($params['dir'])) {
        $_open_basedir_ini = ini_get('open_basedir');

        if (DIRECTORY_SEPARATOR=='/') {
            
            $_dir = $params['dir'];
            $_dir_parts = preg_split('!/+!', $_dir, -1, PREG_SPLIT_NO_EMPTY);
            $_new_dir = (substr($_dir, 0, 1)=='/') ? '/' : getcwd().'/';
            if($_use_open_basedir = !empty($_open_basedir_ini)) {
                $_open_basedirs = explode(':', $_open_basedir_ini);
            }

        } else {
            
            $_dir = str_replace('\\','/', $params['dir']);
            $_dir_parts = preg_split('!/+!', $_dir, -1, PREG_SPLIT_NO_EMPTY);
            if (preg_match('!^((//)|([a-zA-Z]:/))!', $_dir, $_root_dir)) {
                
                $_new_dir = $_root_dir[1];
                
                if (isset($_root_dir[3])) array_shift($_dir_parts);

            } else {
                $_new_dir = str_replace('\\', '/', getcwd()).'/';

            }

            if($_use_open_basedir = !empty($_open_basedir_ini)) {
                $_open_basedirs = explode(';', str_replace('\\', '/', $_open_basedir_ini));
            }

        }

        
        foreach ($_dir_parts as $_dir_part) {
            $_new_dir .= $_dir_part;

            if ($_use_open_basedir) {
                // do not attempt to test or make directories outside of open_basedir
                $_make_new_dir = false;
                foreach ($_open_basedirs as $_open_basedir) {
                    if (substr($_new_dir, 0, strlen($_open_basedir)) == $_open_basedir) {
                        $_make_new_dir = true;
                        break;
                    }
                }
            } else {
                $_make_new_dir = true;
            }

            if ($_make_new_dir && !file_exists($_new_dir) && !@mkdir($_new_dir, $smarty->_dir_perms) && !is_dir($_new_dir)) {
                $smarty->trigger_error("problem creating directory '" . $_new_dir . "'");
                return false;
            }
            $_new_dir .= '/';
        }
    }
}



?>
