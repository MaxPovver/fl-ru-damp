<?php
/**
 * Шаблон письма уведомление исполнителю об изменении заказа (П-3)
 * все переводы каретки (новая строка) будут заменены <br/>
 */

/**
 * Тема письма
 */
$smail->subject = "Заказ на услугу «{$order['title']}» изменен";

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$accept_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'accept', $order['frl_id']);
$decline_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'decline', $order['frl_id']);

?>
Заказчик <?=$emp_fullname?> изменил параметры заказа &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;:

<?php include 'change2_order.tpl.php'; ?>

<a href="<?=$order_url?>">Перейти к заказу</a> / <a href="<?=$accept_url?>">Подтвердить его</a> / <a href="<?=$decline_url?>">Отказаться от выполнения</a><?=PHP_EOL?>