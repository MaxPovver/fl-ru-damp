<?php

/**
 *  Пополнение ЛС
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupAccount.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
//------------------------------------------------------------------------------

/**
 * Это методы для разных видов оплаты но сгруппированные в яндекс кассе
 * 
 * @param type $type
 * @param type $data
 */
function quickPaymentAccountDolcard($type, $data)
{
    return quickPaymentAccountYandexKassa($type, $data);
}

function quickPaymentAccountYa($type, $data)
{
    return quickPaymentAccountYandexKassa($type, $data);
}

function quickPaymentAccountWebmoney($type, $data)
{
    return quickPaymentAccountYandexKassa($type, $data);
}

function quickPaymentAccountAlfaclick($type, $data)
{
    return quickPaymentAccountYandexKassa($type, $data);
}

function quickPaymentAccountSberbank($type, $data)
{
    return quickPaymentAccountYandexKassa($type, $data);
}

/**
 * Пополнение счета через яндекс кассу
 * 
 * @param type $type - тип оплаты
 * @param type $data - данные по параметрам покупаемой услуги
 * @return \xajaxResponse
 */
function quickPaymentAccountYandexKassa($type, $data)
{
    $is_error = true;
    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    $price = (int)@$data['price'];
    
    $pay_methods = array(
        quickPaymentPopup::PAYMENT_TYPE_CARD => yandex_kassa::PAYMENT_AC,
        quickPaymentPopup::PAYMENT_TYPE_YA => yandex_kassa::PAYMENT_YD,
        quickPaymentPopup::PAYMENT_TYPE_WM => yandex_kassa::PAYMENT_WM,
        quickPaymentPopup::PAYMENT_TYPE_ALFACLICK => yandex_kassa::PAYMENT_AB,
        quickPaymentPopup::PAYMENT_TYPE_SBERBANK => yandex_kassa::PAYMENT_SB
    );
    
    if (!isset($pay_methods[$type])) { 
        return $objResponse;
    }
    
    $allow = !(sbr_meta::isFtJuri($uid));
    
    if($allow) {
        $is_error = false;
        $billReserveId = null;
        
        $bill = new billing($uid);
        
        $minPrice = quickPaymentPopupAccount::PRICE_MIN;
        if ($bill->getAccSum() < 0) {
           $debt = abs($bill->getAccSum());
           $minPrice = $debt > $minPrice? $debt:$minPrice;
           
           if ($price >= $minPrice) {
               
                $option = array('acc_sum' => $minPrice);
                //Автоматическая покупка услуги погашения задолженности
                $billReserveId = $bill->addServiceAndCheckout(135, $option);
                
           }
        }
        
        $payment = $pay_methods[$type];
        
        if ($price < $minPrice 
                || $price > quickPaymentPopupAccount::PRICE_MAX
                || ($payment == yandex_kassa::PAYMENT_WM 
                    && $price > quickPaymentPopupAccount::PRICE_MAX_WM)
            ) {
            
            $is_error = true;
        }
        
        if (!$is_error) {
            $yandex_kassa = new yandex_kassa();
            $html_form = $yandex_kassa->render(
                    $price, 
                    $bill->account->id, 
                    $payment, 
                    $billReserveId);

            $objResponse->script("
                var qp_form_wrapper = $$('#quick_payment_account .__quick_payment_form');
                if(qp_form_wrapper){    
                    qp_form_wrapper.set('html','{$html_form}');
                    qp_form_wrapper.getElement('form')[0].submit();
                }
            ");
            $link = '/bill/history/?period=3';
            //сохраняем в сессию куда перейти при успешной покупке
            $_SESSION[quickPaymentPopup::QPP_REDIRECT] = $link;
        }
    }
    
    // Показываем предупреждение в случае ошибки
    if ($is_error) {
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPayment('account');
            if(qp) qp.show_error('Возникла ошибка при пополнении счета!');
        ");
    }

    return $objResponse;
}