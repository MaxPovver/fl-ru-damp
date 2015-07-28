<?php

/**
 * Рассылка по личке.
 * Оплата услуги.
 */


require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupFactory.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/masssending.php';


//------------------------------------------------------------------------------


/**
 * Оплата с личного счета
 * @param type $type
 * @param array $data (weeks, prof_id, is_spec)
 * @return type
 */
function quickPaymentMasssendingAccount($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();
    
    $send_id = (int)$data['send_id'];
    $promo_code = (string)@$data['promo'];

    $masssending = new masssending();
    
    $params = $masssending->getAccepted($send_id, $uid);
    
    if ($params) {
    
        $bill = new billing(get_uid(false));
        //Допустимо использование промокодов
        $bill->setPromoCodes('SERVICE_MASSSENDING', $promo_code);     

        $option = array(
            'amount' => $params['pre_sum'],
            'masssending_id' => $params['id'],
        );
        
        $complete = $bill->addServiceAndPayFromAccount(
                masssending::OPER_CODE, 
                $option);
        
        if ($complete) {
            $is_error = false;
            $_SESSION['quickmss_ok'] = 1;
            $objResponse->script("window.location = '/bill/history/?period=3';");
        }
    }

    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPayment('masssending');
            if(qp) qp.show_error('Возникла ошибка при оплате рассылки!');
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
function quickPaymentMasssendingDolcard($type, $data)
{
    return quickPaymentMasssendingYandexKassa($type, $data);
}

function quickPaymentMasssendingYa($type, $data)
{
    return quickPaymentMasssendingYandexKassa($type, $data);
}

function quickPaymentMasssendingWebmoney($type, $data)
{
    return quickPaymentMasssendingYandexKassa($type, $data);
}

function quickPaymentMasssendingAlfaclick($type, $data)
{
    return quickPaymentMasssendingYandexKassa($type, $data);
}

function quickPaymentMasssendingSberbank($type, $data)
{
    return quickPaymentMasssendingYandexKassa($type, $data);
}


//------------------------------------------------------------------------------


/**
 * Оплата через яндекс кассу
 * 
 * @param type $type - тип оплаты
 * @param type $data - данные по параметрам покупаемой услуги
 * @return \xajaxResponse
 */
function quickPaymentMasssendingYandexKassa($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();
    
    $send_id = (int)$data['send_id'];
    $promo_code = (string)@$data['promo'];
    
    $pay_methods = array(
        quickPaymentPopup::PAYMENT_TYPE_CARD => yandex_kassa::PAYMENT_AC,
        quickPaymentPopup::PAYMENT_TYPE_YA => yandex_kassa::PAYMENT_YD,
        quickPaymentPopup::PAYMENT_TYPE_WM => yandex_kassa::PAYMENT_WM,
        quickPaymentPopup::PAYMENT_TYPE_ALFACLICK => yandex_kassa::PAYMENT_AB,
        quickPaymentPopup::PAYMENT_TYPE_SBERBANK => yandex_kassa::PAYMENT_SB
    );
    
    if(!isset($pay_methods[$type])) return $objResponse;

    $masssending = new masssending();
    
    $params = $masssending->getAccepted($send_id, $uid);
    
    if ($params) {
        $is_error = false;
        
        $bill = new billing(get_uid(false));
        //Допустимо использование промокодов
        $bill->setPromoCodes('SERVICE_MASSSENDING', $promo_code);  
        
        $option = array(
            'amount' => $params['pre_sum'],
            'masssending_id' => $params['id'],
        );
        
        //Формируем заказ
        $billReserveId = $bill->addServiceAndCheckout(
                masssending::OPER_CODE, 
                $option);
        $payed_sum = $bill->getRealPayedSum();
        
        $payment = $pay_methods[$type];
        $yandex_kassa = new yandex_kassa();
        $html_form = $yandex_kassa->render(
                $payed_sum, 
                $bill->account->id, 
                $payment, 
                $billReserveId);
        
        $objResponse->script("
            var qp_form_wrapper = $$('#quick_payment_masssending .__quick_payment_form');
            if(qp_form_wrapper){    
                qp_form_wrapper.set('html','{$html_form}');
                qp_form_wrapper.getElement('form')[0].submit();
            }
        ");

        //сохранаем в сессию куда перейти при успешной покупке        
        $_SESSION[quickPaymentPopup::QPP_REDIRECT] = '/bill/history/?period=3';
    }

    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPayment('masssending');
            if(qp) qp.show_error('Возникла ошибка при оплате рассылки!');
        ");
    }
        
    return $objResponse;
}