<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");

class BillInvoicesPayForm extends Form_View
{
    protected $viewScriptPrefixPath = 'classes/Form/Templates/Horizontal';

    
    public function init()
    {
        //$this->setElementsBelongTo('price');
        
        /*
        $this->setElementsBelongTo('foo')->setElements(array(
            'bar' => 'text',
            'baz' => 'text'
        ));
        */

        
        /*
        $this->addElement(
           new Zend_Form_Element_Text('price', array(
               'hide_label' => true,
               'width' => 80,
               'maxlength' => 7,
               'isArray' => true,
               'validators' => array(
                   array('Digits', true),
                   array('Between', true, array('max' => 9999999,'min' => 300))
               )
        )));
        */
        
        /*
        $this->addElement(
           new Zend_Form_Element_Text('2', array(
               'hide_label' => true,
               'width' => 80,
               'maxlength' => 7,
               //'isArray' => true,
               'validators' => array(
                   array('Digits', true),
                   array('Between', true, array('max' => 9999999,'min' => 300))
               )
        )));   
        
        $this->setElementsBelongTo('price');*/
    }
    

}