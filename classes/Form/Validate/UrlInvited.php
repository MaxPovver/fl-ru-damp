<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/guest/models/GuestInviteModel.php");

/**
 * Class Form_Validate_UrlInvited
 * 
 * ¬алидатор провер€ет наличие ссылки указанного типа 
 */
class Form_Validate_UrlInvited extends Zend_Validate_Abstract 
{
    const ERROR_USER_INVITED  = 'invited';
    
    protected $_messageTemplates = array(
        self::ERROR_USER_INVITED => 'ѕо %s ранее уже было отправлено приглашение.'
    );
    
    //@todo: реализаци€ не по правилам Zend_Validation
    //см NoUserExists или любой зенд валидатор
    protected $message_type = array(
        GuestConst::TYPE_VACANCY => 'данной вакансии',
        GuestConst::TYPE_PROJECT => 'данному проекту'
    );
    
    protected $type;
    protected $_str_type;
    
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
            $temp['type'] = array_shift($options);
            $options = $temp;
        }
        
        if (array_key_exists('type', $options)) {
            $this->setType($options['type']);
        }

        $this->_str_type = @$this->message_type[$this->type];
        
        $this->_messageTemplates[self::ERROR_USER_INVITED] = sprintf(
                $this->_messageTemplates[self::ERROR_USER_INVITED], 
                $this->_str_type
        );
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }

    public function isValid($value) 
    {
        $isValid = true;
        
        $this->_setValue($value);
        
        $guestInviteModel = new GuestInviteModel();
       
        if ($guestInviteModel->isExistLink($value)) {
            $this->_error(self::ERROR_USER_INVITED);
            $isValid = false;
        }
        
        return $isValid;
    }
}