<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesHelper.php');


//------------------------------------------------------------------------------


/**
 * Это методы для разных видов оплаты но сгруппированные в яндекс кассе
 * 
 * @param type $type
 * @param type $data
 */
function quickPaymentReserveDolcard($type, $data)
{
    return quickPaymentReserveYandexKassa($type, $data);
}

function quickPaymentReserveYa($type, $data)
{
    return quickPaymentReserveYandexKassa($type, $data);
}

function quickPaymentReserveWebmoney($type, $data)
{
    return quickPaymentReserveYandexKassa($type, $data);
}

function quickPaymentReserveAlfaclick($type, $data)
{
    return quickPaymentReserveYandexKassa($type, $data);
}

function quickPaymentReserveSberbank($type, $data)
{
    return quickPaymentReserveYandexKassa($type, $data);
}

/**
 * Резервирование средств через яндекс кассу
 * 
 * @param type $type
 * @param type $data
 * @return \xajaxResponse
 */
function quickPaymentReserveYandexKassa($type, $data)
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
    
    if (!isset($pay_methods[$type]))  {
        return $objResponse;
    }
    
    $reserve_id = intval(@$data['quick_payment_reserve_form']);
    $reserveInstance = ReservesModelFactory::getInstanceById($reserve_id);
    if(!$reserveInstance) return $objResponse;
    $reserve_data = $reserveInstance->getReserveData();
    
    if($reserve_data['emp_id'] != $uid || 
       !$reserveInstance->isEmpAllowFinance()) {
        return $objResponse;
    }
    
    //Если уже был платеж то обновляем страницу
    if(!$reserveInstance->isStatusNew()) {
        $objResponse->script("window.location.reload()");
        return $objResponse;
    }
    
    //Формируем заказ
    $bill = new billing($uid);
    $billReserveId = $bill->addServiceAndCheckout(
            ReservesModel::OPCODE_RESERVE,
            array(
                'acc_sum' => $reserve_data['reserve_price'],
                'reserve_data' => $reserve_data
            ));
    

    $payment = $pay_methods[$type];
    
    $yandex_kassa = new yandex_kassa();
    $yandex_kassa->setShop(yandex_kassa::SHOPID_SBR);
    $html_form = $yandex_kassa->render(
            $bill->getOrderPayedSum(), 
            $bill->getAccId(), 
            $payment, 
            $billReserveId);

    $objResponse->script("
        var qp_form_wrapper = $$('#quick_payment_reserve .__quick_payment_form');
        if(qp_form_wrapper){    
            qp_form_wrapper.set('html','{$html_form}');
            qp_form_wrapper.getElement('form')[0].submit();
        }
    ");
    
         
    $_SESSION[quickPaymentPopup::QPP_REDIRECT] = $reserveInstance->getTypeUrl();
    
    return $objResponse;
}


//------------------------------------------------------------------------------

/**
 * Выставяем счет оплаты по безналу для резерва
 * 
 * @param string $type - тип платежа (тут всегда bank)
 * @param array $data
 * @return \xajaxResponse
 */
function quickPaymentReserveBank($type, $data)
{
    $objResponse = &new xajaxResponse();
    
    $uid = get_uid(false);
    if($uid <= 0) return $objResponse;
    
    //Проверка на юрика
    $reqvs = ReservesHelper::getInstance()->getUserReqvs($uid);
    if($reqvs['form_type'] != sbr::FT_JURI) return $objResponse;
    $reqv = $reqvs[$reqvs['form_type']];
    
    //Проверка наличия резерва средств
    $reserve_id = intval(@$data['quick_payment_reserve_form']);
    $reserveInstance = ReservesModelFactory::getInstanceById($reserve_id);
    if(!$reserveInstance) return $objResponse;
    $reserve_data = $reserveInstance->getReserveData();
    
    if ($reserve_data['emp_id'] != $uid || 
       !$reserveInstance->isEmpAllowFinance()) {
        return $objResponse;
    }
       
    //Если уже был платеж то обновляем страницу
    if (!$reserveInstance->isStatusNew()) {
        $objResponse->script("window.location.reload()");
        return $objResponse;
    }
    
    $reqv['is_send_docs'] = @$data['is_reserve_send_docs'] == 1;
    
    $file = $reserveInstance->getReservesBank()->generateInvoice2($reqv, true);
    
    if(!$file){
        $objResponse->script("
            var qp_reserve = quick_payment_factory.getQuickPayment('reserve');
            if(qp_reserve) qp_reserve.show_error('Не удалось создать файл счета. Попробуйте еще раз.');
        ");
        return $objResponse;
    }
    
    $success_text = 'Для резервирования суммы на сайте вам был сформирован '
            . '<a href="'.WDCPREFIX.'/'.$file->path.$file->name.'" target="_blank">счет на оплату</a>.<br />'
            . 'Счет также доступен вам в заказе в списке загруженных документов.';
    
    $objResponse->script("
        var qp_reserve = quick_payment_factory.getQuickPayment('reserve');
        if(qp_reserve) qp_reserve.show_success('".$success_text."');
    ");

    return $objResponse;
}