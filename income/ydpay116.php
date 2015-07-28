<?php

define('NO_CSRF', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/ydpay.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
if( !is_release() ) { // ƒл€ прохода тестовых платежей через яƒ на бете
    $_SERVER['HTTP_X_REAL_IP'] = '77.75.157.168';
    $_SERVER['REMOTE_ADDR'] = '77.75.157.168';
}
$account = new ydpay();
$log = new log('ydpay/%d%m%Y.log');
$log->writeln('----- ' . date('d.m.Y H:i:s'));
$log->writevar($_POST);

$account->bank = $_POST['shopSumBankPaycash'];
$action = __paramInit('string', null, 'action');
$orderCreatedDatetime = __paramInit('string', null, 'orderCreatedDatetime');
if (in_array($_POST['action'], array('Check', 'PaymentSuccess'))) {
    $error = $account->process_payment($_POST);
}

$log->writeln('----- ' . ($error ? $error : 'YES'));
$log->writeln();

echo "<?xml version=\"1.0\" encoding=\"windows-1251\"?>"
?>
<response performedDatetime="<?=$orderCreatedDatetime?>">
	<result code="<?=(($error)?100:0)?>" action="<?=$action?>" shopId="<?=htmlspecialchars($_POST['shopId'])?>" invoiceId="<?=htmlspecialchars($_POST['invoiceId'])?>" techMessage="<?=$error?>"/>
</response>