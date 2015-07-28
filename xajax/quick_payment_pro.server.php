<?php

/**
 * Покупка ПРО. Оплата услуги.
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/platipotom.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/forms/ProForm.php');


//------------------------------------------------------------------------------


/**
 * Это методы для разных видов оплаты но сгруппированные в яндекс кассе
 * 
 * @param type $type
 * @param type $data
 */
function quickPaymentProDolcard($type, $data)
{
    return quickPaymentProYandexKassa($type, $data);
}

function quickPaymentProYa($type, $data)
{
    return quickPaymentProYandexKassa($type, $data);
}

function quickPaymentProWebmoney($type, $data)
{
    return quickPaymentProYandexKassa($type, $data);
}

function quickPaymentProAlfaclick($type, $data)
{
    return quickPaymentProYandexKassa($type, $data);
}

function quickPaymentProSberbank($type, $data)
{
    return quickPaymentProYandexKassa($type, $data);
}


/**
 * Оплата карусели через яндекс кассу
 * 
 * @param type $type
 * @param type $data
 * @return \xajaxResponse
 */
function quickPaymentProYandexKassa($type, $data)
{
    $uid = get_uid(false);
    
    $objResponse = new xajaxResponse();
    
    $pay_methods = array(
        quickPaymentPopup::PAYMENT_TYPE_CARD => yandex_kassa::PAYMENT_AC,
        quickPaymentPopup::PAYMENT_TYPE_YA => yandex_kassa::PAYMENT_YD,
        quickPaymentPopup::PAYMENT_TYPE_WM => yandex_kassa::PAYMENT_WM,
        quickPaymentPopup::PAYMENT_TYPE_ALFACLICK => yandex_kassa::PAYMENT_AB,
        quickPaymentPopup::PAYMENT_TYPE_SBERBANK => yandex_kassa::PAYMENT_SB
    );
    
    if(!isset($pay_methods[$type]) || !($uid > 0)) {
        return $objResponse;
    }
    
    $is_emp = is_emp();
    
    $form = new ProForm($is_emp);
    
    if (!$form->isValid($data)) {
        $objResponse->script("
            var qp = window.quick_ext_payment_factory.getQuickPayment('pro');
            if(qp) qp.show_error('К сожалению, в процессе оплаты произошла ошибка, и платеж не был завершен. Попробуйте провести оплату еще раз.');
        ");
        
        return $objResponse;
    }    
    
   
    $data = $form->getValues();
    $opcode = $data['type'];
    $promo_code = isset($data['promo']) ? $data['promo'] : "";
    
    
    $bill = new billing($uid);

    //Допустимо использование промокодов
    $bill->setPromoCodes('SERVICE_PRO', $promo_code);        
    
    //Формируем заказ
    $billReserveId = $bill->addServiceAndCheckout($opcode);
    $sum = $bill->getRealPayedSum();
      
    if ($sum > 0) { 
        $payment = $pay_methods[$type];
        $yandex_kassa = new yandex_kassa();
        $html_form = $yandex_kassa->render(
                $sum, 
                $bill->account->id, 
                $payment, 
                $billReserveId);

        $objResponse->script("
            var qp = window.quick_ext_payment_factory.getQuickPayment('pro');
            if (qp) qp.sendPaymentForm('{$html_form}');
        ");
            
        //сохранаем в сессию куда перейти при успешной покупке        
        $_SESSION[quickPaymentPopup::QPP_REDIRECT] = urldecode($_SESSION['ref_uri']); 
        
        $payed_sum = $bill->getOrderPayedSum(); 
        //@todo: функционал из старого попапа оплаты нужен рефакторинг
        $_SESSION['quickbuypro_is_begin'] = 1;
        $_SESSION['quickbuypro_success_opcode'] = $payed_sum;
        $_SESSION['quickbuypro_success_opcode2'] = $opcode;
        //$_SESSION['quickbuypro_redirect'] = urldecode($_SESSION['ref_uri']);
    }
    
    return $objResponse;
}


/**
 * Оплата с личного счета
 * 
 * @param type $type
 * @param array $data
 * @return type
 */
function quickPaymentProAccount($type, $data)
{
    $uid = get_uid(false);
    
    $objResponse = new xajaxResponse();
    
    if(!($uid > 0)) {
        return $objResponse;
    }
    
    
    $is_emp = is_emp();
    
    $form = new ProForm($is_emp);
    
    if (!$form->isValid($data)) {
        $objResponse->script("
            var qp = window.quick_ext_payment_factory.getQuickPayment('pro');
            if(qp) qp.show_error('К сожалению, в процессе оплаты произошла ошибка, и платеж не был завершен. Попробуйте провести оплату еще раз.');
        ");
        
        return $objResponse;
    }    
    
    $data = $form->getValues();
    $opcode = $data['type'];
    $promo_code = isset($data['promo']) ? $data['promo'] : "";
    
    $bill = new billing($uid);
    
    //Допустимо использование промокодов
    $bill->setPromoCodes('SERVICE_PRO', $promo_code);     
    
    $complete = $bill->addServiceAndPayFromAccount($opcode);

    if ($complete) {
        
        $payed_sum = $bill->getOrderPayedSum();
        $_SESSION['quickbuypro_success_opcode'] = $payed_sum;
        
        $uri = isset($_SESSION['quickbuypro_redirect'])? $_SESSION['quickbuypro_redirect']: '';
        $uri .= '?quickpro_ok=1';
        
        //@todo: ПРОФИ пока игнорируем
        /*
        if ($opcode == 164) {
            $uri = '/profi/?quickprofi_ok=1';
        }
        */
        
        $objResponse->script("window.location = '{$uri}';");
    }

    return $objResponse;
}


/**
 * Оплата карусели через Плати потом
 * 
 * @param type $type
 * @param type $data
 * @return \xajaxResponse
 */
function quickPaymentProPlatipotom($type, $data)
{
    $uid = get_uid(false);
    
    $objResponse = new xajaxResponse();
    
    if(!($uid > 0)) {
        return $objResponse;
    }
    
    $is_emp = is_emp();
    
     
    $form = new ProForm($is_emp);
    
    if (!$form->isValid($data)) {
        $objResponse->script("
            var qp = window.quick_ext_payment_factory.getQuickPayment('pro');
            if(qp) qp.show_error('К сожалению, в процессе оплаты произошла ошибка, и платеж не был завершен. Попробуйте провести оплату еще раз.');
        ");
        
        return $objResponse;
    }    
    
    $data = $form->getValues();
    $opcode = $data['type'];    
    $promo_code = isset($data['promo']) ? $data['promo'] : "";
    
    
    $bill = new billing($uid);
        
    //Допустимо использование промокодов
    $bill->setPromoCodes('SERVICE_PRO', $promo_code);     
    
    //Формируем заказ
    $billReserveId = $bill->addServiceAndCheckout($opcode);
    $sum = $bill->getRealPayedSum();
    
    $platipotom = new platipotom(true);
    if ($sum > 0 && 
        $sum <= $platipotom->getMaxPrice($bill->account->id)) {
        
        $html_form = $platipotom->render(
                $sum, 
                $bill->account->id, 
                $billReserveId);

        if($html_form) {
            $objResponse->script("
                var qp = window.quick_ext_payment_factory.getQuickPayment('pro');
                if (qp) qp.sendPaymentForm('{$html_form}');
            ");

            //сохранаем в сессию куда перейти при успешной покупке        
            $_SESSION[quickPaymentPopup::QPP_REDIRECT] = urldecode($_SESSION['ref_uri']);
            
            $payed_sum = $bill->getRealPayedSum();
            //@todo: функционал из старого попапа оплаты нужен рефакторинг
            $_SESSION['quickbuypro_is_begin'] = 1;
            $_SESSION['quickbuypro_success_opcode'] = $payed_sum;
            $_SESSION['quickbuypro_success_opcode2'] = $opcode;
            //$_SESSION['quickbuypro_redirect'] = $redirect;
        }
    }
    
    
    return $objResponse;
}