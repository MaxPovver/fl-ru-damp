<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_binds.php');


class quickPaymentPopupTservicebind extends quickPaymentPopup
{
    const PAYMENT_TYPE_ACCOUNT = 'account';
    const POPUP_ID_PREFIX = 'quick_payment_tservicebind_%d';
    
    protected $UNIC_NAME = 'tservicebind';
    
    public $inited = false;


    public function __construct()
    {
        parent::__construct();
        
        //Допускаем оплату с личного счета
        $this->options['payments'][self::PAYMENT_TYPE_ACCOUNT] = array();
    }
    
    public function init($params) 
    {
        $kind = $params['kind'] ? : 1; //По умолчанию ставим лендинг
        
        $prof_id = (int) $params['prof_id'];

        $profs = array();
        if ($kind == tservices_binds::KIND_SPEC) {
            $profs[] = $prof_id;
        } elseif ($kind == tservices_binds::KIND_GROUP) {
            $tservices_categories = new tservices_categories();
            $categories = $tservices_categories->getCategoriesByParent($prof_id);
            foreach ($categories as $category) {
                $profs[] = $category['id'];
            }
        }
        
        $tservices = new tservices($params['uid']);
        $data = $tservices->getNotBindedList($kind, $profs);
        $tservices_text = $tservices_cur_text = '';
        $tservices_cur = 0;
        if ($data) {
            foreach ($data as $tservice) {
                if (!$tservices_cur) $tservices_cur = $tservice['id'];
                if (!$tservices_cur_text) $tservices_cur_text = $tservice['title'];
                $tservices_list[] = $tservice['id'] . ": '" . addslashes($tservice['title']) . "'";
            }
            $tservices_text = '{' . implode(', ', $tservices_list) . '}';
        } else {
            $tservices_text = '{}';
        }
        
        $this->setBuyPopupTemplate('buy_popup_tservicebind.tpl.php');

        
        $tservices_binds = new tservices_binds($kind);
        
        $promoCodes = new PromoCodes();
        
        $options = array(
            'popup_title_class_bg'      => 'b-fon_bg_po',
            'popup_title_class_icon'    => 'b-icon__po',
            'popup_title'               => $is_prolong ? 'Продление закрепления' : 'Закрепление услуги',
            'popup_subtitle'            => $is_prolong ? 'Срок продления закрепления' : 'Срок закрепления услуги',
            'popup_id'                  => $this->getPopupId(0),
            'unic_name'                 => $this->UNIC_NAME,
            'payments_title'            => 'Сумма и способ оплаты',
            'payments_exclude'          => array(self::PAYMENT_TYPE_BANK),
            'ac_sum'                    => round($_SESSION['ac_sum'], 2),
            'payment_account'           => self::PAYMENT_TYPE_ACCOUNT,
            'kind'                      => $kind,
            'profession'                => $tservices_binds->getProfessionText(false, $prof_id),
            'tservices'                 => $tservices_text,
            'tservices_cur'             => $tservices_cur,
            'tservices_cur_text'        => $tservices_cur_text,
            'ammount'                   => $tservices_binds->getPrice(false, $params['uid'], $prof_id),
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
        
        //@todo: неиспользуется?
        $tservices_binds = new tservices_binds($this->options['kind']);
        
        
        $is_prolong = (boolean) $options['date_stop'];
        
        $options['popup_title'] = $is_prolong ? 'Продление закрепления услуги' : 'Закрепление услуги';
        $options['popup_subtitle'] = $is_prolong ? 'Срок продления услуги' : 'Срок закрепления услуги';
        
        if ($is_prolong) {
            $options['tservices'] = '{'.$options['tservices_cur'].':'.$options['tservices_cur_text'].'}';
            $options['disable_tservices'] = true;
        }
        
        $this->options = array_merge($this->options, $options);
        return Template::render(ABS_PATH . self::TPL_MAIN_PATH . $this->buy_popup_tpl, $this->options);
    }
    
    public static function getPopupId($id)
    {
        $popup_id = sprintf(static::POPUP_ID_PREFIX, $id);
        return $popup_id;
    }

}
