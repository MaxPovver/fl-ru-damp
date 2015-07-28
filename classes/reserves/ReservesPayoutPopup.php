<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesHelper.php');


class ReservesPayoutPopup 
{
    const POPUP_ID_PREFIX       = 'reserve_payout_popup_%d';
    
    const TPL_MAIN_PATH         = '/templates/reserves/';
    const TPL_POPUP_DEFAULT     = 'payout_popup.tpl.php';
    
    
    const PAYMENT_TYPE_CARD         = 'dolcard';
    const PAYMENT_TYPE_YA           = 'ya';
    const PAYMENT_TYPE_BANK         = 'bank';
    const PAYMENT_TYPE_RS           = 'rs';
    
    
    public static $payment_list     = array(
            self::PAYMENT_TYPE_BANK,
            self::PAYMENT_TYPE_YA,
            self::PAYMENT_TYPE_CARD,
            self::PAYMENT_TYPE_RS
    );

    
    public static $payments_text    = array(
            self::PAYMENT_TYPE_BANK =>  'Сумма будет перечислена на ваш счет в течение 2 рабочих дней.',
            self::PAYMENT_TYPE_YA   =>  'В течение нескольких минут сумма будет перечислена на ваш Яндекс.Кошелек.',
            self::PAYMENT_TYPE_CARD =>  'Сумма будет перечислена на вашу банковскую карту в течение 2 рабочих дней.',
            self::PAYMENT_TYPE_RS   =>   'Сумма будет перечислена на ваш счет в течение 2 рабочих дней.'
    );

    
    public static $payments_short_text = array(
            self::PAYMENT_TYPE_BANK =>  'банковский счет',
            self::PAYMENT_TYPE_YA   =>  'Яндекс.Кошелек',
            self::PAYMENT_TYPE_CARD =>  'банковскую карту',
            self::PAYMENT_TYPE_RS   =>  'банковский счет'         
    );


    public $options = array();
    public $price = 0;


    public function init()
    {    
        global $js_file;
        $js_file['reserves_payout'] = 'reserves/reserves_payout.js';
        
        $uid = get_uid(false);
        $reqvs = ReservesHelper::getInstance()->getUserReqvs($uid);
        $form_type = $reqvs['form_type'];
        $rez_type = $reqvs['rez_type'];
        
        $reqv = $reqvs[$form_type];

        $payments = array(
            self::PAYMENT_TYPE_CARD => array(
                'title' => 'Пластиковые<br/>карты',
                'class' => 'b-button__pm_card',
                'num' => @$reqv['el_ccard'],
                'wait' => 'Идет выплата суммы.'
            ),
            self::PAYMENT_TYPE_YA => array(
                'title' => 'Яндекс.Деньги',
                'class' => 'b-button__pm_yd',
                'num' => @$reqv['el_yd'],
                'wait' => 'Идет выплата суммы.'
            ),
            self::PAYMENT_TYPE_BANK => array(
                'title' => 'Банковский<br/>перевод',
                'class' => 'b-button__pm_bank',
                'num' => @$reqv['bank_rs'],
                'wait' => 'Идет выплата суммы.'
            ),
            self::PAYMENT_TYPE_RS => array(
                'title' => 'Банковский<br/>перевод',
                'class' => 'b-button__pm_bank',
                'num' => @$reqv['bank_rs'],
                'wait' => 'Идет выплата суммы.'
            )
        );
        
        
        $form_list = array(
            sbr::FT_PHYS    => 'физическое лицо',
            sbr::FT_JURI    => 'юридическое лицо'
        );
        
        $this->options['form_txt'] = @$form_list[$form_type];
        $this->options['rez_txt'] = sbr::getRezTypeText($rez_type);
        
        $allowed_payments = ReservesHelper::getInstance()->getAllowedPayoutTypes(
                $form_type,
                $rez_type,
                $this->price);
        
        $this->options['payments'] = array_intersect_key(
                $payments, 
                $allowed_payments);
        
        
        $popup_id = self::getPopupId($this->options['idx']);
        
        $this->options['is_show'] = __paramInit('bool', $popup_id, $popup_id, false);
    }

    
    public function run()
    {
        echo Template::render(
                ABS_PATH . 
                self::TPL_MAIN_PATH . 
                self::TPL_POPUP_DEFAULT, 
                $this->options);
    }
    
   
    public static function getPopupId($id)
    {
        return sprintf(static::POPUP_ID_PREFIX, $id);
    }    
    
}