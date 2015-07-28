<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/Date.php");

class ReservesReestrForm extends Form_View
{
    protected $viewScriptPrefixPath = 'classes/Form/Templates/Horizontal';
    
    protected $typePrefix = array(
        'Date'   => 'Zend_Form_Element_',
     );

    public $filters = array(
        'StringTrim',
        'StripSlashes'
    );
    
    public function __construct($options = null) 
    {
        parent::__construct($options);
    }
    
    
    public function getReadbleDateInterval()
    {
        $date_start = $this->getElement('date_start')->getValue();
        $time_start = $this->getElement('time_start')->getValue();
        $date_end = $this->getElement('date_end')->getValue();
        $time_end = $this->getElement('time_end')->getValue();
        
        return $date_start . (($time_start)?" {$time_start}":"") . " - {$date_end}"
                           . (($time_end)?" {$time_end}":"");
    }
    

    public function init()
    {
        $this->addElement(
            new Zend_Form_Element_Date('date_start', array(
                'label' => 'Начало',
                'unit' => true,
                'width' => 110,
                'filters' => $this->filters,
                'set_date_on_load' => true,
//                'required' => true,
                'validators' => array(
                    //array('StringLength',true,array('max' => 60,'min' => 4))
                    //array('Digits', true)
                )
        )));
        $this->addElement(
           new Zend_Form_Element_Text('time_start', array(
               'label' => '',
               'hide_label' => true,
               'unit' => true,
//               'required' => true,
               'width' => 50,
               'filters' => $this->filters,
               'validators' => array(
                   //new Zend_Validate_Date(array("format" => 'H:i'))
               ),
               'placeholder' => '00:00'
        )));
        $this->addElement(
            new Zend_Form_Element_Date('date_end', array(
               'label' => 'Начало',
               'unit' => true,
                'width' => 110,
               'filters' => $this->filters,
                'set_date_on_load' => true,
 //               'required' => true,
               'validators' => array(
                   //array('StringLength',true,array('max' => 60,'min' => 4))
                   //array('Digits', true)
                )
        )));
        $this->addElement(
           new Zend_Form_Element_Text('time_end', array(
               'label' => '№ дог.',
               'hide_label' => true,
               'unit' => true,
               'width' => 50,
  //             'required' => true,
               'filters' => $this->filters,
               'validators' => array(
                   //new Zend_Validate_Date(array("format" => 'H:i'))
               ),
               'placeholder' => '23:59',
               
        )));
    }
    

}