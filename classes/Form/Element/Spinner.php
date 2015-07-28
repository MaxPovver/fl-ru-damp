<?php

class Zend_Form_Element_Spinner extends Zend_Form_Element_Text
{
    public function init()
    {
        global $js_file;
        $js_file['ElementsFactory'] = 'form/ElementsFactory.js';
        $js_file['ElementSpinner'] = 'form/Spinner.js';

        $max = $this->getAttrib('max');
        $min = $this->getAttrib('min');
        
        if($max && $min) {
            $this->addValidator('Between', true, array('max' => $max,'min' => $min));
        }
    }
}