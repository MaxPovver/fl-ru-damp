<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');


class quickPaymentPopupAccount extends quickPaymentPopup
{
    const PRICE_MIN = 10;
    const PRICE_MAX = 250000;
    
    const PRICE_MAX_WM = 14850;
    
    protected $UNIC_NAME = 'account';
    
    public function __construct()
    {
        parent::__construct();
        
    }
    
    public function init($params) 
    {

        $this->setBuyPopupTemplate('buy_popup_account.tpl.php');

        $minPrice = self::PRICE_MIN;
        if (isset($params['acc_sum']) && $params['acc_sum'] < 0) {
            $minPrice = abs($params['acc_sum']);
        }
        
        $options = array(
            'popup_title_class_bg'      => '',
            'popup_title_class_icon'    => '',
            'popup_title'               => 'Пополнение счета',
            'popup_subtitle'            => 'Сумма пополнения',
            'popup_id'                  => $this->ID,
            'unic_name'                 => $this->UNIC_NAME,
            'payments_title'            => 'Способ пополнения',
            'payments_exclude'          => array(
                self::PAYMENT_TYPE_BANK,
                self::PAYMENT_TYPE_PLATIPOTOM
            ),
            'min_price' => $minPrice,
            'max_price' => self::PRICE_MAX
        );
        
        //Обязательно передаем родителю
        parent::init($options);
        
        
        //Добавляем свойство к одному способу оплаты
        $this->options['payments'][self::PAYMENT_TYPE_CARD]['wait'] = 'Ждите ....';
        $this->options['payments'][self::PAYMENT_TYPE_WM]['data-maxprice'] = self::PRICE_MAX_WM;            
        
    }
    
    public function getPopupId() {
        return $this->ID;
    }
    
    
}