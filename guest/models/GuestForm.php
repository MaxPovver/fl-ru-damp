<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");
require_once('GuestConst.php');

/**
 * Class GuestForm
 * Базовый класс формы для всех форм данного модуля
 */
class GuestForm extends Form_View
{
    protected $viewScriptFormPrefixPath = 'guest/views/forms';
    protected $viewScriptPrefixPath = 'classes/Form/Templates/Horizontal';
    
    protected $is_adm = false;
    
    public $filters = array(
        'StringTrim',
        'StripSlashes'
    ); 
    
    public $filtersAll = array(
        'StripTags',
        'StringTrim',
        'StripSlashes'        
    ); 
    
    public function __construct($options = null) 
    {
        if (isset($options['is_adm'])) {
            $this->setIsAdm($options['is_adm']);
            unset($options['is_adm']);
        }
        
        parent::__construct($options);
    }    
    
    
    /**
     * Общая вьюшка для форм
     */
    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 
                $this->viewScriptFormPrefixPath . 
                '/guest-default-form.phtml'))
        ));
    }
    
    /**
     * Установить что пользователь админ
     * (доступ к расширенному интерфейсу)
     * 
     * @param type $is_adm
     */
    public function setIsAdm($is_adm = true)
    {
        $this->is_adm = $is_adm;
    }
    
    /**
     * Текущий пользователь админ?
     * 
     * @return bool
     */
    public function isAdm()
    {
        return $this->is_adm;
    }
    
    /**
     * @override
     * @param array $data
     */
    public function isValid($data)
    {
        if ($this->isAdm()) {
            if ($data['cost']['budget'] > 0) {
                unset($data['cost']['agreement']);
            } else {
                $data['cost']['agreement'] = 1;
            }
        }
        
        return parent::isValid($data);
        
    }
}