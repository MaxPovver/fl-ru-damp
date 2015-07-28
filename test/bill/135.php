<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");


//------------------------------------------------------------------------------


$results = array();
//$profiler = new profiler();


//------------------------------------------------------------------------------


/*
$bill = new billing(6);
$bill->clearOrders();
$option = array('acc_sum' => 1000);   
$bill->setOptions($option);     
$ok = $bill->create(135);
$results['create(135)'] = $ok;
*/  
        

$uid = 6;

$account = new account();
$ok = $account->GetInfo($uid, true);
$results['GetInfo'] = (int)$ok;
if($ok)
{
    $sum = 14320;
    $scomment = 'Пополнение счета';
    $ucomment = 'Пополнение счета';
    $trs_sum = $sum;
    $op_date = date('c');//, strtotime($_POST['date']));
            
    //$results['depositEx'] = $account->depositEx($account->id, $sum, $scomment, $ucomment, 135, $trs_sum, NULL, $op_date);
    
    $op_id = 0;
    
    //$account->Buy(&$id, $transaction_id, $op_code, $uid, $ucomment, $scomment, $ammount, $commit, $payment_sys);
    
    
    $fromcode = '"TEST"';
    $ammount = $sum;
    $paymentDateTime = $op_date;
    $orderNumber = 134;
    
    $descr = "ЯД с кошелька $fromcode сумма - $ammount, обработан $paymentDateTime, номер покупки - $orderNumber";
    
    
    $results['deposit'] = $account->deposit($op_id, $account->id, $sum, $descr, 3, $sum, 12);
    
}



//------------------------------------------------------------------------------

//$profiler->start('fill_frl_mem');

//------------------------------------------------------------------------------




//------------------------------------------------------------------------------

//$profiler->stop('fill_frl_mem');

//------------------------------------------------------------------------------


//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;