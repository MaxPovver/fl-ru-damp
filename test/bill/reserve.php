<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/account.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DocGen/DocGenReserves.php');


//------------------------------------------------------------------------------


//$results = array();
//if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//------------------------------------------------------------------------------

//$order_id = 0;//intval($_GET['order_id']);
$customerNumber = 120824;//$_GET['cn'];
$ammount = 569;//$_GET['ammount'];
$invoiceId = 2000246796791;//$_GET['invoiceId'];
$payments = 3;
$orderId = 367369;

$params['invoiceId'] = $invoiceId;

/*
3 => self::PAYMENT_YD,
6 => self::PAYMENT_AC,
10 => self::PAYMENT_WM,
16 => self::PAYMENT_AB,
17 => self::PAYMENT_SB
*/

$descr = "Платеж через Яндекс.Кассу. Сумма - {$ammount}, номер покупки - {$invoiceId}";

//------------------------------------------------------------------------------

//Заносим деньги на ЛС
$account = new account();
$error = $account->deposit(
        $op_id,//@todo: никому ненужен?
        $customerNumber, 
        $ammount, 
        $descr, 
        $payments, 
        $ammount, 
        12
);

if (!$error) {
    
    //Пробуем купить заказ за который занесли деньги выше
    if ($orderId > 0) {
        $billing = new billing($account->uid);
        $billing->buyOrder(
                $orderId, 
                12,//@todo: подсомнением необходимость параметра
                $params);
    }

    exit;
}

echo $error;
exit;