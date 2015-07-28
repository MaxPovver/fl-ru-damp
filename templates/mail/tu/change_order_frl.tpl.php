<?php
/**
 * Шаблон письма уведомление исполнителю об изменении цены и срока заказа
 */

/**
 * Тема письма
 */
$smail->subject = "Бюджет и сроки заказа «{$order['title']}» изменены";

$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$order_days = tservices_helper::days_format($order['order_days']);

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);

?>
Здравствуйте.<br /><br />
Заказчик <?=$emp_fullname?> отредактировал бюджет и сроки по заказу &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;<br /><br />
Бюджет – <?=$order_price?><br />
Срок – <?=$order_days?>.<br /><br />
<a href="<?=$order_url?>">Перейти к заказу</a><br /><br />

С уважением, 
команда <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>