<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * Class Form_Validate_NoUserExists
 * 
 * Валидатор проверяет наличие указанного типа пользователя 
 * по одному из входных параметров. 
 */
class Form_Validate_NoUserExists extends Zend_Validate_Abstract 
{
    const ERROR_USER_FOUND  = 'userFound';
    
    
    protected $_messageTemplates = array(
        self::ERROR_USER_FOUND => 'Данный %by% принадлежит %is_emp%'
    );
    
    protected $_messageRole = array(
        false => 'фрилансеру',
        true  => 'заказчику',
        null  => 'другому пользователю'
    );

    protected $_messageBy = array(
        'login' => 'логин',
        'email' => 'e-mail',
        'uid'   => 'ID'
    );

    protected $_messageVariables = array(
        'is_emp' => '_str_is_emp',
        'by'     => '_str_by'
    );
    
    protected $_is_emp = null;
    protected $_str_is_emp;
    
    protected $_by     = 'login';
    protected $_str_by;

    
    protected $user = null;




    /**
     * Sets validator options
     *
     * @param  integer|array|Zend_Config $options
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $options = func_get_args();
            $temp['is_emp'] = array_shift($options);
            
            if (!empty($options)) {
                $temp['by'] = array_shift($options);
            }            
            
            $options = $temp;
        }

        if (array_key_exists('is_emp', $options)) {
            $this->setIsEmp($options['is_emp']);
        }       
        
        if (array_key_exists('by', $options)) {
            $this->setBy($options['by']);
        }
        
        $this->_by = in_array($this->_by, array_keys($this->_messageBy))?
                $this->_by:'login';
        
        $this->_str_is_emp = @$this->_messageRole[$this->_is_emp];
        $this->_str_by = @$this->_messageBy[$this->_by];
    }
    
    
    public function setBy($by)
    {
        $this->_by = $by;
    }
    

    public function setIsEmp($is_emp)
    {
        $this->_is_emp = $is_emp;
    }

    
    public function getUser()
    {
        return $this->user;
    }

    
    public function isValid($value) 
    {
        $isValid = true;
        
        $this->_setValue($value);

        $this->user = new users();

        switch ($this->_by) {
            case 'login':
                $this->user->GetUser($value, true, false);
                break;
            
            case 'email':
                $this->user->GetUser($value, true, true);
                break;
            
            case 'uid':
                $this->user->GetUserByUID($value);
                break;
        }
        
        if ($this->user->uid > 0 && $this->user->{$this->_by} == $value && 
            ($this->_is_emp === null || is_emp($this->user->role) == $this->_is_emp)) {
            
            $this->_error(self::ERROR_USER_FOUND);
            $isValid = false;
        }
        
        return $isValid;
    }
}