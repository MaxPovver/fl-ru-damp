<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_binds.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/buffer.php');

class quickPaymentPopupTservicebindup extends quickPaymentPopup
{
    const PAYMENT_TYPE_ACCOUNT = 'account';
    const PAYMENT_TYPE_BUFFER = 'buffer';
    
    const POPUP_ID_PREFIX = 'quick_payment_tservicebindup_%d';
    
    protected $UNIC_NAME = 'tservicebindup';
    
    public $inited = false;


    public function __construct()
    {
        parent::__construct();
        
        //Допускаем оплату с личного счета
        $this->options['payments'][self::PAYMENT_TYPE_ACCOUNT] = array();
        
        //Допускаем оплату с буфера
        $this->options['payments'][self::PAYMENT_TYPE_BUFFER] = array();
    }
    
    public function init($params) 
    {
        $kind = $params['kind'] ? : tservices_binds::KIND_LANDING; //По умолчанию ставим лендинг
        $prof_id = (int) $params['prof_id'];
        
        $this->setBuyPopupTemplate('buy_popup_tservicebindup.tpl.php');
        
        $tservices_binds = new tservices_binds($kind);
        
        $promoCodes = new PromoCodes();
        
        $buffer = new buffer();
        
        $options = array(
            'popup_title_class_bg'      => 'b-fon_bg_po',
            'popup_title_class_icon'    => 'b-icon__po',
            'popup_title'               => 'Поднятие закрепления на 1 место',
            'popup_id'                  => $this->getPopupId(0),
            'unic_name'                 => $this->UNIC_NAME,
            'payments_title'            => 'Сумма и способ оплаты',
            'payments_exclude'          => array(self::PAYMENT_TYPE_BANK),
            'ac_sum'                    => round($_SESSION['ac_sum'], 2),
            'payment_account'           => self::PAYMENT_TYPE_ACCOUNT,
            'kind'                      => $kind,
            'profession'                => $tservices_binds->getProfessionText(false, $prof_id),
            'buffer'                    => $buffer->getSum(),
            'ammount'                   => round($tservices_binds->getPrice(true, @$params['uid'], $prof_id), 2),
            'disable_tservices'         => false,
            'prof_id'                   => $prof_id,
            'promo_code' => $promoCodes->render(PromoCodes::SERVICE_TSERVICEBIND)
        );
        
        //Обязательно передаем родителю
        parent::init($options);
        
        
        //Добавляем свойство к одному способу оплаты
        $this->options['payments'][self::PAYMENT_TYPE_CARD]['wait'] = 'Ждите ....';
        
        $this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM]['content_after'] = sprintf(
            $this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM]['content_after'],
            'закрепление'
        );
        
        $this->inited = true;
    }
    
    public function render($options = array())
    {
        $this->options['is_show'] = __paramInit('bool', $options['popup_id'], $options['popup_id'], false);
        
        $this->options = array_merge($this->options, $options);
        return Template::render(ABS_PATH . self::TPL_MAIN_PATH . $this->buy_popup_tpl, $this->options);
    }
    
    public static function getPopupId($id)
    {
        $popup_id = sprintf(static::POPUP_ID_PREFIX, $id);
        return $popup_id;
    }

}
