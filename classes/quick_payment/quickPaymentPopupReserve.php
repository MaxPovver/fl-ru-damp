<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesHelper.php');


class quickPaymentPopupReserve extends quickPaymentPopup
{
    protected $UNIC_NAME = 'reserve';
    
    //@todo: #28021 Вебмани не позволяет сейчас зачислять суммы свыше 15000р.
    const MAX_PAYMEN_WM = 15000;


    //Исключаем способы оплаты в зависимости
    //от типа физика или юрика
    protected $payments_exclude = array(
        sbr::FT_PHYS => array(
            self::PAYMENT_TYPE_BANK,
            //@todo: ЯД неумеет возвращать платежи способами ниже
            //поэтому временно исключаем их из способов резервирования для всех
            self::PAYMENT_TYPE_ALFACLICK,
            self::PAYMENT_TYPE_SBERBANK
        ),
        sbr::FT_JURI => array(
            self::PAYMENT_TYPE_YA,
            self::PAYMENT_TYPE_CARD,
            self::PAYMENT_TYPE_WM,
            self::PAYMENT_TYPE_ALFACLICK,
            self::PAYMENT_TYPE_SBERBANK)
    );

    /**
     * Обьект резерва в зависимости от его типа
     * но все наследники ReservesModel
     * @example может быть обьект типа ReservesTServiceOrderModel
     * 
     * @todo: плохой подход только из-за цены передавать целый обьект, 
     * достаточно было цену передать в init параметром
     * 
     * @var ReservesModel 
     */
    public $reserveInstance;
    
    //@todo: современем заменить на данные из обьекта выше
    public $opt = array();
    public $uid;
    public $reserve_id;

    public function init() 
    {
        $this->setBuyPopupTemplate('buy_popup_reserve.tpl.php');
        
        $uid = $this->uid;
        $reserve_id = $this->reserve_id;
        
        $reqvs = ReservesHelper::getInstance()->getUserReqvs($uid);
        $form_type = $reqvs['form_type'];
        $rez_type = $reqvs['rez_type'];
        
        $form_id = $this->ID . '_form';
        $rez_id  = $this->ID . '_rez';
        
        $form_name = $form_type == sbr::FT_PHYS ? 'физическое лицо' : 'юридическое лицо';
        //$rez_name = $rez_type == sbr::RT_RU ? 'резидент РФ' : 'нерезидент РФ';
        $rez_name = sbr::getRezTypeText($rez_type);

        
        $options = array(
            'popup_title_class_bg'      => 'b-fon_bg_po',
            'popup_title_class_icon'    => 'b-icon__po',
            'popup_title'               => 'Резервирование бюджета',
            'popup_subtitle'            => '',
            'items_title'               => 'Сумма оплаты',
            'popup_id'                  => $this->ID,
            'unic_name'                 => $this->UNIC_NAME,
            'form_name'                 => $form_name,
            'rez_name'                  => $rez_name,
            'items' => array(
                array(
                    'value' => $reserve_id,
                    'name' => $form_id
                ),
                array(
                    'value' => $reserve_id,
                    'name' => $rez_id
                )
            ),
            'payments_title'            => 'Способ резервирования', 
            'payments_exclude'          => $this->payments_exclude[$form_type]
        );
        
        
        if ($form_type == sbr::FT_JURI) {
            $options['items'][] = array(
                'value' => 1,
                'name' => 'is_reserve_send_docs'
            );
        } 
        
        
        if ($this->reserveInstance->getReservePrice() >= self::MAX_PAYMEN_WM) {
            $options['payments_exclude'][] = self::PAYMENT_TYPE_WM;
        }
        if ($this->reserveInstance->getReservePrice() >= parent::MAX_PAYMENT_ALFA) {
            $options['payments_exclude'][] = self::PAYMENT_TYPE_ALFACLICK;
        }
        if ($this->reserveInstance->getReservePrice() >= parent::MAX_PAYMENT_SB) {
            $options['payments_exclude'][] = self::PAYMENT_TYPE_SBERBANK;
        }
        $options['payments_exclude'][] = self::PAYMENT_TYPE_PLATIPOTOM;
        
        parent::init($options);
    }
    
    
    /**
     * Метод для поддержки интерфейса виджета в архитектуре Yii
     */
    public function run()
    {
        echo $this->render($this->opt);
    }
    
    
    public function initJS()
    {
        global $js_file;
        
        parent::initJS();
        
        $js_file['quick_payment_reserve'] = 'quick_payment/reserve_quick_payment.js';        
    }
    
}
