<?php

//$rpath = "../";

require_once($_SERVER['DOCUMENT_ROOT'] . '/xajax/quick_payment.common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupFactory.php');


session_start();


//------------------------------------------------------------------------------


/**
 * Функции оплаты
 * 
 * @param type $process
 * @return type
 */
function quickPayments($process)
{
    $list = array(
        'reserve'       => '/xajax/quick_payment_reserve.server.php',
        'autoresponse'  => '/xajax/quick_payment_autoresponse.server.php',
        'frlbind'       => '/xajax/quick_payment_frlbind.server.php',
        'frlbindup'     => '/xajax/quick_payment_frlbindup.server.php',
        'carusel'       => '/xajax/quick_payment_carusel.server.php',
        'tservicebind'  => '/xajax/quick_payment_tservicebind.server.php',
        'tservicebindup'=> '/xajax/quick_payment_tservicebindup.server.php',
        'billinvoice'   => '/xajax/quick_payment_billinvoice.server.php',
        'account'       => '/xajax/quick_payment_account.server.php',
        'masssending'   => '/xajax/quick_payment_masssending.server.php',
        'pro'           => '/xajax/quick_payment_pro.server.php'
    );
    
    return (isset($list[$process]))?$list[$process]:false;
}


//------------------------------------------------------------------------------


function quickPaymentProcess($process, $type, $data)
{
    $objResponse = &new xajaxResponse();
    
    //Проверка на существование передаваемого способа оплаты
    if (!quickPaymentPopupFactory::getInstance($process)->isExistPaymentType($type)) {
        return $objResponse;
    }
    
    $source_file = quickPayments($process);
    $source_file = $_SERVER['DOCUMENT_ROOT'] . $source_file;
    if(!file_exists($source_file)) return $objResponse;
    require_once $source_file;

    $func = sprintf('quickPayment%s%s', ucfirst($process), ucfirst($type));
    if(!function_exists($func)) return $objResponse;
    
    $objResponse = $func($type,$data);
    
    $_SESSION[quickPaymentPopupFactory::QPP_PROCESS_SESSION] = $process;
    
    return $objResponse;
}


//------------------------------------------------------------------------------


$xajax->processRequest();