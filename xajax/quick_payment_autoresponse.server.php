<?php

/**
 * Автоответы. Оплата услуги.
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/platipotom.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/autoresponse.php");


//------------------------------------------------------------------------------

/**
 * Оплата с личного счета
 * @param type $type
 * @param type $data
 * @return type
 */
function quickPaymentAutoresponseAccount($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    /*
    $id = isset($data['id'])?intval($data['id']):0;
    $promo_code = @$data['promo'];

    // БД с данными
    autoresponse::$db = $GLOBALS['DB'];
    
    $autoresponse = autoresponse::get($id);
    if ($autoresponse && !$autoresponse->isActive()) {
        if ($autoresponse->data['user_id'] == $uid && intval($autoresponse->data['price'])) {
            $is_error = false;
            
            //Нужно получить стоимость услуги по данным о услуги из $data
            $sum = (int) $autoresponse->data['price'];
            
            $bill = new billing($uid);
            //Допустимо использование промокодов
            $bill->setPromoCodes('SERVICE_AUTORESPONSE', $promo_code, array(
                'is_original_price' => true
            ));
            
            //разные опции бывают
            $option = array(
                'acc_sum' => $sum,
                'autoresponse_id' => $autoresponse->data['id'],
            );   
            
            $bill->addServiceAndPayFromAccount(137, $option);
            
            $objResponse->script("window.location = '/autoresponse/';");
        }
    }

    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $objResponse->script("
            var qpa = window.quick_payment_factory.getQuickPayment('autoresponse');
            if(qpa) qpa.show_error('Возникла ошибка при добавлении автоответа!');
        ");
    }
    */
        
    return $objResponse;
}

//------------------------------------------------------------------------------

/**
 * Это методы для разных видов оплаты но сгруппированные в яндекс кассе
 * 
 * @param type $type
 * @param type $data
 */
function quickPaymentAutoresponseDolcard($type, $data)
{
    return quickPaymentAutoresponseYandexKassa($type, $data);
}

function quickPaymentAutoresponseYa($type, $data)
{
    return quickPaymentAutoresponseYandexKassa($type, $data);
}

function quickPaymentAutoresponseWebmoney($type, $data)
{
    return quickPaymentAutoresponseYandexKassa($type, $data);
}

function quickPaymentAutoresponseAlfaclick($type, $data)
{
    return quickPaymentAutoresponseYandexKassa($type, $data);
}

function quickPaymentAutoresponseSberbank($type, $data)
{
    return quickPaymentAutoresponseYandexKassa($type, $data);
}

//------------------------------------------------------------------------------

/**
 * Оплата автоответов через яндекс кассу
 * 
 * @param type $type - тип оплаты
 * @param type $data - данные по параметрам покупаемой услуги
 * @return \xajaxResponse
 */
function quickPaymentAutoresponseYandexKassa($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    /*
    $pay_methods = array(
        quickPaymentPopup::PAYMENT_TYPE_CARD => yandex_kassa::PAYMENT_AC,
        quickPaymentPopup::PAYMENT_TYPE_YA => yandex_kassa::PAYMENT_YD,
        quickPaymentPopup::PAYMENT_TYPE_WM => yandex_kassa::PAYMENT_WM,
        quickPaymentPopup::PAYMENT_TYPE_ALFACLICK => yandex_kassa::PAYMENT_AB,
        quickPaymentPopup::PAYMENT_TYPE_SBERBANK => yandex_kassa::PAYMENT_SB
    );
    
    if(!isset($pay_methods[$type])) return $objResponse;

    $id = isset($data['id'])?intval($data['id']):0;
    $promo_code = @$data['promo'];
    
    // БД с данными
    autoresponse::$db = $GLOBALS['DB'];
    
    $autoresponse = autoresponse::get($id);
    if ($autoresponse && !$autoresponse->isActive()) {
        if ($autoresponse->data['user_id'] == $uid && 
            intval($autoresponse->data['price'])) {
            
            $is_error = false;

            //Нужно получить стоимость услуги по данным о услуги из $data
            $sum = (int) $autoresponse->data['price'];
            
            $bill = new billing($uid);
            //Допустимо использование промокодов
            $bill->setPromoCodes('SERVICE_AUTORESPONSE', $promo_code, array(
                'is_original_price' => true
            ));
            
            //разные опции бывают
            $option = array(
                'acc_sum' => $sum,
                'autoresponse_id' => $autoresponse->data['id'],
            );   

            //Формируем заказ
            $billReserveId = $bill->addServiceAndCheckout(137, $option);
            $payed_sum = $bill->getRealPayedSum();

            $payment = $pay_methods[$type];
            $yandex_kassa = new yandex_kassa();
            $html_form = $yandex_kassa->render(
                    $payed_sum, 
                    $bill->account->id, 
                    $payment, 
                    $billReserveId);

            $objResponse->script("
                var qp_form_wrapper = $$('#quick_payment_autoresponse .__quick_payment_form');
                if(qp_form_wrapper){    
                    qp_form_wrapper.set('html','{$html_form}');
                    qp_form_wrapper.getElement('form')[0].submit();
                }
            ");
            
            //сохранаем в сессию куда перейти при успешной покупке        
            $_SESSION[quickPaymentPopup::QPP_REDIRECT] = '/autoresponse/';            
        }
    }

    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $objResponse->script("
            var qpa = window.quick_payment_factory.getQuickPayment('autoresponse');
            if(qpa) qpa.show_error('Возникла ошибка при добавлении автоответа!');
        ");
    }
     */   
    
    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * Оплата автоответов через Плати потом
 * 
 * @param type $type - тип оплаты
 * @param type $data - данные по параметрам покупаемой услуги
 * @return \xajaxResponse
 */
function quickPaymentAutoresponsePlatipotom($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    /*
    $id = isset($data['id'])?intval($data['id']):0;
    $promo_code = @$data['promo'];

    // БД с данными
    autoresponse::$db = $GLOBALS['DB'];
    
    $autoresponse = autoresponse::get($id);
    if ($autoresponse && !$autoresponse->isActive()) {
        if ($autoresponse->data['user_id'] == $uid && 
            intval($autoresponse->data['price'])) {
            
            $is_error = false;

            //Нужно получить стоимость услуги по данным о услуги из $data
            $sum = (int) $autoresponse->data['price'];
            

            $bill = new billing($uid);
            
            //Допустимо использование промокодов
            $bill->setPromoCodes('SERVICE_AUTORESPONSE', $promo_code, array(
                'is_original_price' => true
            ));
            
            //разные опции бывают
            $option = array(
                'acc_sum' => $sum,
                'autoresponse_id' => $autoresponse->data['id'],
            );              
            
            //Формируем заказ
            $billReserveId = $bill->addServiceAndCheckout(137, $option);
            $payed_sum = $bill->getRealPayedSum();            
            
            $platipotom = new platipotom();
            $html_form = $platipotom->render(
                    $payed_sum, 
                    $bill->account->id, 
                    $billReserveId);

            if($html_form) {
                $objResponse->script("
                    var qp_form_wrapper = $$('#quick_payment_autoresponse .__quick_payment_form');
                    if(qp_form_wrapper){    
                        qp_form_wrapper.set('html','{$html_form}');
                        qp_form_wrapper.getElement('form')[0].submit();
                    }
                ");

                //сохранаем в сессию куда перейти при успешной покупке        
                $_SESSION[quickPaymentPopup::QPP_REDIRECT] = '/autoresponse/';  
            }
        }
    }

    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $objResponse->script("
            var qpa = window.quick_payment_factory.getQuickPayment('autoresponse');
            if(qpa) qpa.show_error('Возникла ошибка при добавлении автоответа!');
        ");
    }
    */
        
    return $objResponse;
}