<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/Select.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/Hidden.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/Date.php");

class ReservesFilterForm extends Form_View
{
    protected $viewScriptPrefixPath = 'classes/Form/Templates/Horizontal';
    
    protected $typePrefix = array(
        'Date'   => 'Zend_Form_Element_',
        'Select' => 'Form_Element_'
    );

    public $filters = array(
        'StringTrim',
        'StripSlashes'
    );
    
    protected $elements_options;


    public function __construct($elements_options, $options = null) 
    {
        $this->elements_options = $elements_options;
        parent::__construct($options);
    }
    
    public function init()
    {
        if (empty($this->elements_options)) {
            return false;
        }

        
        foreach ($this->elements_options as $name => $element_option) {
            $class = 'Zend_Form_Element_Text';
            $_prefix = @$this->typePrefix[@$element_option['type']];
            if($_prefix) $class = $_prefix . $element_option['type']; 
            
            $filters = $this->filters;
            if (isset($element_option['filters'])) {
                $filters = array_merge($filters, $element_option['filters']);
            }
            
            $options = array(
                'filters' => $filters,
                'label' => $element_option['label'],
                'hide_label' => true
            );
            
            if (isset($element_option['multioptions'])) {
                $options['multioptions'] = $element_option['multioptions'];
            }
            
            if (!isset($element_option['order'])) {
                $options['data_stop_order'] = true;
            }
            
            $element = new $class($name, $options);
            $this->addElement($element);
        }
        
        
        $this->addElement(
           new Form_Element_Hidden('dir', array(
               'data_hide' => true,
               'filters' => $this->filters,
               'value' => 'desc',
               'validators' => array(
                   array('InArray', true, array(array('asc','desc')))
               )
        )));
        
        $this->addElement(
           new Form_Element_Hidden('dir_col', array(
               'data_hide' => true,
               'filters' => $this->filters,
               'validators' => array(
                   array('InArray', true, array(array_keys($this->elements_options)))
               )
        )));         
    }
    

}