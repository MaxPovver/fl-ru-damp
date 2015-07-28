<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/PromoCodes.php");

class quickPaymentPopup 
{
    
    protected $PREFIX               = 'quick_payment';
    protected $UNIC_NAME            = '';
    protected $ID                   = '';
    
    const TPL_MAIN_PATH             = '/templates/quick_payment/';
    const TPL_BUY_POPUP_DEFAULT     = 'buy_popup_default.tpl.php';
    protected $buy_popup_tpl;

    const PAYMENT_TYPE_CARD         = 'dolcard';
    const PAYMENT_TYPE_YA           = 'ya';
    const PAYMENT_TYPE_WM           = 'webmoney';
    const PAYMENT_TYPE_BANK         = 'bank';
    const PAYMENT_TYPE_PLATIPOTOM   = 'platipotom';
    const PAYMENT_TYPE_SBERBANK     = 'sberbank';
    const PAYMENT_TYPE_ALFACLICK    = 'alfaclick';

    const QPP_REDIRECT              = 'quickPaymentPopupRedirect';
    
    const MAX_PAYMENT_ALFA = 15000;
    const MAX_PAYMENT_SB = 10000;
    
    protected $options = array();

    
    public function __construct() 
    {
        $this->initJS();

        $this->buy_popup_tpl = static::TPL_BUY_POPUP_DEFAULT;
        $this->ID = $this->PREFIX . (!empty($this->UNIC_NAME)?'_':'') . $this->UNIC_NAME;
        
        $this->options['payments'] = array(
            self::PAYMENT_TYPE_CARD => array(
                'title' => 'Пластиковые<br/>карты', 
                'class' => 'b-button__pm_card',
                'short' => 'card'
                ),
            self::PAYMENT_TYPE_YA   => array(
                'title' => 'Яндекс.Деньги', 
                'class' => 'b-button__pm_yd',
                'short' => 'ym'
                ),
            self::PAYMENT_TYPE_WM   => array(
                'title' => 'WebMoney', 
                'class' => 'b-button__pm_wm',
                'short' => 'wm'
                ),
            self::PAYMENT_TYPE_BANK => array(
                'title' => 'Банковский<br/>перевод',
                'class' => 'b-button__pm_bank',
                'wait' => 'Идет создание счета на оплату.',
                'short' => 'bank'
                ),
            self::PAYMENT_TYPE_SBERBANK => array(
                'title' => 'Сбербанк<br />Онлайн',
                'class' => 'b-button__pm_sber',
                'data-maxprice' => self::MAX_PAYMENT_SB,
                'short' => 'sber'
                ),
            self::PAYMENT_TYPE_ALFACLICK => array(
                'title' => 'Альфа Клик',
                'class' => 'b-button__pm_alfa',
                'data-maxprice' => self::MAX_PAYMENT_ALFA,
                'short' => 'alfa'
                ),
            self::PAYMENT_TYPE_PLATIPOTOM => array(
                'title' => 'Купить <br />с отсрочкой <br />оплаты',
                'class' => 'b-button__pm_pp platipotom_link',
                'content_after' => 'Купите %s сейчас, а оплатите потом с отсрочкой 
                    платежа до 30 дней через сервис &quot;ПлатиПотом&quot;.',
                'short' => 'pp'
                )
        );
        
    }
    
    
    public function getActivePayments()
    {
        return array_keys($this->options['payments']);
    }
    

    public function initJS()
    {
        global $js_file;
        $js_file['quick_payment'] = 'quick_payment/quick_payment.js';        
    }
    

    
    public function isExistPaymentType($type)
    {
        return isset($this->options['payments'][$type]);
    }

    

    public function setBuyPopupTemplate($tmpl)
    {
        $this->buy_popup_tpl = $tmpl;
    }

    

    public function init($options = array())
    {
        if(isset($options['payments_exclude']))
        {
            foreach($options['payments_exclude'] as $payment_type)
                unset($this->options['payments'][$payment_type]);
        }
        
        if (isset($this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM])) {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/platipotom.php');
            $platipotom = new platipotom();
            $this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM]['data-maxprice'] = $platipotom->getMaxPrice();            
        }
         
        $this->options['is_show'] = __paramInit('bool', $options['popup_id'], $options['popup_id'], false);
        $this->options = array_merge($this->options, $options);
    }
    
    
    
    public function render($options = array())
    {
        $this->options = array_merge($options, $this->options);
        return Template::render(ABS_PATH . self::TPL_MAIN_PATH . $this->buy_popup_tpl, $this->options);
    }
    

    /**
     * Успех после оплаты и перехода юзера на сайт
     */    
    public function fireEventSuccess()
    {
        //TODO
        
        $this->redirect();
    }
    
    /**
     * Отказ/неудача оплаты и переход юзера на сайт
     */
    public function failEventSuccess()
    {
        //TODO
        
        $this->redirect();
    }

    
    
    public function redirect()
    {
        $url = @$_SESSION[static::QPP_REDIRECT];
        if(!$url) return false;
        
        header('Location: ' . $url, true, 302);
        exit;        
    }



    /*
    public function setRedirectUrl($url)
    {
        $_SESSION[static::QPP_REDIRECT] = $url;
        return true;
    }
    */

    
    /**
     * Создаем синглтон
     * @return object
     */
    final public static function getInstance()
    {
        static $instances = array();

        $calledClass = get_called_class();

        if (!isset($instances[$calledClass])) {
            $instances[$calledClass] = new $calledClass();
        }

        return $instances[$calledClass];
    }
    
}