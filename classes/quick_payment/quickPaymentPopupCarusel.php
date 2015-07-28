<?php

require_once('quickExtPaymentPopup.php');
require_once('forms/CaruselForm.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");

class quickPaymentPopupCarusel extends quickExtPaymentPopup
{
    
    public function initJS() 
    {
        parent::initJS();
        
        global $js_file;
        
        //расширяем скрипт попапа для специфики
        $js_file['carusel_quick_ext_payment'] = 'quick_payment/carusel_quick_ext_payment.js';
    }
    
    
    public function init($uid, $type_place = 0) 
    {
        
        $promoCodes = new PromoCodes();
        
        $options = array(
            'popup_title'               => 'Размещение в Карусели',
            'items_title'               => 'Параметры объявления',
            'payments_exclude'          => array(self::PAYMENT_TYPE_BANK),
            //@todo: пока одна цена и одна позиция для всех
            'price'                     => pay_place::getPrice(),
            'promo_code' => $promoCodes->render(PromoCodes::SERVICE_CARUSEL)
        );
        
        $this->addWaitMessageForAll(/* только индикатор */);
        
        $form = new CaruselForm();
        
        //Если уже размещался то поумолчанию заполняем тексты
        $payPlace = new pay_place($type_place);
        $data = $payPlace->getUserRequest($uid);
        if ($data) {
            $form->setDefaults(array(
                'title' => $data['ad_header'],
                'description' => $data['ad_text']
            ));
        }
        
        $this->setContent($form->render());
        
        parent::init($options);
        
        $this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM]['content_after'] = sprintf(
            $this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM]['content_after'],
            'карусель'
        );
    }
    
}