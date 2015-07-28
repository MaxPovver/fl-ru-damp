<?php


class Form_Filter_Htmlspecialchars implements Zend_Filter_Interface
{

    public function filter($value)
    {
        //return htmlspecialchars($value, ENT_QUOTES, 'cp1251'); 
        return str_replace(array('<', '>', '"', '\''), array('&lt;', '&gt;', '&quot;', '&#039;'), $value);
    }
}
