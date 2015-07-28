<?php

$rpath = "../";
require_once ($_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/yandex_kassa.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/platipotom.php");

session_start();

/**
 * Покупка ПРО через ЛС
 * 
 * @param type $opcode
 * @param type $redirect
 * @return \xajaxResponse
 */
function quickPROPayAccount($opcode, $redirect, $promo_code) 
{
    $objResponse = new xajaxResponse();

    $pro = 0;
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
    $proList = payed::getAvailablePayedList(is_emp());
    foreach($proList as $proItem) {
        if($proItem['opcode']==$opcode) { $pro = $proItem; break; }
    }

    if ($pro) {

        $uid = get_uid(false);
        $bill = new billing($uid);
        
        //Допустимо использование промокодов
        $bill->setPromoCodes('SERVICE_PRO', $promo_code); 
        
        $billReserveId = $bill->addServiceAndCheckout($opcode);
        $payed_sum = $bill->getOrderPayedSum();
        
        if ($bill->isAllowPayFromAccount()) { 
            
            $complete = $bill->buyOrder($billReserveId);

            if ($complete) {
                $_SESSION['quickbuypro_success_opcode'] = $payed_sum;

                $uri = '?quickpro_ok=1';
                if ($opcode == 164) {
                    $uri = '/profi/?quickprofi_ok=1';
                }

                $objResponse->script("window.location = '{$uri}';");
            }
        }

    }
    
    return $objResponse;
}



/**
 * Покупка ПРО через ЯД Кассу
 * 
 * @param type $opcode
 * @param type $payment
 * @param type $redirect
 * @return \xajaxResponse
 */
function quickPROGetYandexKassaLink($opcode, $payment, $redirect, $promo_code) 
{
    $objResponse = new xajaxResponse();

    $pro = 0;
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
    $proList = payed::getAvailablePayedList(is_emp());
    foreach ($proList as $proItem) {
        if($proItem['opcode']==$opcode) { $pro = $proItem; break; }
    }

    if ($pro) {
        $uid = get_uid(false);

        $bill = new billing($uid);

        //Допустимо использование промокодов
        $bill->setPromoCodes('SERVICE_PRO', $promo_code);    
        
        //Формируем заказ
        $billReserveId = $bill->addServiceAndCheckout($opcode);
        $sum = $bill->getRealPayedSum();
        $payed_sum = $bill->getOrderPayedSum();
        
        if ($sum > 0) {
            $yandex_kassa = new yandex_kassa();
            $html_form = $yandex_kassa->render($sum, $bill->account->id, $payment, $billReserveId);
            
            $html_form = preg_replace('/^[^\/]+\/\*!?/', '', $html_form);
            $html_form = preg_replace('/\*\/[^\/]+$/', '', $html_form);
            
            $_SESSION['quickbuypro_is_begin'] = 1;
            $_SESSION['quickbuypro_success_opcode'] = $payed_sum;
            $_SESSION['quickbuypro_success_opcode2'] = $opcode;
            $_SESSION['quickbuypro_redirect'] = $redirect;
            $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
            
            $objResponse->script('$("quick_pro_div_wait_txt").set("html", \''.$html_form.'\');');
            $objResponse->script("$('quick_pro_div_wait_txt').getElements('form')[0].submit();");
        }
    }
    return $objResponse;
}


/**
 * Оплата через ПлатиПотом сервис
 * 
 * @param type $opcode
 * @param type $redirect
 * @param type $promo_code
 * @return \xajaxResponse
 */
function quickPROGetPlatipotomLink($opcode, $redirect, $promo_code)
{
    $objResponse = new xajaxResponse();

    $pro = 0;
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
    $proList = payed::getAvailablePayedList(is_emp());
    foreach ($proList as $proItem) {
        if($proItem['opcode']==$opcode) { $pro = $proItem; break; }
    }

    if ($pro) {
        $uid = get_uid(false);

        $bill = new billing($uid);
        
        //Допустимо использование промокодов
        $bill->setPromoCodes('SERVICE_PRO', $promo_code); 
        
        //Формируем заказ
        $billReserveId = $bill->addServiceAndCheckout($opcode);
        $sum = $bill->getRealPayedSum();
        $payed_sum = $bill->getOrderPayedSum();

        $platipotom = new platipotom(true);
        if ($sum > 0 && $sum <= $platipotom->getMaxPrice($bill->account->id)) {
            
            $html_form = $platipotom->render($sum, $bill->account->id, $billReserveId);
            
            if($html_form) {
                $html_form = preg_replace('/^[^\/]+\/\*!?/', '', $html_form);
                $html_form = preg_replace('/\*\/[^\/]+$/', '', $html_form);

                $_SESSION['quickbuypro_is_begin'] = 1;
                $_SESSION['quickbuypro_success_opcode'] = $payed_sum;
                $_SESSION['quickbuypro_success_opcode2'] = $opcode;
                $_SESSION['quickbuypro_redirect'] = $redirect;
                $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];

                $objResponse->script('$("quick_pro_div_wait_txt").set("html", \''.$html_form.'\');');
                $objResponse->script("$('quick_pro_div_wait_txt').getElements('form')[0].submit();");
            }            
        }
    }
    return $objResponse;
}

$xajax->processRequest();