<?php

require_once('GuestForm.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Validate/NoUserExists.php");

/**
 * Class NewDataForm
 * Класс основной формы с интерфейсом авторизации/регистрации юзера
 */
class NewDataForm extends GuestForm
{   
    /**
     * Объект дочерней формы со специфическим 
     * интерфейсом сбора данных
     */
    protected $dataForm;


    public function __construct(Zend_Form $dataForm, $options = null) 
    {
        $this->dataForm = $dataForm;
        parent::__construct($options);
    }

    /**
     * Специфическая вьюшка для этой формы
     */
    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            //'PrepareElements',
            array('ViewScript', array('viewScript' => 
                $this->viewScriptFormPrefixPath . 
                '/new-data-form.phtml'))
        ));
    }
    
    /**
     * Инициализация формы
     */    
    public function init()
    {
        $this->addSubForm($this->dataForm, 'dataForm');
        
        $this->addElement(
           new Zend_Form_Element_Text('uname', array(
               'hide_label' => true,
               'label' => 'Имя',
               'required' => true,
               'width' => 250,
               'placeholder' => 'Ваше имя, не более 21 символа',
               'maxlength' => 21,
               'filters' => $this->filters + array('StripTags'),
               'validators' => array(
                   array('StringLength', true, array('max' => 21,'min' => 2))
                )
        )));
        
        $this->addElement(
           new Zend_Form_Element_Text('usurname', array(
               'hide_label' => true,
               'label' => 'Фамилия',
               'width' => 250,
               'placeholder' => 'Ваша фамилия, не более 21 символа',
               'required' => true,
               'filters' => $this->filters + array('StripTags'),               
               'validators' => array(
                   array('StringLength', true, array('max' => 21,'min' => 2))
                )
        )));
        
        $message = $this->getCustomErrorMessage(GuestConst::EMAIL_ERR);
        $messages = ($message)?array(Form_Validate_NoUserExists::ERROR_USER_FOUND => $message):array();
        

        $validators = array(
            array('EmailAddress', true, array('domain' => false)),
            array('NoUserExists', true, array('is_emp' => false, 'by' => 'email', 'messages' => $messages))
        );
        
        if($this->isAdm()) {
            $validators[] = array('EmailUnsubscribed', true);
            $this->getElement('uname')->setRequired(false);
            $this->getElement('usurname')->setRequired(false);
        }
        
        $this->addElement(
           new Zend_Form_Element_Text('email', array(
               'hide_label' => true,
               'label' => 'Электронный адрес',
               'width' => 250,
               'placeholder' => 'Введите ваш e-mail',
               'required' => true,
               'filters' => $this->filters,
               'validators' => $validators
        )));
        
    }    
    
    
    public function getCustomErrorMessage($err)
    {
        if (method_exists($this->dataForm, __FUNCTION__)) {
            return $this->dataForm->getCustomErrorMessage($err);
        }
        
        return false;
    }
    
    
    public function getCustomMessage($mes)
    {
        if (method_exists($this->dataForm, __FUNCTION__)) {
            return $this->dataForm->getCustomMessage($mes);
        }
        
        return false;
    }
    
}