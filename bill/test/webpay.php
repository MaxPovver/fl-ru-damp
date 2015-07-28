<?php
// Тестовое оплата услуг через Вебкошелек
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if(is_release()) exit;

//empty($data['amount']) || empty($data['userid']) || empty($data['userid_extra'])
//            || empty($data['paymentid']) || empty($data['key']) || empty($data['paymode'])
if(isset($_POST['cancel'])) {
    $back_url = $_SESSION['referer'];
    unset($_SESSION['referer'], $_SESSION['post_payment']);
    header("Location: {$back_url}");
    exit;
} elseif(isset($_POST['success']) ) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/onlinedengi_cards.php");
    $host    = $GLOBALS['host'];
    $payment = $_SESSION['post_payment'];
    $post = array(
        'amount'        => $payment['amount'],
        'userid'        => $payment['nickname'],
        'userid_extra'  => $payment['nickname'],
        'paymentid'     => rand(1, 500000),
        'paymode'       => 204
    );
    
    $post['key'] = md5( $post['amount'] . $post['userid'] . $post['paymentid'] . onlinedengi_cards::SECRET);
    var_dump($post);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/do-card.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();
    
    header("Location: /bill/");
    exit;
}
$_SESSION['post_payment'] = $_POST;
$_SESSION['referer']      = $_SERVER['HTTP_REFERER'];
?>

<h2>Тестовая оплата Веб.Кошелек</h2>
<p>
Оплата услуг аккаунт <?= __paramValue('string', $_POST['nickname'])?>, сумма оплаты <?= to_money($_POST['amount'],2)?> рублей
</p>

<form method="POST" />
    <input type="submit" name="success" value="Оплатить" />
    <input type="submit" name="cancel" value="Отмена" />
    <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
</form>