<?php

require_once('quickExtPaymentPopup.php');
require_once('forms/ProForm.php');

class quickPaymentPopupPro extends quickExtPaymentPopup
{
    const TPL_BUY_POPUP_DEFAULT_LAYOUT  = 'buy_ext_popup_pro_layout.tpl.php';
    
    
    const TXT_FRL_TITLE = "Покупка аккаунта %s фрилансера";
    const TXT_FRL_SUBTITLE = '
        неограниченные отклики на проекты, доступ
        к премиум проектам и повышение рейтинга
    ';
    
    const TXT_EMP_TITLE = "Покупка аккаунта %s работодателя";
    const TXT_EMP_SUBTITLE = '
        доступ к контактам фрилансеров <br/>
        и скидки до 50% на дополнительные услуги';
    
    
    const TXT_DTITLE = 'PRO аккаунт на %s';
    const TXT_DTITLE_SUFFIX = ' <span class="%s">+%s экономии</span>,';

    
    public function initJS() 
    {
        parent::initJS();
        
        global $js_file;
        $js_file['pro_quick_ext_payment'] = 'quick_payment/pro_quick_ext_payment.js';
    }    
    
    public function init() 
    {
        $is_emp = is_emp();
        $form = new ProForm($is_emp);
        $css_class = ($is_emp)?'g-color_64bc39':'g-color_ff7f1a';

        $options = array(
            'payments_exclude' => array(self::PAYMENT_TYPE_BANK),
            'is_emp' => $is_emp
        );
        
        
        $list = $form->getPayedList();
        if ($list) {
            
            $clientside_templates = array();
            
            foreach ($list as $item) {
                $key = "{$this->ID}Type{$item['opcode']}";
                
                $value = sprintf(self::TXT_DTITLE, proItemToText($item));
                if (isset($item['sale'])) {
                    $value .= sprintf(self::TXT_DTITLE_SUFFIX, $css_class, $item['sale']);
                } else {
                    $value .= ',';
                }
                
                $clientside_templates[$key] = $value;
            }
            
            $options['clientside_templates'] = $clientside_templates;
        }
        
        
        if ($is_emp) {
            $options['popup_title'] = sprintf(
                    self::TXT_EMP_TITLE, 
                    view_pro_emp('b-icon__pro_va_baseline'));
            $options['popup_subtitle'] = self::TXT_EMP_SUBTITLE;
        } else {
            $options['popup_title'] = sprintf(
                    self::TXT_FRL_TITLE, 
                    view_pro('b-icon__pro_va_baseline'));    
            $options['popup_subtitle'] = self::TXT_FRL_SUBTITLE;
        }
        
        
        

        $this->addWaitMessageForAll(/* только индикатор */);
        $this->setContent($form->render());
        
        parent::init($options);
        
        $this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM]['content_after'] = sprintf(
            $this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM]['content_after'],
            'аккаунт PRO'
        );        
    }
    
}