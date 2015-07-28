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




function smarty_function_html_radios($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');
   
    $name = 'radio';
    $values = null;
    $options = null;
    $selected = null;
    $separator = '';
    $labels = true;
    $label_ids = false;
    $output = null;
    $extra = '';

    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'name':
            case 'separator':
                $$_key = (string)$_val;
                break;

            case 'checked':
            case 'selected':
                if(is_array($_val)) {
                    $smarty->trigger_error('html_radios: the "' . $_key . '" attribute cannot be an array', E_USER_WARNING);
                } else {
                    $selected = (string)$_val;
                }
                break;

            case 'labels':
            case 'label_ids':
                $$_key = (bool)$_val;
                break;

            case 'options':
                $$_key = (array)$_val;
                break;

            case 'values':
            case 'output':
                $$_key = array_values((array)$_val);
                break;

            case 'radios':
                $smarty->trigger_error('html_radios: the use of the "radios" attribute is deprecated, use "options" instead', E_USER_WARNING);
                $options = (array)$_val;
                break;

            case 'assign':
                break;

            default:
                if(!is_array($_val)) {
                    $extra .= ' '.$_key.'="'.smarty_function_escape_special_chars($_val).'"';
                } else {
                    $smarty->trigger_error("html_radios: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if (!isset($options) && !isset($values))
        return ''; 

    $_html_result = array();

    if (isset($options)) {

        foreach ($options as $_key=>$_val)
            $_html_result[] = smarty_function_html_radios_output($name, $_key, $_val, $selected, $extra, $separator, $labels, $label_ids);

    } else {

        foreach ($values as $_i=>$_key) {
            $_val = isset($output[$_i]) ? $output[$_i] : '';
            $_html_result[] = smarty_function_html_radios_output($name, $_key, $_val, $selected, $extra, $separator, $labels, $label_ids);
        }

    }

    if(!empty($params['assign'])) {
        $smarty->assign($params['assign'], $_html_result);
    } else {
        return implode("\n",$_html_result);
    }

}

function smarty_function_html_radios_output($name, $value, $output, $selected, $extra, $separator, $labels, $label_ids) {
    $_output = '';
    if ($labels) {
      if($label_ids) {
          $_id = smarty_function_escape_special_chars(preg_replace('![^\w\-\.]!', '_', $name . '_' . $value));
          $_output .= '<label for="' . $_id . '">';
      } else {
          $_output .= '<label>';           
      }
   }
   $_output .= '<input type="radio" name="'
        . smarty_function_escape_special_chars($name) . '" value="'
        . smarty_function_escape_special_chars($value) . '"';

   if ($labels && $label_ids) $_output .= ' id="' . $_id . '"';

    if ((string)$value==$selected) {
        $_output .= ' checked="checked"';
    }
    $_output .= $extra . ' />' . $output;
    if ($labels) $_output .= '</label>';
    $_output .=  $separator;

    return $_output;
}

?>
