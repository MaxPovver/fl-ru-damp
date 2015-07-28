<?php

/**
 * Ряд услуг при публикации проекта.
 * Оплата услуг.
 */


$rpath = "../";
require_once ($_SERVER['DOCUMENT_ROOT'] . "/xajax/quickprj.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/yandex_kassa.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");

session_start();


//------------------------------------------------------------------------------


function quickPRJPayAccount() {
    $objResponse = new xajaxResponse();

    $uid = get_uid(false);
    
    ob_start();
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
    $bill = new billing($uid);
    
    $billReserveId = $bill->checkoutOrder();
    
    $payed_sum = $bill->getOrderPayedSum();
        
    if ($bill->isAllowPayFromAccount()) { 

        $complete = $bill->buyOrder($billReserveId);

        if($complete) {
            $_SESSION['quickprj_ok'] = 1;
                
            $memBuff = new memBuff();
            $project_id = $memBuff->get('bill_ok_project_'.$uid);
    
            if($project_id) {
                
                $is_payed = $memBuff->get('bill_ok_project_payed_'.$uid);

                if ($is_payed) {
                    $memBuff->delete('bill_ok_project_payed_'.$uid);
                    $friendly_url = "/public/?step=2&public={$project_id}";
                } else {
                    $friendly_url = getFriendlyURL('project', $project_id);
                    $_SESSION['quickprj_ok'] = 1;
                    $friendly_url.='?quickprj_ok=1';
                }                

                $objResponse->script("window.location = '{$friendly_url}';");
                $memBuff->delete('bill_ok_project_'.$uid);
                
            } else {
                $objResponse->script("window.location = '/?quickprj_ok=1';");
            }
        }
    }

    ob_end_clean();
    return $objResponse;

}


//------------------------------------------------------------------------------


function quickPRJGetYandexKassaLink($payment) {
    $objResponse = new xajaxResponse();

    $bill = new billing(get_uid(false));
    
    $billReserveId = $bill->checkoutOrder();
    $sum = $bill->getRealPayedSum();
    $payed_sum = $bill->getOrderPayedSum();
        
    if($sum > 0) {
        $_SESSION['quickprj_is_begin'] = 1;
            
        $yandex_kassa = new yandex_kassa();
        $html_form = $yandex_kassa->render($sum, $bill->account->id, $payment, $billReserveId);
            
        $objResponse->script('$("quick_pro_div_wait_txt").set("html", \''.$html_form.'\');');
        $objResponse->script("$('quick_pro_div_wait_txt').getElements('form')[0].submit();");
    }
    return $objResponse;
}

$xajax->processRequest();