<?php

class Form_Element_Select extends Zend_Form_Element_Select
{
    public function init()
    {
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorators(array(
            'ViewHelper',
            'Description',
            'Errors'));
    }
    
    public function render(Zend_View_Interface $view = null) 
    {
        $this->getView()->setEncoding('cp1251');
        return parent::render($view);
    }
}