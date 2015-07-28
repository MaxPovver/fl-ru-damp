<?php
$paypost = $_POST;
// Тестовое оплата услуг через WM
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if(is_release()) exit;

require_once($_SERVER['DOCUMENT_ROOT']."/classes/pmpay.php");
if(isset($paypost['cancel'])) {
    $back_url = $_SESSION['referer'];
    unset($_SESSION['referer']);
    header("Location: {$back_url}");
    exit;
} elseif(isset($paypost['success']) ) {
    $host    = $GLOBALS['host'];
    $payment = $_SESSION['post_payment'];
    $pmpay   = new pmpay();
    
    $post = array(
        'LMI_PREREQUEST'  => 1,
        'LMI_MERCHANT_ID' => $pmpay->merchants[pmpay::MERCHANT_BILL],
        'PAYMENT_BILL_NO' => $payment['PAYMENT_BILL_NO'],
        'LMI_PAYMENT_NO'  => $payment['LMI_PAYMENT_NO'],
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/pm.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();
    
    $req = array(
        'LMI_PREREQUEST'  => 2,
        'LMI_MERCHANT_ID' => $pmpay->merchants[pmpay::MERCHANT_BILL],
        'PAYMENT_BILL_NO' => $payment['PAYMENT_BILL_NO'],
        'LMI_SYS_PAYMENT_ID' =>  rand(1, 500000),
        'LMI_SYS_PAYMENT_DATE' => date('d.m.Y H:i'),
        'LMI_PAYMENT_AMOUNT' => $payment['LMI_PAYMENT_AMOUNT'],
        'LMI_CURRENCY' => 'RUB',
        'LMI_PAID_AMOUNT' => $payment['LMI_PAYMENT_AMOUNT'],
        'LMI_PAID_CURRENCY' => 'RUB',
        'LMI_PAYMENT_SYSTEM' => 'R209922555324',
        'LMI_SIM_MODE' => $payment['LMI_SIM_MODE'],
        'LMI_PAYMENT_NO'  => $payment['LMI_PAYMENT_NO'],
    );
    
    
    $req['LMI_HASH'] = base64_encode(md5($req['LMI_MERCHANT_ID'] .';'. $req['LMI_PAYMENT_NO'] .';'. $req['LMI_SYS_PAYMENT_ID']
                  .';'. $req['LMI_SYS_PAYMENT_DATE'] .';'. $req['LMI_PAYMENT_AMOUNT'] .';'. $req['LMI_CURRENCY']
                  .';'. $req['LMI_PAID_AMOUNT'] .';'. $req['LMI_PAID_CURRENCY'] .';'. $req['LMI_PAYMENT_SYSTEM']
                  .';'. $req['LMI_SIM_MODE']
                  .';'. $pmpay->key, true));
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/pm.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();
    
    if($complete == 'YES') {
        header("Location: /bill/success/");
        exit;
    }
    
    echo $complete;
    echo "ERROR";
    exit;
}
$_SESSION['post_payment'] = $paypost;
?>

<h2>Тестовая оплата Webmoney</h2>
<p>
<?= iconv('UTF-8', 'CP1251', base64_decode($paypost['LMI_PAYMENT_DESC_BASE64']))?>, сумма оплаты <?= $paypost['LMI_PAYMENT_AMOUNT'] ?> рублей
</p>

<form method="POST" />
    <input type="hidden" name="LMI_PREREQUEST" value="1" />
    <input type="submit" name="success" value="Оплатить" />
    <input type="submit" name="cancel" value="Отмена" />
    <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
</form>