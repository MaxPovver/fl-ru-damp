<?php

/*
 * Шаблон уведомления заказчику о том, что исполнитель оставил ответный отзыв. (УВ-11)
 * Так же используется при отправле ЛС поэтому все переводы каретки (новая строка) будут заменены <br/> при выводе сообщения и при отправке письма
 */

$smail->subject = "Отзыв в заказе «{$order['title']}»";

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$frl_feedback = reformat(htmlspecialchars($order['frl_feedback']), 30);
$frl_is_good = ($order['frl_rating'] > 0);
$feedback_url = $GLOBALS['host'] . "/users/{$order['employer']['login']}/opinions/";

?>
По результатам сотрудничества в заказе &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; 
исполнитель оставил вам <?php if($frl_is_good){ ?>положительный<?php }else{ ?>отрицательный<?php } ?> отзыв:

<i><?=$frl_feedback?></i>

Ознакомиться с ним и оставить ответный отзыв вы можете в заказе &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; или в разделе &laquo;<a href="<?= $feedback_url ?>">Отзывы</a>&raquo; профиля.

<a href="<?=$order_url?>">Перейти к отзыву</a> / <a href="<?=$order_url?>">Оставить ответный отзыв</a>
