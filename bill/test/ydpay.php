<?php
$paypost = $_POST;
$payget = $_GET;
// Тестовое оплата услуг через Яндекс.Деньги
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if(is_release()) exit;

require_once($_SERVER['DOCUMENT_ROOT']."/classes/pmpay.php");
if(isset($paypost['cancel'])) {
    $back_url = $_SESSION['referer'];
    unset($_SESSION['referer'], $_SESSION['post_payment']);
    header("Location: {$back_url}");
    exit;
} elseif( isset($paypost['success']) ) {
    $host    = $GLOBALS['host'];
    $ydpay = new ydpay();
    $payment = $_SESSION['post_payment'];
    
    $_SERVER['HTTP_X_REAL_IP'] = '77.75.157.168';
    
    $shopid = $request['shopId'];
        $ammount = $request['orderSumAmount'];
        $orderIsPaid = $request['orderIsPaid'];
        $orderNumber = $request['invoiceId'];
        $billing_no = $request['customerNumber'];
        $hash = $request['md5'];
        $fromcode = $request['paymentPayerCode'];
        $paymentDateTime = $request['paymentDateTime'];
        $orderCreatedDatetime = $request['orderCreatedDatetime'];
        $operation_type = $request['OPERATION_TYPE'];
        $operation_id = $request['OPERATION_ID'];
    
    $orderIsPaid = rand(1,500); 
    $invoceId    = rand(1, 50000);
    
    $hash_str = $orderIsPaid . ';' . floatval($payment['Sum']) . ';' . $ydpay->curid . ';'
            . $ydpay->bank . ';' . ydpay::SHOP_DEPOSIT . ';' . $invoceId . ';' . intval($payment['CustomerNumber']) . ';' . $ydpay->key;    
        
    $post = array(
        'shopSumBankPaycash'   => $ydpay->bank,
        'orderCreatedDatetime' => date('c'),
        'action'               => 'Check',
        'shopId'               => ydpay::SHOP_DEPOSIT,
        'orderSumAmount'       => floatval($payment['Sum']),
        'orderIsPaid'          => $orderIsPaid,
        'invoiceId'            => $invoceId,
        'customerNumber'       => intval($payment['CustomerNumber']),
        'md5'                  => strtoupper(md5($hash_str)),
        'paymentPayerCode'     => '10002003040',
        'paymentDateTime'      => date('c'),
        'orderCreatedDatetime' => date('c'),
        'OPERATION_TYPE'       => 0,
        'OPERATION_ID'         => 0
    );
    
    var_dump($post);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/ydpay116.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();
    
    $post['action'] = 'PaymentSuccess';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/ydpay116.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();
    
    header("Location: /bill/success/");
    exit;
}
$_SESSION['post_payment'] = $payget;

?>

<h2>Тестовая оплата Яндекс.Деньги</h2>
<p>
Оплата услуг аккаунт #<?= intval($payget['CustomerNumber'])?>, сумма оплаты <?= to_money($payget['Sum'], 2)?> рублей
</p>

<form method="POST" />
    <input type="submit" name="success" value="Оплатить" />
    <input type="submit" name="cancel" value="Отмена" />
    <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
</form>