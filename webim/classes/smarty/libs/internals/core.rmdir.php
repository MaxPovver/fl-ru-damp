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




//  $dirname, $level = 1, $exp_time = null

function smarty_core_rmdir($params, &$smarty)
{
   if(!isset($params['level'])) { $params['level'] = 1; }
   if(!isset($params['exp_time'])) { $params['exp_time'] = null; }

   if($_handle = @opendir($params['dirname'])) {

        while (false !== ($_entry = readdir($_handle))) {
            if ($_entry != '.' && $_entry != '..') {
                if (@is_dir($params['dirname'] . DIRECTORY_SEPARATOR . $_entry)) {
                    $_params = array(
                        'dirname' => $params['dirname'] . DIRECTORY_SEPARATOR . $_entry,
                        'level' => $params['level'] + 1,
                        'exp_time' => $params['exp_time']
                    );
                    smarty_core_rmdir($_params, $smarty);
                }
                else {
                    $smarty->_unlink($params['dirname'] . DIRECTORY_SEPARATOR . $_entry, $params['exp_time']);
                }
            }
        }
        closedir($_handle);
   }

   if ($params['level']) {
       return @rmdir($params['dirname']);
   }
   return (bool)$_handle;

}



?>
