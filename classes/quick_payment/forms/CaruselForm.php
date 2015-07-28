<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/Spinner.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");

class CaruselForm extends Form_View
{
    public $filters = array(
        'StringTrim',
        'StripSlashes',
        //'StripTags',
        //'Htmlspecialchars',
        'Carusel'
    );
    
    //Путь к вьюшкам элементов
    protected $viewScriptPrefixPath = 'classes/Form/Templates/Horizontal';
    //Путь вьюшкам форм
    protected $viewScriptFormPrefixPath = 'templates/quick_payment/forms';
    
    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            //'PrepareElements',
            array('ViewScript', array('viewScript' => 
                $this->viewScriptFormPrefixPath . 
                '/carusel_form.phtml'))
        ));
    }    
    
    public function init()
    {
        $this->addElement(
           new Zend_Form_Element_Text('title', array(
               'placeholder' => 'Заголовок',
               'required' => true,
               //'padbot' => 20, // отступ снизу
               'maxlength' => pay_place::MAX_HEADER_SIZE,
               'filters' => $this->filters,
               'validators' => array(
                   array('StringLength',true,array('max' => pay_place::MAX_HEADER_SIZE,'min' => 4))
                )
        )));       
        
        $this->addElement(
          new Zend_Form_Element_Textarea('description', array(
              'placeholder' => 'Текст объявления',
              'required' => true,
              //'padbot' => 20, // отступ снизу
              'filters' => $this->filters,
              'validators' => array(
                  array('StringLength', true, array('max' => pay_place::MAX_TEXT_SIZE, 'min' => 4))
               )
        )));        
        
        $this->addElement(
          new Zend_Form_Element_Spinner('num', array(
              'required' => true,
              'width' => 80,
              'value' => 1,
              'max' => 99,
              'min' => 1,
              'suffix' => array('размещение','размещения','размещений')
          ))
        );
        
        $this->addElement(
          new Zend_Form_Element_Spinner('hours', array(
              'required' => true,
              'width' => 80,
              'value' => 1,
              'max' => 99,
              'min' => 1,
              'suffix' => array('час','часа','часов')
          ))
        );        
    }
    

}