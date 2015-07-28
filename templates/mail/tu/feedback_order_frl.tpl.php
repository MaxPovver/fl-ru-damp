<?php

/*
 * Шаблон уведомления исполнителю о том, что заказчик оставил ответный отзыв (УВ-12)
 * Так же используется при отправле ЛС поэтому все переводы каретки (новая строка) будут заменены <br/> при выводе сообщения и при отправке письма
 */

$smail->subject = "Отзыв в заказе «{$order['title']}»";

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$tu_url = $order['tu_id'] ? $GLOBALS['host'] . tservices_helper::card_link($order['tu_id'], $order['title']) : '';
$emp_feedback = reformat(htmlspecialchars($order['emp_feedback']), 30);
$emp_is_good = ($order['emp_rating'] > 0);
$feedback_url = $GLOBALS['host'] . "/users/{$order['freelancer']['login']}/opinions/";

?>
По результатам сотрудничества в заказе &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; 
заказчик оставил вам <?php if($emp_is_good){ ?>положительный<?php }else{ ?>отрицательный<?php } ?> отзыв:

<i><?=$emp_feedback?></i>

Ознакомиться с отзывом вы можете в заказе &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;, в разделе &laquo;<a href="<?= $feedback_url ?>">Отзывы</a>&raquo; профиля<?php if ($tu_url): ?> или в карточке услуги &laquo;<a href="<?=$tu_url?>"><?=$title?></a>&raquo;<?php endif; ?>.

<a href="<?=$order_url?>">Перейти к отзыву</a> / <a href="<?=$order_url?>">Оставить комментарий к отзыву</a>
