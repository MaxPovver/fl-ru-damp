<?php

/**
 * Место в карусели. Оплата услуги.
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/platipotom.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/forms/CaruselForm.php');


//------------------------------------------------------------------------------


/**
 * Это методы для разных видов оплаты но сгруппированные в яндекс кассе
 * 
 * @param type $type
 * @param type $data
 */
function quickPaymentCaruselDolcard($type, $data)
{
    return quickPaymentCaruselYandexKassa($type, $data);
}

function quickPaymentCaruselYa($type, $data)
{
    return quickPaymentCaruselYandexKassa($type, $data);
}

function quickPaymentCaruselWebmoney($type, $data)
{
    return quickPaymentCaruselYandexKassa($type, $data);
}

function quickPaymentCaruselAlfaclick($type, $data)
{
    return quickPaymentCaruselYandexKassa($type, $data);
}

function quickPaymentCaruselSberbank($type, $data)
{
    return quickPaymentCaruselYandexKassa($type, $data);
}


/**
 * Оплата карусели через яндекс кассу
 * 
 * @param type $type
 * @param type $data
 * @return \xajaxResponse
 */
function quickPaymentCaruselYandexKassa($type, $data)
{
    $uid = get_uid(false);
    
    $objResponse = &new xajaxResponse();
    
    $pay_methods = array(
        quickPaymentPopup::PAYMENT_TYPE_CARD => yandex_kassa::PAYMENT_AC,
        quickPaymentPopup::PAYMENT_TYPE_YA => yandex_kassa::PAYMENT_YD,
        quickPaymentPopup::PAYMENT_TYPE_WM => yandex_kassa::PAYMENT_WM,
        quickPaymentPopup::PAYMENT_TYPE_ALFACLICK => yandex_kassa::PAYMENT_AB,
        quickPaymentPopup::PAYMENT_TYPE_SBERBANK => yandex_kassa::PAYMENT_SB
    );
    
    if(!isset($pay_methods[$type]) || !$uid || is_emp()) {
        return $objResponse;
    }
    
    $promo = isset($data['promo']) ? $data['promo'] : "";
    
    $form = new CaruselForm();
    
    if (!$form->isValid($data)) {
        $params = addslashes(urldecode(http_build_query($form->getAllMessages('<br/>'))));
        $objResponse->script("
            var qp = window.quick_ext_payment_factory.getQuickPayment('carusel');
            if(qp) qp.showElementsError('{$params}');
        ");
        return $objResponse;
    }
    
    $data = $form->getValues();
    
    $bill = new billing($uid);
    
    $bill->setPromoCodes('SERVICE_CARUSEL', $promo);
    
    $options = array(
        'ad_header' => $data['title'],
        'ad_text' => $data['description'],
        'num' => $data['num']
    );
    
    if($data['num'] > 1) {
        $options['hours'] = $data['hours'];
    }
    
    //Каталог
    //@todo: неиспользуется разделение платный мест в картусели
    //$tarif = 73;
    
    //Главная
    //@todo: сейчас общий раздел для всех
    $tarif = 65;
    
    //Формируем заказ
    $billReserveId = $bill->addServiceAndCheckout($tarif, $options);
    $payed_sum = $bill->getRealPayedSum();
    
    $payment = $pay_methods[$type];
    $yandex_kassa = new yandex_kassa();
    $html_form = $yandex_kassa->render(
            $payed_sum, 
            $bill->account->id, 
            $payment, 
            $billReserveId);

    $objResponse->script("
        var qp = window.quick_ext_payment_factory.getQuickPayment('carusel');
        if (qp) qp.sendPaymentForm('{$html_form}');
    ");
    
    //сохранаем в сессию куда перейти при успешной покупке        
    $_SESSION[quickPaymentPopup::QPP_REDIRECT] = urldecode($_SESSION['ref_uri']);   
        
    return $objResponse;
}


/**
 * Оплата с личного счета
 * 
 * @param type $type
 * @param array $data
 * @return type
 */
function quickPaymentCaruselAccount($type, $data)
{
    $uid = get_uid(false);
    
    $objResponse = &new xajaxResponse();
    
    if(!$uid || is_emp()) {
        return $objResponse;
    }
    
    $promo = isset($data['promo']) ? $data['promo'] : "";
    
    $form = new CaruselForm();
    
    if (!$form->isValid($data)) {
        $params = addslashes(urldecode(http_build_query($form->getAllMessages('<br/>'))));
        $objResponse->script("
            var qp = window.quick_ext_payment_factory.getQuickPayment('carusel');
            if(qp) qp.showElementsError('{$params}');
        ");
        return $objResponse;
    }
    
    $data = $form->getValues();
    
    $bill = new billing($uid);
    
    $bill->setPromoCodes('SERVICE_CARUSEL', $promo);
    
    $options = array(
        'ad_header' => $data['title'],
        'ad_text' => $data['description'],
        'num' => $data['num']
    );
    
    if($data['num'] > 1) {
        $options['hours'] = $data['hours'];
    }
    
    //Каталог
    //@todo: неиспользуется разделение платный мест в карусели
    //$tarif = 73;
    
    //Главная
    //@todo: сейчас общий раздел для всех
    $tarif = 65;
    
    $complete = $bill->addServiceAndPayFromAccount($tarif, $options);

    if ($complete) {
        $objResponse->script("window.location.reload();");
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
function quickPaymentCaruselPlatipotom($type, $data)
{
    $uid = get_uid(false);
    
    $objResponse = &new xajaxResponse();
    
    $promo = isset($data['promo']) ? $data['promo'] : "";
    
    $form = new CaruselForm();
    
    if (!$form->isValid($data)) {
        $params = addslashes(urldecode(http_build_query($form->getAllMessages('<br/>'))));
        $objResponse->script("
            var qp = window.quick_ext_payment_factory.getQuickPayment('carusel');
            if(qp) qp.showElementsError('{$params}');
        ");
        return $objResponse;
    }
    
    $data = $form->getValues();
    
    $bill = new billing($uid);
    
    $bill->setPromoCodes('SERVICE_CARUSEL', $promo);
    
    $options = array(
        'ad_header' => $data['title'],
        'ad_text' => $data['description'],
        'num' => $data['num']
    );
    
    if($data['num'] > 1) {
        $options['hours'] = $data['hours'];
    }
    
    //Каталог
    //@todo: неиспользуется разделение платный мест в картусели
    //$tarif = 73;
    
    //Главная
    //@todo: сейчас общий раздел для всех
    $tarif = 65;
    
    //Формируем заказ
    $billReserveId = $bill->addServiceAndCheckout($tarif, $options);
    $payed_sum = $bill->getRealPayedSum();
    
    $platipotom = new platipotom();
    $html_form = $platipotom->render(
            $payed_sum, 
            $bill->account->id, 
            $billReserveId);
            
    if($html_form) {
        $objResponse->script("
            var qp = window.quick_ext_payment_factory.getQuickPayment('carusel');
            if (qp) qp.sendPaymentForm('{$html_form}');
        ");

        //сохранаем в сессию куда перейти при успешной покупке        
        $_SESSION[quickPaymentPopup::QPP_REDIRECT] = urldecode($_SESSION['ref_uri']);   
    }
        
    return $objResponse;
}