<?php

require_once('FormElement.php');

class Form_Element_BudgetExt extends Form_Element
{
    public function init()
    {
        global $js_file;
        $js_file['BudgetExt'] = 'form/BudgetExt.js';
        
        $this->addValidator('Digits', true)
             ->addValidator('Between', true, array('max' => 999999,'min' => 0));
    }
    
    public function getValue($name = 'budget')
    {
        $valueFiltered = (isset($this->_value[$name]))?$this->_value[$name]:'';
        $this->_filterValue($valueFiltered, $valueFiltered);
        return $valueFiltered;
    }
    
    public function getUnfilteredValue($name = 'budget') 
    {
        $value = (isset($this->_value[$name]))?$this->_value[$name]:0;
        return $value;
    }
    
}