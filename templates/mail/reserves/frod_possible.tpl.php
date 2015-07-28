<?php

$smail->subject = "Подозрительная сделка на FL.ru";
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order_id);

if ($date_reserve) {
    $date_reserve = date('d.m.Y H:i:s',  strtotime($date_reserve));
}

$date_payout = date('d.m.Y H:i:s');

$price = tservices_helper::cost_format($price);

?>
Номер сделки: <a href="<?=$order_url?>"><?=$num?></a><br/>
Логин и ФИО Заказчика: <?=$emp?><br/>
Логин и ФИО Исполнителя: <?=$frl?><br/>
Invoice ID: <?=$invoiceId?><br/>
Дата и время резервирования: <?=$date_reserve?><br/>
Дата и время запроса на выплату: <?=$date_payout?><br/>
Сумма платежа: <?=$price?>