<?php

/*
 * Шаблон письма уведомление исполнителю о возвращении заказа в работу
 * Все переводы каретки (\n) будут заменены <br/> при выводе сообщения и при отправке письма
 */

$smail->subject = "Заказ «{$order['title']}» возвращен в работу";

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);

?>
Заказчик <?=$emp_fullname?> вернул заказ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; в работу. 
Пожалуйста, обговорите с заказчиком условия дальнейшего сотрудничества и продолжите выполнение работы. 

<a href="<?=$order_url?>">Перейти к заказу</a>