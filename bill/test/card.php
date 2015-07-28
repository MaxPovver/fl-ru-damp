<?php
$paypost = $_POST;
// Тестовое оплата услуг через Qiwi
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if(is_release()) exit;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/cardpay.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/onlinedengi_cards.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");

if(isset($paypost['cancel'])) {
    $back_url = $_SESSION['referer'];
    unset($_SESSION['referer']);
    header("Location: {$back_url}");
    exit;
} elseif(isset($paypost['success']) ) {
    $post_payment = $_SESSION['post_payment'];
    
    // Оплата через асист
    if($post_payment['Merchant_ID'] > 0) {
        $cardpay = new cardpay();
        $post = array(
            'merchant_id'  => $post_payment['Merchant_ID'],
            'ordernumber'  => $post_payment['OrderNumber'],
            'orderamount'  => $post_payment['OrderAmount'],
            'ordercurrency'=> "RUR",
            'amount'       => $post_payment['OrderAmount'],
            'currency'     => "RUR",
            'orderstate'   => "Approved",
            'responsecode' => "AS000",
            'billnumber'   => rand(1, 909999999),
            'meantypename' => "VISA",
            'meannumber'   => '76XXXXXXX12',
            'packetdate'   => date('d.m.Y H:i')
        );  
        $post['checkvalue'] = $post['merchant_id'].$post['ordernumber'].$post['amount'].$post['currency'].$post['orderstate'];
        $post['checkvalue'] = strtoupper(md5(strtoupper(md5($cardpay->getSecret()).md5($post['checkvalue']))));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host . "/income/card.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        ob_start();
        $res = curl_exec($ch);
        $complete = ob_get_clean();
        
        header("Location: /bill/success/");
        exit;
    } else {
        $post = array(
            'amount'    => $post_payment['OrderAmount'],
            'userid'    => get_uid(false),
            'paymentid' => rand(1, 9999999999),
            'orderid'   => $post_payment['OrderNumber'],
            'date'      => date('d.m.Y H:i')
        );
        $post['key'] = md5( $post['amount'] . $post['userid'] . $post['paymentid'] . onlinedengi_cards::SECRET);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host . "/income/do-card.php?src=1");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        ob_start();
        $res = curl_exec($ch);
        $complete = ob_get_clean();
        
        header("Location: /bill/success/");
        exit;
    }
}
$bill = new billing(get_uid(false));

$_SESSION['post_payment'] = $paypost;

?>

<h2>Тестовая оплата Пластиковая карта</h2>
<p>
Оплата услуг аккаунт #<?= get_uid(false);?>, сумма оплаты <?= to_money($paypost['OrderAmount'],2)?> рублей
</p>

<form method="POST" />
    <input type="submit" name="success" value="Оплатить" />
    <input type="submit" name="cancel" value="Отмена" />
    <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
</form>