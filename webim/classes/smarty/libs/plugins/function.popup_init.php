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




function smarty_function_popup_init($params, &$smarty)
{
    $zindex = 1000;
    
    if (!empty($params['zindex'])) {
        $zindex = $params['zindex'];
    }
    
    if (!empty($params['src'])) {
        return '<div id="overDiv" style="position:absolute; visibility:hidden; z-index:'.$zindex.';"></div>' . "\n"
         . '<script type="text/javascript" language="JavaScript" src="'.$params['src'].'"></script>' . "\n";
    } else {
        $smarty->trigger_error("popup_init: missing src parameter");
    }
}



?>
