<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/Budget.php");

class AutoresponseForm extends Form_View
{
    public $filters = array(
        'StringTrim',
        'StripSlashes'
    );
    
    public function init()
    {
        $this->addElement(
            new Zend_Form_Element_Textarea('descr', array(
                    'label' => 'Текст ответа',
                    'required' => true,
                    'placeholder' => 'Кратко опишите суть вашего предложения, условия сотрудничества, вопросы и необходимые требования к заказчику перед началом работы.',
                    'padbot' => 0, // отступ снизу
                    'maxlength' => 1000,
                    'filters' => $this->filters,
                    'validators' => array(
                        array(new Zend_Validate_StringLength(array('max' => 1000)), true),
                    ),
                    'suffix' => 'Не более 1000 символов.'
                )
            )
        );

        $this->addElement(
            new Zend_Form_Element_Checkbox('only_4_cust', array(
                    'label'      => 'Скрыть ответ, сделав его видимым только работодателю (автору проекта)',
                    'required' => false,
                )
            )
        );

        $this->addElement(
            new Zend_Form_Element_Text('total', array(
                    'label' => 'Количество<br>автоответов',
                    'width' => 80,
                    'required' => false,
                    'validators' => array(
                        array(new Zend_Validate_Int(), true),
                        array(new Zend_Validate_Between(array('min' => 1, 'max' => 100000)), true),
                    )                    
                )
            )
        );

        $this->addElement(
            new Form_Element_Budget('filter_budget', array(
                    'label' => 'Бюджет от',
                    'width' => 80,
                    'required' => false,
                )
            )
        );
    }
}
