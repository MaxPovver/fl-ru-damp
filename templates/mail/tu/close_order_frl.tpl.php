<?php

/*
 * Шаблон уведомления исполнителю о завершении заказа заказчиком и получении отзыва (УВ-10)
 * Так же используется при отправле ЛС поэтому все переводы каретки (новая строка) будут заменены <br/> при выводе сообщения и при отправке письма
 */


$smail->subject = "Завершение заказа «{$order['title']}»";


$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
//$tu_url = $GLOBALS['host'] . tservices_helper::card_link($order['tu_id'], $order['title']);
$emp_feedback = reformat(htmlspecialchars($order['emp_feedback']), 30);
$emp_is_good = ($order['emp_rating'] > 0);
//$feedback_url = $GLOBALS['host'] . "/users/{$order['employer']['login']}/opinions/";

if(empty($emp_feedback))
{
    
?>
Заказчик <?=$emp_fullname?> завершил сотрудничество и закрыл заказ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;.
<a href="<?=$order_url?>">Вы можете оставить отзыв.</a>
<?php

}
else
{
    
?>
Заказчик <?=$emp_fullname?> завершил сотрудничество с вами по заказу &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; и оставил <?php if($emp_is_good){ ?>положительный<?php }else{ ?>отрицательный<?php } ?> отзыв:

<i><?=$emp_feedback?></i>

Ознакомиться с ним и оставить ответный отзыв вы можете в заказе &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;.

<a href="<?=$order_url?>">Перейти к отзыву</a> / <a href="<?=$order_url?>">Оставить ответный отзыв</a> 
<?php

}