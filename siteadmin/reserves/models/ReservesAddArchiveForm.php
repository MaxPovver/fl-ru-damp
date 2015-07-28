<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");

class ReservesAddArchiveForm extends Form_View
{
    protected $viewScriptPrefixPath = 'classes/Form/Templates/Horizontal';


    public $filters = array(
        'StringTrim',
        'StripSlashes'
    );
    
    public function __construct($options = null) 
    {
        parent::__construct($options);
    }
    

    public function init()
    {
        $this->addElement(
            new Zend_Form_Element_MultiCheckbox('bs_ids', array(
                'filters' => $this->filters,
                'required' => true,
                'registerInArrayValidator' => false,
                'validators' => array(
                    array('Int')
                )
            ))    
        );
        
        
        $this->filters[] = array(
            'PregReplace', 
             array('match' => '/[^0-9:\-\.\s]+/', 'replace' => '')
        );
        
        $this->filters = array_reverse($this->filters);
        
        $this->addElement(
                new Form_Element_Hidden('date_range', array(
                    'required' => true,
                    'filters' => $this->filters
                ))
        );        
    }
}