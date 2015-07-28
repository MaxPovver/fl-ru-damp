<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php';
require_once($_SERVER['DOCUMENT_ROOT'].'/tu/models/TServiceOrderModel.php');

/**
 * Отправка ЛС по Типовым Услугам
 */
class messages_tservices extends messages
{
    
    /**
     * Отправка ЛС по заказу ТУ
     * 
     * @param string $login
     * @param string $message
     */
    public function sendOrderStatus($login, $message)
    {
        messages::Add( users::GetUid($err, 'admin'), $login, $message, '', 0, TRUE );
    }
    

}