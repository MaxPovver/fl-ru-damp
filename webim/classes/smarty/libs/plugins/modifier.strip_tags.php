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




function smarty_modifier_strip_tags($string, $replace_with_space = true)
{
    if ($replace_with_space)
        return preg_replace('!<[^>]*?>!', ' ', $string);
    else
        return strip_tags($string);
}



?>
