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



function smarty_function_html_select_date($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');
    require_once $smarty->_get_plugin_filepath('shared','make_timestamp');
    require_once $smarty->_get_plugin_filepath('function','html_options');
    
    $prefix          = "Date_";
    $start_year      = strftime("%Y");
    $end_year        = $start_year;
    $display_days    = true;
    $display_months  = true;
    $display_years   = true;
    $month_format    = "%B";
    
    $month_value_format = "%m";
    $day_format      = "%02d";
    
    $day_value_format = "%d";
    $year_as_text    = false;
    
    $reverse_years   = false;
    
    $field_array     = null;
    
    $day_size        = null;
    $month_size      = null;
    $year_size       = null;
    
    $all_extra       = null;
    
    $day_extra       = null;
    $month_extra     = null;
    $year_extra      = null;
    
    $field_order     = 'MDY';
    
    $field_separator = "\n";
    $time = time();
    $all_empty       = null;
    $day_empty       = null;
    $month_empty     = null;
    $year_empty      = null;
    $extra_attrs     = '';

    foreach ($params as $_key=>$_value) {
        switch ($_key) {
            case 'prefix':
            case 'time':
            case 'start_year':
            case 'end_year':
            case 'month_format':
            case 'day_format':
            case 'day_value_format':
            case 'field_array':
            case 'day_size':
            case 'month_size':
            case 'year_size':
            case 'all_extra':
            case 'day_extra':
            case 'month_extra':
            case 'year_extra':
            case 'field_order':
            case 'field_separator':
            case 'month_value_format':
            case 'month_empty':
            case 'day_empty':
            case 'year_empty':
                $$_key = (string)$_value;
                break;

            case 'all_empty':
                $$_key = (string)$_value;
                $day_empty = $month_empty = $year_empty = $all_empty;
                break;

            case 'display_days':
            case 'display_months':
            case 'display_years':
            case 'year_as_text':
            case 'reverse_years':
                $$_key = (bool)$_value;
                break;

            default:
                if(!is_array($_value)) {
                    $extra_attrs .= ' '.$_key.'="'.smarty_function_escape_special_chars($_value).'"';
                } else {
                    $smarty->trigger_error("html_select_date: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if (preg_match('!^-\d+$!', $time)) {
        // negative timestamp, use date()
        $time = date('Y-m-d', $time);
    }
    // If $time is not in format yyyy-mm-dd
    if (preg_match('/^(\d{0,4}-\d{0,2}-\d{0,2})/', $time, $found)) {
        $time = $found[1];
    } else {
        // use smarty_make_timestamp to get an unix timestamp and
        // strftime to make yyyy-mm-dd
        $time = strftime('%Y-%m-%d', smarty_make_timestamp($time));
    }
    // Now split this in pieces, which later can be used to set the select
    $time = explode("-", $time);

    // make syntax "+N" or "-N" work with start_year and end_year
    if (preg_match('!^(\+|\-)\s*(\d+)$!', $end_year, $match)) {
        if ($match[1] == '+') {
            $end_year = strftime('%Y') + $match[2];
        } else {
            $end_year = strftime('%Y') - $match[2];
        }
    }
    if (preg_match('!^(\+|\-)\s*(\d+)$!', $start_year, $match)) {
        if ($match[1] == '+') {
            $start_year = strftime('%Y') + $match[2];
        } else {
            $start_year = strftime('%Y') - $match[2];
        }
    }
    if (strlen($time[0]) > 0) {
        if ($start_year > $time[0] && !isset($params['start_year'])) {
            // force start year to include given date if not explicitly set
            $start_year = $time[0];
        }
        if($end_year < $time[0] && !isset($params['end_year'])) {
            // force end year to include given date if not explicitly set
            $end_year = $time[0];
        }
    }

    $field_order = strtoupper($field_order);

    $html_result = $month_result = $day_result = $year_result = "";

    $field_separator_count = -1;
    if ($display_months) {
    	$field_separator_count++;
        $month_names = array();
        $month_values = array();
        if(isset($month_empty)) {
            $month_names[''] = $month_empty;
            $month_values[''] = '';
        }
        for ($i = 1; $i <= 12; $i++) {
            $month_names[$i] = strftime($month_format, mktime(0, 0, 0, $i, 1, 2000));
            $month_values[$i] = strftime($month_value_format, mktime(0, 0, 0, $i, 1, 2000));
        }

        $month_result .= '<select name=';
        if (null !== $field_array){
            $month_result .= '"' . $field_array . '[' . $prefix . 'Month]"';
        } else {
            $month_result .= '"' . $prefix . 'Month"';
        }
        if (null !== $month_size){
            $month_result .= ' size="' . $month_size . '"';
        }
        if (null !== $month_extra){
            $month_result .= ' ' . $month_extra;
        }
        if (null !== $all_extra){
            $month_result .= ' ' . $all_extra;
        }
        $month_result .= $extra_attrs . '>'."\n";

        $month_result .= smarty_function_html_options(array('output'     => $month_names,
                                                            'values'     => $month_values,
                                                            'selected'   => (int)$time[1] ? strftime($month_value_format, mktime(0, 0, 0, (int)$time[1], 1, 2000)) : '',
                                                            'print_result' => false),
                                                      $smarty);
        $month_result .= '</select>';
    }

    if ($display_days) {
    	$field_separator_count++;
        $days = array();
        if (isset($day_empty)) {
            $days[''] = $day_empty;
            $day_values[''] = '';
        }
        for ($i = 1; $i <= 31; $i++) {
            $days[] = sprintf($day_format, $i);
            $day_values[] = sprintf($day_value_format, $i);
        }

        $day_result .= '<select name=';
        if (null !== $field_array){
            $day_result .= '"' . $field_array . '[' . $prefix . 'Day]"';
        } else {
            $day_result .= '"' . $prefix . 'Day"';
        }
        if (null !== $day_size){
            $day_result .= ' size="' . $day_size . '"';
        }
        if (null !== $all_extra){
            $day_result .= ' ' . $all_extra;
        }
        if (null !== $day_extra){
            $day_result .= ' ' . $day_extra;
        }
        $day_result .= $extra_attrs . '>'."\n";
        $day_result .= smarty_function_html_options(array('output'     => $days,
                                                          'values'     => $day_values,
                                                          'selected'   => $time[2],
                                                          'print_result' => false),
                                                    $smarty);
        $day_result .= '</select>';
    }

    if ($display_years) {
    	$field_separator_count++;
        if (null !== $field_array){
            $year_name = $field_array . '[' . $prefix . 'Year]';
        } else {
            $year_name = $prefix . 'Year';
        }
        if ($year_as_text) {
            $year_result .= '<input type="text" name="' . $year_name . '" value="' . $time[0] . '" size="4" maxlength="4"';
            if (null !== $all_extra){
                $year_result .= ' ' . $all_extra;
            }
            if (null !== $year_extra){
                $year_result .= ' ' . $year_extra;
            }
            $year_result .= ' />';
        } else {
            $years = range((int)$start_year, (int)$end_year);
            if ($reverse_years) {
                rsort($years, SORT_NUMERIC);
            } else {
                sort($years, SORT_NUMERIC);
            }
            $yearvals = $years;
            if(isset($year_empty)) {
                array_unshift($years, $year_empty);
                array_unshift($yearvals, '');
            }
            $year_result .= '<select name="' . $year_name . '"';
            if (null !== $year_size){
                $year_result .= ' size="' . $year_size . '"';
            }
            if (null !== $all_extra){
                $year_result .= ' ' . $all_extra;
            }
            if (null !== $year_extra){
                $year_result .= ' ' . $year_extra;
            }
            $year_result .= $extra_attrs . '>'."\n";
            $year_result .= smarty_function_html_options(array('output' => $years,
                                                               'values' => $yearvals,
                                                               'selected'   => $time[0],
                                                               'print_result' => false),
                                                         $smarty);
            $year_result .= '</select>';
        }
    }

    // Loop thru the field_order field
    for ($i = 0; $i <= 2; $i++){
        $c = substr($field_order, $i, 1);
        switch ($c){
            case 'D':
                $html_result .= $day_result;
                break;

            case 'M':
                $html_result .= $month_result;
                break;

            case 'Y':
                $html_result .= $year_result;
                break;
        }
        // Add the field seperator
        if($i < $field_separator_count) {
            $html_result .= $field_separator;
        }
    }

    return $html_result;
}



?>
