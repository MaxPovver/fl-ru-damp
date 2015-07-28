<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/guest/models/GuestInviteUnsubscribeModel.php");

/**
 * Class Form_Validate_NoUserExists
 * 
 * ¬алидатор провер€ет наличие указанного типа пользовател€ 
 * по одному из входных параметров. 
 */
class Form_Validate_EmailUnsubscribed extends Zend_Validate_Abstract 
{
    const ERROR_USER_UNSUBSCRIBED  = 'unsubscribed';
    
    
    protected $_messageTemplates = array(
        self::ERROR_USER_UNSUBSCRIBED => 'ѕользователь с этим e-mail адресом запретил отправку ему приглашений'
    );
    
    public function isValid($value) 
    {
        $isValid = true;
        
        $this->_setValue($value);
        
        $guestInviteUnsubscribeModel = new GuestInviteUnsubscribeModel();
       
        if ($guestInviteUnsubscribeModel->isUnsubscribed($value)) {
            
            $this->_error(self::ERROR_USER_UNSUBSCRIBED);
            $isValid = false;
        }
        
        return $isValid;
    }
}