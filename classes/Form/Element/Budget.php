<?php

class Form_Element_Budget extends Zend_Form_Element
{
    public function init()
    {
        $this->addDecorator('ViewScript', array('viewScript' => 'classes/Form/Templates/Budget.phtml'));
    }

    public function getValue($name = '')
    {
        $value = parent::getValue();

        if ($name) {
            $value = isset($value[$name])?$value[$name]:'';
        }

        return $value;
    }
}