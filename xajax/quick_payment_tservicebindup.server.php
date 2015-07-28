<?php

/**
 * Поднятие закрепления в каталоге ТУ.
 * Оплата услуги.
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/platipotom.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_binds.php");


//------------------------------------------------------------------------------

/**
 * Оплата из буфера
 * @param type $type
 * @param array $data (prof_id)
 * @return type
 */
function quickPaymentTservicebindupBuffer($type, $data)
{
    $is_error = true;
    $uid = get_uid(false);
    $objResponse = &new xajaxResponse();
    $kind = (int)@$data['kind'];
    $tservice_id = (int)@$data['tservice_text_db_id'];
    $prof_id = (int)@$data['prof_id'];
    $is_spec = $kind == tservices_binds::KIND_SPEC;

    $tservices_binds = new tservices_binds($kind);
    $bind = $tservices_binds->getItem($uid, $tservice_id, $prof_id);

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/buffer.php");
    $buffer = new buffer();
    $buffer_sum = $buffer->getSum();
    
    $price = $tservices_binds->getPrice(true, $uid, $prof_id);

    if ($bind && $buffer_sum >= $price) {
        $bill = new billing($uid);
        
        $ok = $bill->addServiceAndPayFromAccount(tservices_binds::OP_CODE_UP_BUFFER, array(
            'bind_id' => $bind['id']
        ));
        
        if ($ok) {
            $is_error = false;
            $buffer->setUsedSum($price);
            $objResponse->script("window.location.reload();");
        }
    }
    
    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $idx = quickPaymentPopupTservicebindup::getPopupId($tservice_id);
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPaymentById('tservicebindup', '".$idx."');
            if(qp) qp.show_error('Возникла ошибка при поднятии закрепления услуги!');
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
function quickPaymentTservicebindupAccount($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    $kind = (int)@$data['kind'];
    $tservice_id = (int)@$data['tservice_text_db_id'];
    $prof_id = (int)@$data['prof_id'];
    $promo_code = (string)@$data['promo'];
    
    $tservices_binds = new tservices_binds($kind);
    $bind = $tservices_binds->getItem($uid, $tservice_id, $prof_id);

    if ($bind) {
        
        $bill = new billing($uid);
        //Допустимо использование промокодов
        $bill->setPromoCodes('SERVICE_TSERVICEBIND', $promo_code);         
        
        $op_code = $tservices_binds->getOpCode(true);
        $option = array(
            'bind_id' => $bind['id'],
            'prof_id' => $prof_id
        );
        
        $ok = $bill->addServiceAndPayFromAccount($op_code, $option);
        
        if ($ok) {
            $is_error = false;
            $objResponse->script("window.location.reload();");
        }
    }

    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $idx = quickPaymentPopupTservicebindup::getPopupId($tservice_id);
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPaymentById('tservicebindup', '".$idx."');
            if(qp) qp.show_error('Возникла ошибка при поднятии закрепления услуги!');
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
function quickPaymentTservicebindupDolcard($type, $data)
{
    return quickPaymentTservicebindupYandexKassa($type, $data);
}

function quickPaymentTservicebindupYa($type, $data)
{
    return quickPaymentTservicebindupYandexKassa($type, $data);
}

function quickPaymentTservicebindupWebmoney($type, $data)
{
    return quickPaymentTservicebindupYandexKassa($type, $data);
}

function quickPaymentTservicebindupAlfaclick($type, $data)
{
    return quickPaymentTservicebindupYandexKassa($type, $data);
}

function quickPaymentTservicebindupSberbank($type, $data)
{
    return quickPaymentTservicebindupYandexKassa($type, $data);
}


//------------------------------------------------------------------------------


/**
 * Резервирование средств через яндекс кассу
 * 
 * @param type $type - тип оплаты
 * @param type $data - данные по параметрам покупаемой услуги
 * @return \xajaxResponse
 */
function quickPaymentTservicebindupYandexKassa($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    $kind = (int)@$data['kind'];
    $tservice_id = (int)@$data['tservice_text_db_id'];
    $prof_id = (int)@$data['prof_id'];
    //$is_prolong = (bool)@$data['is_prolong'];//@todo: накой?
    $promo_code = (string)@$data['promo'];
    
    $pay_methods = array(
        quickPaymentPopup::PAYMENT_TYPE_CARD => yandex_kassa::PAYMENT_AC,
        quickPaymentPopup::PAYMENT_TYPE_YA => yandex_kassa::PAYMENT_YD,
        quickPaymentPopup::PAYMENT_TYPE_WM => yandex_kassa::PAYMENT_WM,
        quickPaymentPopup::PAYMENT_TYPE_ALFACLICK => yandex_kassa::PAYMENT_AB,
        quickPaymentPopup::PAYMENT_TYPE_SBERBANK => yandex_kassa::PAYMENT_SB
    );
    
    if(!isset($pay_methods[$type])) return $objResponse;
    
    $tservices_binds = new tservices_binds($kind);
    
    $bind = $tservices_binds->getItem($uid, $tservice_id, $prof_id);
    
    if ($bind) {
        $is_error = false;
        
        $bill = new billing($uid);
        //Допустимо использование промокодов
        $bill->setPromoCodes('SERVICE_TSERVICEBIND', $promo_code); 
        
        $op_code = $tservices_binds->getOpCode(true);
        $option = array('bind_id' => $bind['id']);
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

        $idx = quickPaymentPopupTservicebindup::getPopupId($tservice_id);
        $objResponse->script("
            var qp_form_wrapper = $$('#".$idx." .__quick_payment_form');
            if(qp_form_wrapper){    
                qp_form_wrapper.set('html','{$html_form}');
                qp_form_wrapper.getElement('form')[0].submit();
            }
        ");

        //сохранаем в сессию куда перейти при успешной покупке        
        $redirect = (string)@$data['redirect'];
        $_SESSION[quickPaymentPopup::QPP_REDIRECT] = $redirect;
    }

    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $idx = quickPaymentPopupTservicebindup::getPopupId($tservice_id);
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPaymentById('tservicebindup', '".$idx."');
            if(qp) qp.show_error('Возникла ошибка при поднятии закрепления услуги!');
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
function quickPaymentTservicebindupPlatipotom($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    $kind = (int)@$data['kind'];
    $tservice_id = (int)@$data['tservice_text_db_id'];
    $prof_id = (int)@$data['prof_id'];
    //$is_prolong = (bool)@$data['is_prolong'];
    $promo_code = (string)@$data['promo'];
    
    $tservices_binds = new tservices_binds($kind);
    
    $bind = $tservices_binds->getItem($uid, $tservice_id, $prof_id);
    
    if ($bind) {
        $is_error = false;

        $bill = new billing($uid);
        //Допустимо использование промокодов
        $bill->setPromoCodes('SERVICE_TSERVICEBIND', $promo_code);        
        
        $op_code = $tservices_binds->getOpCode(true);
        $option = array('bind_id' => $bind['id']);
         //Формируем заказ
        $billReserveId = $bill->addServiceAndCheckout($op_code, $option);
        $payed_sum = $bill->getRealPayedSum();

        $platipotom = new platipotom();
        $html_form = $platipotom->render(
                $payed_sum, 
                $bill->account->id, 
                $billReserveId);

        if($html_form) {

            $idx = quickPaymentPopupTservicebindup::getPopupId($tservice_id);
            $objResponse->script("
                var qp_form_wrapper = $$('#".$idx." .__quick_payment_form');
                if(qp_form_wrapper){    
                    qp_form_wrapper.set('html','{$html_form}');
                    qp_form_wrapper.getElement('form')[0].submit();
                }
            ");

            //сохранаем в сессию куда перейти при успешной покупке        
            $redirect = (string)@$data['redirect'];
            $_SESSION[quickPaymentPopup::QPP_REDIRECT] = $redirect;
        }
    }

    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $idx = quickPaymentPopupTservicebindup::getPopupId($tservice_id);
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPaymentById('tservicebindup', '".$idx."');
            if(qp) qp.show_error('Возникла ошибка при поднятии закрепления услуги!');
        ");
    }
        
    return $objResponse;
}