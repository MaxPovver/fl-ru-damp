<?php
// Тестовая оплата услуг через Плати потом

define('NO_CSRF', 1);

//Данные платежной формы
$paypost = $_POST;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/platipotom.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");


if(is_release()) exit;


if(isset($_GET['cancel'])) { //Отказ от платежа
    header("Location: /bill/fail");
    exit;
} elseif($_GET['success']) {
    $host    = $GLOBALS['host'];
    $platipotom = new platipotom();
    $payment = $_SESSION['post_payment'];
    
    $request = array(
        'orderid' => $payment['orderid'], //Уникальный идентификатор заказа в базе магазина.
        'subid' => $payment['subid'], //Уникальный идентификатор пользователя
        'sig' => $platipotom->getSig($payment['price'], $payment['orderid'], $payment['subid']) //Подпись платежа.
    );
    $get = '?';
    foreach ($request as $param => $value) {
        if ($get !== '?') $get .= '&';
        $get .= $param .'='. $value;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/platipotom.php".$get);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();
    
    echo "<p>Результат <strong>нотификации</strong>:</p>";
    echo '<pre>';
    print_r(htmlspecialchars($complete));
    echo '</pre>';
    echo '<p><a href="/bill/success/">Вернуться в магазин</a></p>';
    exit;
} else {
    //Сохраняем в сессию, т.к. яндекс это помнит при двух последующих запросах
    $_SESSION['post_payment'] = $paypost;
}
?>

<h2>Тестовая оплата Плати Потом</h2>
<p>
    Оплата услуг аккаунт <strong>#<?= intval($paypost['subid'])?></strong><br />
    Cумма оплаты <strong><?= to_money($paypost['price'], 2)?> рублей</strong><br />
</p>

<form method="GET" >
    <input type="submit" name="success" value="Успешно оплатить" />
    <input type="submit" name="cancel" value="Вернуться в магазин" />
    <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
</form>