<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/tu/models/NewOrderForm.php");
require_once('GuestConst.php');

class GuestNewOrderForm extends NewOrderForm
{
    protected $viewScriptFormPrefixPath = 'guest/views/forms';
    public $freelancer = null;


    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 
                $this->viewScriptFormPrefixPath . 
                '/guest-new-order-form.phtml'))
        ));
    }
    
    public function getCustomErrorMessage($err)
    {
        return GuestConst::getErrorMessage($err, GuestConst::TYPE_PERSONAL_ORDER);
    }    
    
    public function getCustomMessage($mes)
    {
        return GuestConst::getMessage($mes, GuestConst::TYPE_PERSONAL_ORDER);
    }
    
}