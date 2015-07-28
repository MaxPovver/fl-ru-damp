<?php


class Form_Filter_StripSlashes implements Zend_Filter_Interface
{

    public function filter($value)
    {
        return stripslashes($value);
    }
}
