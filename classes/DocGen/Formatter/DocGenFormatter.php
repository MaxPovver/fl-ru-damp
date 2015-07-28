<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/num_to_word.php');

class DocGenFormatter 
{
    const DATE          = 'date';
    const DATETIME      = 'datetime'; 
    
    
    public function date($value)
    {
        return date('d.m.Y', strtotime($value));
    }
    
    public function datetime($value)
    {
        return $value;
    }
    
    
    public function datetext($value)
    {
        return date_text($value,'d');
    }

    
    public function pricetext($value)
    {
        return num2str($value);
    }

    public function pricefull($value)
    {
        return num2strL($value);
    }

    public function pricelong($value)
    {
        return num2strL($value) . ' (' . num2str($value) . ')';
    }

    public function price($value)
    {
        return number_format($value, 2, ',', '');
    }
    
    
    public function template($tmpl, $vars)
    {
        if(count($vars))
        {
            $keys = array();
            $values = array();
            foreach($vars as $key => $var)
            {
                $keys[] = '{$'.$key.'}';
                $values[] = $var;
            }
            
            $tmpl = str_replace($keys, $values, $tmpl);
        }    
        
        return $tmpl;
    }
}