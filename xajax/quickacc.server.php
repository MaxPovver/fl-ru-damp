<?php

/**
 * @todo: Более не используется погашение задолженности
 *        оставил если вдруг внезапно вернемся
 */

$rpath = "../";
require_once ($_SERVER['DOCUMENT_ROOT'] . "/xajax/quickacc.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/yandex_kassa.php");

session_start();

function quickACCGetYandexKassaLink($sum, $payment) {
    $objResponse = new xajaxResponse();

    /*
     * Код биллинга устарел
     * 
    $bill = new billing(get_uid(false));
    $bill->cancelAllNewAndReserved();

    $account = new account();

    $option = array('acc_sum' => $sum);   
    $bill->setOptions($option);     
    $ok = $bill->create(135);

    $bill->setPage('orders');
    
    $payed_sum = 0; //реальная сумма
    foreach($bill->list_service as $service) {
        $payed_sum += ($bill->pro_exists_in_list_service && ($service['pro_ammount'] > 0 || $service['op_code'] == 53) ? $service['pro_ammount'] : $service['ammount']);
    }//foreach //подсчитали реальную сумму к оплате
    $bill->calcPayedSum($payed_sum);
    $bill->preparePayments($payed_sum);
    $action = is_release() ? "https://money.yandex.ru/eshop.xml" : "/bill/test/ydpay.php";
    
    $_SESSION['quickacc_is_begin'] = 1;
    $_SESSION['quickacc_sum'] = $sum;
    $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
    
    $yandex_kassa = new yandex_kassa();
    $html_form = $yandex_kassa->render($sum, $bill->account->id, $payment);
            
    $html_form = preg_replace('/^[^\/]+\/\*!?/', '', $html_form);
    $html_form = preg_replace('/\*\/[^\/]+$/', '', $html_form);
            
    $objResponse->script('$("quick_acc_div_wait").set("html", \''.$html_form.'\');');
    $objResponse->script("$('quick_acc_div_wait').getElements('form')[0].submit();");
    */
    
    return $objResponse;
}

$xajax->processRequest();