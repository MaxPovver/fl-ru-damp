<?php

class Form_Element_Hidden extends Zend_Form_Element_Hidden
{
    public function init()
    {
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorators(array(
            'ViewHelper',
            //'Description',
            //'Errors'
            ));
    }
}