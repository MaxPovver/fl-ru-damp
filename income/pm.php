<?php

define('NO_CSRF', 1);
$reqv = $_POST; // Для автоплатежей, тк мы не знаем u_token_key
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/pmpay.php');
$account = new pmpay();
if(pmpay::getPaymentBillNO($reqv['LMI_PAYMENT_DESC']) > 0 && $reqv['PAYMENT_BILL_NO'] == '') {
    $reqv['PAYMENT_BILL_NO'] = pmpay::getPaymentBillNO($reqv['LMI_PAYMENT_DESC']);
}
$error = $reqv['LMI_PREREQUEST'] == 1 ? $account->prepare($reqv) : $account->checkdeposit($reqv);

//////////////////////////////

echo ($error ? $error : 'YES');

