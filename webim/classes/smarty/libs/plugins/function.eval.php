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




function smarty_function_eval($params, &$smarty)
{

    if (!isset($params['var'])) {
        $smarty->trigger_error("eval: missing 'var' parameter");
        return;
    }

    if($params['var'] == '') {
        return;
    }

    $smarty->_compile_source('evaluated template', $params['var'], $_var_compiled);

    ob_start();
    $smarty->_eval('?>' . $_var_compiled);
    $_contents = ob_get_contents();
    ob_end_clean();

    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $_contents);
    } else {
        return $_contents;
    }
}



?>
