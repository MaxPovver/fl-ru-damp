<?php

require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate_a1.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/tu/models/TServiceOrderModel.php';
require_once $_SERVER['DOCUMENT_ROOT']."/classes/sbr.php";

/**
 * СМС уведомления по ТУ
 */
class tservices_sms extends sms_gate_a1
{
    /**
     * Дополнительные статусы
     */
    const STATUS_NEW_RESERVE        = 100;
    const STATUS_CHANGE_ORDER       = 101;
    const STATUS_RESERVE_ACCEPT     = 102;
    
    /**
     * Сообщения состояния заказа ТУ
     * 
     * @var type 
     */
    public $txt_order_status = array(
        //TServiceOrderModel::STATUS_NEW      => 'Вам заказана услуга на FL.ru. Пожалуйста, подтвердите заказ #%d или откажитесь от него.',
        self::STATUS_NEW_RESERVE            => 'Вам предложен заказ на FL.ru с резервированием суммы. Пожалуйста, подтвердите заказ #%d или откажитесь от него.',
        //self::STATUS_CHANGE_ORDER           => 'Заказчик изменил параметры заказа на услугу. Пожалуйста, подтвердите заказ #%d или откажитесь от него.',
        //TServiceOrderModel::STATUS_CANCEL   => 'К сожалению, заказчик отменил свой заказ услуги #%d на FL.ru.',
        //TServiceOrderModel::STATUS_ACCEPT   => 'Исполнитель подтвердил ваш заказ услуги #%d на FL.ru и начал его выполнение.',
        //self::STATUS_RESERVE_ACCEPT         => 'Исполнитель подтвердил заказ #%d. Пожалуйста, зарезервируйте бюджет на сайте FL.ru, чтобы исполнитель начал выполнение работы.',
        //TServiceOrderModel::STATUS_DECLINE  => 'К сожалению, исполнитель отказался от выполнения вашего заказа #%d на FL.ru.',
        //TServiceOrderModel::STATUS_FRLCLOSE => 'Сотрудничество по заказу #%d завершено. Пожалуйста, оставьте отзыв в заказе на FL.ru.',
        //TServiceOrderModel::STATUS_FRLCLOSE => 'Исполнитель завершил работы по заказу. Пожалуйста, перейдите в заказ #%d или ознакомьтесь с результатом работы.',
        //TServiceOrderModel::STATUS_EMPCLOSE => 'Сотрудничество по заказу #%d завершено. Пожалуйста, оставьте отзыв в заказе на FL.ru.'
    );


    /**
     * Телефончик то есть?
     * 
     * @return type
     */
    public function isPhone()
    {
        return !empty($this->_msisdn);
    }

    

    /**
     * Отправить СМС по состоянию заказа ТУ
     * 
     * @param int $status
     * @param int $num
     * @return boolean
     */
    public function sendOrderStatus($status, $id)
    {
        if(!isset($this->txt_order_status[$status]) || !$this->isPhone()) return FALSE;
        $message = sprintf($this->txt_order_status[$status], $id);
        return $this->sendSMS($message);
    }

    



    /**
     * Создаем сами себя
     * @return TServiceModel
     */
    public static function model($uid) 
    {
        $phone = '';
        $reqv = sbr_meta::getUserReqvs($uid);
        
        if($reqv)
        {
            $ureqv = $reqv[$reqv['form_type']];
            $phone = $ureqv['mob_phone'];
        }

        $class = get_called_class();
        return new $class($phone);
    }
}