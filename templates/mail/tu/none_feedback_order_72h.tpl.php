<?php

/*
 * Если заказ закрыт, и в течение 3х суток после закрытия сторона 
 * не оставила отзыв, то отправляем стороне почтовое уведомление.
 */

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);

?>
Напоминаем вам, что 3 дня назад было завершено сотрудничество по заказу &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;. 
В течение 4 дней вы можете оставить отзыв о сотрудничестве.
<br/>
<br/>
<a href="<?=$order_url?>">Перейти к заказу</a> / 
<a href="<?=$order_url?>">Оставить отзыв</a>
<br/>
<br/>