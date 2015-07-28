<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');


class quickPaymentPopupMasssending extends quickPaymentPopup
{
    const PAYMENT_TYPE_ACCOUNT = 'account';
    const PAYMENT_TYPE_BUFFER = 'buffer';
    
    protected $UNIC_NAME = 'masssending';
    
    public function __construct()
    {
        parent::__construct();
        
        //Допускаем оплату с личного счета
        $this->options['payments'][self::PAYMENT_TYPE_ACCOUNT] = array();
    }
    
    public function init($params) 
    {

        $this->setBuyPopupTemplate('buy_popup_masssending.tpl.php');

        $promoCodes = new PromoCodes();
        
        $options = array(
            'popup_title_class_bg'      => 'b-fon b-fon_bg_soap',
            'popup_title_class_icon'    => 'b-icon__soap',
            'popup_title'               => 'Покупка рассылки по разделам',
            'popup_subtitle'            => 'Параметры рассылки',
            'popup_id'                  => $this->ID,
            'unic_name'                 => $this->UNIC_NAME,
            'payments_title'            => 'Сумма и способ оплаты',
            'payments_exclude'          => array(
                self::PAYMENT_TYPE_BANK,
                self::PAYMENT_TYPE_PLATIPOTOM
            ),
            'ac_sum'                    => round($_SESSION['ac_sum'], 2),
            'payment_account'           => self::PAYMENT_TYPE_ACCOUNT,
            'count'                     => $params['count'],
            'count_pro'                 => $params['count_pro'],
            'price'                     => $params['price'],
            'send_id'                   => $params['send_id'],
            'promo_code' => $promoCodes->render(PromoCodes::SERVICE_MASSSENDING)
        );
        
        //Обязательно передаем родителю
        parent::init($options);
        
        
        //Добавляем свойство к одному способу оплаты
        $this->options['payments'][self::PAYMENT_TYPE_CARD]['wait'] = 'Ждите ....';
        
    }
    
    
}
