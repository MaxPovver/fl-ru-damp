<?php

/**
 * ≈сли создан заказ, предложен исполнителю, и в течение 3х суток от него не было никаких действий 
 * (отказа или подтверждени€), то отправл€ем исполнителю почтовое уведомление:
 */


$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);

$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$order_days = tservices_helper::days_format($order['order_days']);

$accept_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'accept', $order['frl_id']);
$decline_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'decline', $order['frl_id']);

?>
Ќапоминаем, что 3 дн€ назад «аказчик <?=$emp_fullname?> предложил вам заказ на услугу 
&laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; на сумму <?php echo $order_price ?> со сроком выполнени€ <?=$order_days?>.<br/>
¬ы можете перейти к заказу и обговорить услови€ сотрудничества, начать выполнение заказа или отказатьс€ от него.
<br/>
<br/> 
<a href="<?=$order_url?>">ѕерейти к заказу</a> / 
<a href="<?=$accept_url?>">Ќачать его выполнение</a> / 
<a href="<?=$decline_url?>">ќтказатьс€ от выполнени€</a>
<br/>
<br/>