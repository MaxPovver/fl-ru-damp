<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');

/**
 * Class TServiceOrderFeedback
 *
 * Виджет - 
 */
class TServiceOrderFeedback extends CWidget 
{
    const POPUP_ID_PREFIX = 'order_feedback_popup_%d';
    
    public $data = array();

    public function init() 
    {
        parent::init();
        
        global $js_file;
        $js_file['tservices_order_feedback'] = 'tservices/tservices_order_feedback.js';
    }

    
    public function run() 
    {
        //собираем шаблон
        $this->render('t-service-order-feedback', $this->data);
    }
    
    
    public static function getPopupId($id)
    {
        return sprintf(static::POPUP_ID_PREFIX, $id);
    }
}