<?php

/**
 * Поднятие акрепления в каталоге фрилансеров.
 * Оплата услуги.
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/platipotom.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_binds.php");


//------------------------------------------------------------------------------

/**
 * Оплата из буфера
 * @param type $type
 * @param array $data (prof_id)
 * @return type
 */
function quickPaymentFrlbindupBuffer($type, $data)
{
    $is_error = true;
    $uid = get_uid(false);
    $objResponse = &new xajaxResponse();
    $prof_id = (int)@$data['prof_id'];
    $is_spec = (bool)@$data['is_spec'];
    
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/buffer.php");
    $buffer = new buffer();
    $buffer_sum = $buffer->getSum();
    
    $freelancer_binds = new freelancer_binds();
    
    $is_binded = $freelancer_binds->isUserBinded($uid, $prof_id, $is_spec);
    
    $price = $freelancer_binds->getPriceUp($prof_id, $is_spec, $uid);
       
    if ($is_binded && $buffer_sum >= $price) {
        $bill = new billing($uid);
        
        $option = array('prof_id' => $prof_id);    
        //Покупаем услугу смоимостью 0 рублей при любом заначении ЛС
        $ok = $bill->addServiceAndPayZero(freelancer_binds::OP_CODE_UP_BUFFER, $option);

        if ($ok) {
            $is_error = false;
            $buffer->setUsedSum($price);
            $objResponse->script("window.location.reload();");
        }  
    }
    
    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPayment('frlbindup');
            if(qp) qp.show_error('Возникла ошибка при поднятии закрепления в каталоге!');
        ");
    }
        
    return $objResponse;
}


/**
 * Оплата с личного счета
 * @param type $type
 * @param array $data (weeks, prof_id, is_spec)
 * @return type
 */
function quickPaymentFrlbindupAccount($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    $prof_id = (int)@$data['prof_id'];
    $is_spec = (bool)@$data['is_spec'];
    $promo_code = (string)@$data['promo'];
    
    $freelancer_binds = new freelancer_binds();
    
    $valid = $freelancer_binds->isUserBinded($uid, $prof_id, $is_spec);

    if ($valid) {
        
        $bill = new billing($uid);
        //Допустимо использование промокодов
        $bill->setPromoCodes('SERVICE_FRLBIND', $promo_code);
        
        $op_code = $freelancer_binds->getOpCodeUp($prof_id, $is_spec);
        $option = array(
            'prof_id' => $prof_id
        );
        
        $ok = $bill->addServiceAndPayFromAccount($op_code, $option);

        if ($ok) {
            $is_error = false;
            $link = '/freelancers/';        
            if ($prof_id) {
                if ($is_spec) {
                    $link .= professions::GetProfLink($prof_id) . '/';
                } else {
                    $group = professions::GetGroup($prof_id, $error);
                    $link .= $group['link'] . '/';
                }
            }
            $objResponse->script("window.location.href = '{$link}';");
        }        
    }

    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPayment('frlbindup');
            if(qp) qp.show_error('Возникла ошибка при поднятии закрепления в каталоге!');
        ");
    }
        
    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * Это методы для разных видов оплаты но сгруппированные в яндекс кассе
 * 
 * @param type $type
 * @param type $data
 */
function quickPaymentFrlbindupDolcard($type, $data)
{
    return quickPaymentFrlbindupYandexKassa($type, $data);
}

function quickPaymentFrlbindupYa($type, $data)
{
    return quickPaymentFrlbindupYandexKassa($type, $data);
}

function quickPaymentFrlbindupWebmoney($type, $data)
{
    return quickPaymentFrlbindupYandexKassa($type, $data);
}

function quickPaymentFrlbindupAlfaclick($type, $data)
{
    return quickPaymentFrlbindupYandexKassa($type, $data);
}

function quickPaymentFrlbindupSberbank($type, $data)
{
    return quickPaymentFrlbindupYandexKassa($type, $data);
}


//------------------------------------------------------------------------------


/**
 * Оплата через яндекс кассу
 * 
 * @param type $type - тип оплаты
 * @param type $data - данные по параметрам покупаемой услуги
 * @return \xajaxResponse
 */
function quickPaymentFrlbindupYandexKassa($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    $prof_id = (int)@$data['prof_id'];
    $is_spec = (bool)@$data['is_spec'];
    $promo_code = (string)@$data['promo'];
    
    $pay_methods = array(
        quickPaymentPopup::PAYMENT_TYPE_CARD => yandex_kassa::PAYMENT_AC,
        quickPaymentPopup::PAYMENT_TYPE_YA => yandex_kassa::PAYMENT_YD,
        quickPaymentPopup::PAYMENT_TYPE_WM => yandex_kassa::PAYMENT_WM,
        quickPaymentPopup::PAYMENT_TYPE_ALFACLICK => yandex_kassa::PAYMENT_AB,
        quickPaymentPopup::PAYMENT_TYPE_SBERBANK => yandex_kassa::PAYMENT_SB
    );
    
    if(!isset($pay_methods[$type])) return $objResponse;
    
    $freelancer_binds = new freelancer_binds();
    
    $valid = $freelancer_binds->isUserBinded($uid, $prof_id, $is_spec);
    
    if ($valid) {
        $is_error = false;

        $bill = new billing($uid);
        //Допустимо использование промокодов
        $bill->setPromoCodes('SERVICE_FRLBIND', $promo_code);
        
        $op_code = $freelancer_binds->getOpCodeUp($prof_id, $is_spec);
        $option = array(
            'prof_id' => $prof_id
        );
        
        //Формируем заказ
        $billReserveId = $bill->addServiceAndCheckout($op_code, $option);
        $payed_sum = $bill->getRealPayedSum();        

        $payment = $pay_methods[$type];
        $yandex_kassa = new yandex_kassa();
        $html_form = $yandex_kassa->render(
                $payed_sum, 
                $bill->account->id, 
                $payment, 
                $billReserveId);

        $objResponse->script("
            var qp_form_wrapper = $$('#quick_payment_frlbindup .__quick_payment_form');
            if(qp_form_wrapper){    
                qp_form_wrapper.set('html','{$html_form}');
                qp_form_wrapper.getElement('form')[0].submit();
            }
        ");

        $link = '/freelancers/';        
        if ($prof_id) {
            if ($is_spec) {
                $link .= professions::GetProfLink($prof_id) . '/';
            } else {
                $group = professions::GetGroup($prof_id, $error);
                $link .= $group['link'] . '/';
            }
        }
        //сохранаем в сессию куда перейти при успешной покупке        
        $_SESSION[quickPaymentPopup::QPP_REDIRECT] = $link;
    }

    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPayment('frlbindup');
            if(qp) qp.show_error('Возникла ошибка при поднятии закрепления в каталоге!');
        ");
    }
        
    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * Оплата через Плати потом
 * 
 * @param type $type - тип оплаты
 * @param type $data - данные по параметрам покупаемой услуги
 * @return \xajaxResponse
 */
function quickPaymentFrlbindupPlatipotom($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    $prof_id = (int)@$data['prof_id'];
    $is_spec = (bool)@$data['is_spec'];
    $promo_code = (string)@$data['promo'];
    
    $freelancer_binds = new freelancer_binds();
    
    $valid = $freelancer_binds->isUserBinded($uid, $prof_id, $is_spec);
    
    if ($valid) {
        $is_error = false;

        $bill = new billing($uid);
        //Допустимо использование промокодов
        $bill->setPromoCodes('SERVICE_FRLBIND', $promo_code);
        
        $op_code = $freelancer_binds->getOpCodeUp($prof_id, $is_spec);
        $option = array(
            'prof_id' => $prof_id
        );
        
        //Формируем заказ
        $billReserveId = $bill->addServiceAndCheckout($op_code, $option);
        $payed_sum = $bill->getRealPayedSum();
        
        $platipotom = new platipotom();
        $html_form = $platipotom->render(
                $payed_sum, 
                $bill->account->id, 
                $billReserveId);

        if($html_form) {
            $objResponse->script("
                var qp_form_wrapper = $$('#quick_payment_frlbindup .__quick_payment_form');
                if(qp_form_wrapper){    
                    qp_form_wrapper.set('html','{$html_form}');
                    qp_form_wrapper.getElement('form')[0].submit();
                }
            ");

            $link = '/freelancers/';        
            if ($prof_id) {
                if ($is_spec) {
                    $link .= professions::GetProfLink($prof_id) . '/';
                } else {
                    $group = professions::GetGroup($prof_id, $error);
                    $link .= $group['link'] . '/';
                }
            }
            //сохранаем в сессию куда перейти при успешной покупке        
            $_SESSION[quickPaymentPopup::QPP_REDIRECT] = $link;
        }
    }

    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPayment('frlbindup');
            if(qp) qp.show_error('Возникла ошибка при поднятии закрепления в каталоге!');
        ");
    }
        
    return $objResponse;
}