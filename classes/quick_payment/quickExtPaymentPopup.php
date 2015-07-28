<?php

require_once('quickPaymentPopup.php');

/**
 * Class quickExtPaymentPopup
 * Класс расширяет возможности и упрощает описание для дочерних классов
 */
class quickExtPaymentPopup extends quickPaymentPopup
{
    const MINIMUM_PAYED_SUM = 10;
    const PAYMENT_TYPE_ACCOUNT = 'account';
    
    const TPL_BUY_POPUP_DEFAULT         = 'buy_ext_popup_default.tpl.php';
    const TPL_BUY_POPUP_DEFAULT_LAYOUT  = 'buy_ext_popup_default_layout.tpl.php';
    protected $buy_popup_layout_tpl;
    protected $usedLayout = true;
    protected $stopRender = false;

    public function __construct() 
    {
        parent::__construct();
        
        $this->buy_popup_layout_tpl = static::TPL_BUY_POPUP_DEFAULT_LAYOUT;
        
        $class_name = get_called_class();
        $this->UNIC_NAME = $this->classNameToUnicName($class_name);
        $this->ID = $class_name;
        
        $this->options['popup_title_class_bg']      = 'b-fon_bg_po';
        $this->options['popup_title_class_icon']    = 'b-icon__po';
        $this->options['popup_id'] = $this->ID;
        $this->options['unic_name'] = $this->UNIC_NAME;
        $this->options['acc_sum'] = (isset($_SESSION['ac_sum']) && $_SESSION['ac_sum'] > 0)? $_SESSION['ac_sum'] : 0;
        $this->options['payment_account'] = static::PAYMENT_TYPE_ACCOUNT;
        $this->options['minimum_payed_sum'] = static::MINIMUM_PAYED_SUM;
        //Допускаем оплату с личного счета
        $this->options['payments'][static::PAYMENT_TYPE_ACCOUNT] = array();
    }
    

    public function init($options = array())
    {
        $this->options = array_merge($this->options, $options);
        
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
        
        if (isset($_SESSION['ref_uri'])) {
            $ref_uri = urldecode($_SESSION['ref_uri']);
            $query = parse_url($ref_uri, PHP_URL_QUERY);
            $toAppend = strpos($query, $this->options['popup_id']) === false;
            if ($toAppend) {
                $delimitter = $query ? '&' : '?';
                $_SESSION['redirect_from_finance'] = urlencode($ref_uri . $delimitter . $this->options['popup_id'].'=1');
            }
        }
        
        $this->options['is_show'] = __paramInit('bool', $this->options['popup_id'], $this->options['popup_id'], false);
    }
    
    
    public function classNameToUnicName($class_name)
    {
        return strtolower(str_replace(array('quickPaymentPopup', 'quickExtPaymentPopup'), '', $class_name));
    }


    public function addWaitMessageForAll($message = ' ')
    {
        if (empty($this->options['payments'])) {
            return false;
        }
        
        foreach ($this->options['payments'] as $key => $value) {
            $this->options['payments'][$key]['wait'] = $message;
        }
        
        return true;
    }

    public function initJS()
    {
        global $js_file;
        $js_file['quick_ext_payment'] = 'quick_payment/quick_ext_payment.js';     
    }
    
    
    public function getPopupId()
    {
        return $this->ID;
    }

    
    public function setContent($html)
    {
        $this->options['content'] = $html;
    }

    
    public function usedLayout($use = true)
    {
        $this->usedLayout = $use;
    }

    
    public function stopRender($stop = true)
    {
        $this->stopRender = $stop;
    }

    
    public function getAccSum()
    {
        return (isset($this->options['acc_sum']) && 
                $this->options['acc_sum'] > 0)? 
        $this->options['acc_sum'] : 0;
    }

    
    public function render($options = array())
    {
        $this->options = array_merge($options, $this->options);
        
        $html = '';
        
        if($this->stopRender) {
            return $html;
        }
        
        if (isset($this->options['content'])) {
            $html = $this->options['content'];
        } else {
            $html = Template::render(
                    ABS_PATH . static::TPL_MAIN_PATH . $this->buy_popup_tpl, 
                    $this->options);
        }
        
        if ($this->usedLayout) {
            $this->setContent($html);
            $html = Template::render(
                    ABS_PATH . self::TPL_MAIN_PATH . $this->buy_popup_layout_tpl, 
                    $this->options);
        }
        
        return $html;
    }
    
    
}