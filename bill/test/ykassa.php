<?php
// Тестовая оплата услуг через Яндекс.Кассу

define('NO_CSRF', 1);


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/yandex_kassa.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");


if(is_release()) exit;


$uid = get_uid(false);
$key = 'post_payment_' . $uid;
$memBuff = new memBuff();



if (isset($_GET['paymentAviso'])) {

    $host    = $GLOBALS['host'];
    $payment = $memBuff->get($key);
    $paypost = $payment;
    $_SERVER['HTTP_X_REAL_IP'] = '77.75.157.166';
    
    //Что нам говорит яндекс
    $post = array(
        'requestDatetime' => date('c'),
        'action' =>	'paymentAviso',
        'shopId' => $payment['ShopID'],//yandex_kassa::SHOPID_DEPOSIT, //Нужно выяснить, что платеж через БС - и тогда ставить SHOPID_DEPOSIT
        'invoiceId' => $payment['invoceId'],
        'customerNumber' => $payment['customerNumber'],
        'orderCreatedDatetime' => date('c'),
        'orderSumAmount' => floatval($payment['Sum']),
        'orderSumCurrencyPaycash' => 643,
        'orderSumBankPaycash' => 1001,
        'shopSumAmount' => $payment['Sum'],
        'shopSumCurrencyPaycash' => 643,
        'shopSumBankPaycash' => 1001,
        'paymentPayerCode' => 42007148320,
        'paymentType' => $payment['paymentType']
    );
    
    if(isset($payment['orderId'])) {
        $post['orderId'] = $payment['orderId'];
    }      
    
    $post['md5'] = strtoupper(md5(implode(';', array(
        $post['action'],
        $post['orderSumAmount'],
        $post['orderSumCurrencyPaycash'],
        $post['orderSumBankPaycash'],
        $post['shopId'],
        $post['invoiceId'],
        $post['customerNumber'],
        YK_KEY
    ))));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/ykassa.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();

    echo "<p>Результат <strong>paymentAviso</strong>:</p>";
    echo '<pre>';
    print_r(htmlspecialchars($complete));
    echo '</pre>';  

} elseif (isset($_GET['checkOrder'])) {
    
    $host    = $GLOBALS['host'];
    $payment = $memBuff->get($key);
    $paypost = $payment;
    $_SERVER['HTTP_X_REAL_IP'] = '77.75.157.166';
    
    //Что нам говорит яндекс
    $post = array(
        'requestDatetime' => date('c'),
        'action' =>	'checkOrder',
        'shopId' => $payment['ShopID'],//yandex_kassa::SHOPID_DEPOSIT, //Нужно выяснить, что платеж через БС - и тогда ставить SHOPID_DEPOSIT
        'invoiceId' => $payment['invoceId'],
        'customerNumber' => $payment['customerNumber'],
        'orderCreatedDatetime' => date('c'),
        'orderSumAmount' => floatval($payment['Sum']),
        'orderSumCurrencyPaycash' => 643,
        'orderSumBankPaycash' => 1001,
        'shopSumAmount' => $payment['Sum'],
        'shopSumCurrencyPaycash' => 643,
        'shopSumBankPaycash' => 1001,
        'paymentPayerCode' => 42007148320,
        'paymentType' => $payment['paymentType']
    );
    
    if(isset($payment['orderId'])) {
        $post['orderId'] = $payment['orderId'];
    }    
    
    $post['md5'] = strtoupper(md5(implode(';', array(
        $post['action'],
        $post['orderSumAmount'],
        $post['orderSumCurrencyPaycash'],
        $post['orderSumBankPaycash'],
        $post['shopId'],
        $post['invoiceId'],
        $post['customerNumber'],
        YK_KEY
    ))));    
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/ykassa.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();
    
    echo "<p>Результат <strong>checkOrder</strong>:</p>";
    echo '<pre>';
    print_r(htmlspecialchars($complete));
    echo '</pre>';
    
} elseif(isset($_GET['cancel'])) { //Отказ от платежа
    header("Location: /bill/fail");
    exit;
} elseif(isset($_GET['success'])) {
    $host    = $GLOBALS['host'];
    $payment = $memBuff->get($key);
    
    $_SERVER['HTTP_X_REAL_IP'] = '77.75.157.166';
    
    
    $invoceId    = rand(1, 50000);
    
    //Что нам говорит яндекс
    $post = array(
        'requestDatetime' => date('c'),
        'action' =>	'checkOrder',
        'shopId' => $payment['ShopID'],//yandex_kassa::SHOPID_DEPOSIT, //Нужно выяснить, что платеж через БС - и тогда ставить SHOPID_DEPOSIT
        'invoiceId' => $invoceId,
        'customerNumber' => $payment['customerNumber'],
        'orderCreatedDatetime' => date('c'),
        'orderSumAmount' => floatval($payment['Sum']),
        'orderSumCurrencyPaycash' => 643,
        'orderSumBankPaycash' => 1001,
        'shopSumAmount' => $payment['Sum'],
        'shopSumCurrencyPaycash' => 643,
        'shopSumBankPaycash' => 1001,
        'paymentPayerCode' => 42007148320,
        'paymentType' => $payment['paymentType']
    );
    
    if(isset($payment['orderId'])) {
        $post['orderId'] = $payment['orderId'];
    }
    
    $post['md5'] = strtoupper(md5(implode(';', array(
        $post['action'],
        $post['orderSumAmount'],
        $post['orderSumCurrencyPaycash'],
        $post['orderSumBankPaycash'],
        $post['shopId'],
        $post['invoiceId'],
        $post['customerNumber'],
        YK_KEY
    ))));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/ykassa.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();
    
    echo "<p>Результат <strong>checkOrder</strong>:</p>";
    echo '<pre>';
    print_r(htmlspecialchars($complete));
    echo '</pre>';
    
    
    $xmlObj = @simplexml_load_string($complete);
    
    //делаем уведомление подтверждения 
    //если запрос небыл отвергнут сайтом
    if($xmlObj && (int)$xmlObj->attributes()->code === 0)
    {
        sleep(2);

        $post['action'] = 'paymentAviso';
        $post['md5'] = strtoupper(md5(implode(';', array(
            $post['action'],
            $post['orderSumAmount'],
            $post['orderSumCurrencyPaycash'],
            $post['orderSumBankPaycash'],
            $post['shopId'],
            $post['invoiceId'],
            $post['customerNumber'],
            YK_KEY
        ))));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host . "/income/ykassa.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        ob_start();
        $res = curl_exec($ch);
        $complete = ob_get_clean();
        
        echo "<p>Результат <strong>paymentAviso</strong>:</p>";
        echo '<pre>';
        print_r(htmlspecialchars($complete));
        echo '</pre>';
    }
    
    echo '<p><a href="/bill/success/">Вернуться в магазин</a></p>';
    exit;
} else {
    //Данные платежной формы
    $paypost = $_POST;
    
    //Генерируем invoceId один для всей сесии
    $paypost['invoceId'] = rand(1, 50000);
    
    //Сохраняем в сессию, т.к. яндекс 
    //это помнит при двух последующих запросах
    $memBuff->set($key, $paypost);
}

$payway = array(
    yandex_kassa::PAYMENT_YD => "Яндекс.Деньги",
    yandex_kassa::PAYMENT_AC => "Карта",
    yandex_kassa::PAYMENT_WM => "Webmoney",
    yandex_kassa::PAYMENT_AB => "Альфа-Клик",
    yandex_kassa::PAYMENT_SB => "Сбербанк Онлайн"
);
?>

<h2>Тестовая оплата Яндекс.Касса</h2>
<p>
    Оплата услуг аккаунт <strong>#<?= intval($paypost['customerNumber'])?></strong><br />
    Cумма оплаты <strong><?= to_money($paypost['Sum'], 2)?> рублей</strong><br />
    Cпособ оплаты <strong><?= $payway[$paypost['paymentType']]?></strong><br />
    IP cервера Яндекса <strong><?=getRemoteIp()?></strong><br />
    ShopID <strong><?=$paypost['ShopID']?></strong><br />
    Bill reserve ID: <strong><?=@$paypost['orderId']?></strong>
</p>

<form method="GET" action="ykassa.php" >
    <input type="submit" name="success" value="Успешно оплатить" />
    <input type="submit" name="cancel" value="Вернуться в магазин" />
    
    <br/>
    <br/>
    
    <b>Отдельно запросы:</b>
    <br/>
    <input type="submit" name="checkOrder" value="checkOrder" />
    <input type="submit" name="paymentAviso" value="paymentAviso" />
    

    <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
</form>