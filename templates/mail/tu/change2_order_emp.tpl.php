<?php
/**
 * Шаблон письма уведомление заказчику об изменении заказа (П-4)
 * все переводы каретки (новая строка) будут заменены <br/>
 */

/**
 * Тема письма
 */
$smail->subject = "Ваш заказ «{$order['title']}» успешно изменен";

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$cancel_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'cancel', $order['emp_id']);

$is_new_reserve = tservices_helper::isOrderReserve($order['pay_type']);

?>
Вы изменили параметры заказа &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;:

<?php include 'change2_order.tpl.php'; ?>

<?php if($is_new_reserve): ?>
Исполнитель получил уведомление об изменении параметров заказа. Как только он обговорит с вами условия сотрудничества и подтвердит заказ, вы сможете зарезервировать бюджет заказа. После этого начнется выполнение работы. Ожидайте, пожалуйста.
<?php else: ?>
Исполнитель получил уведомление об изменении параметров заказа. Как только он обговорит с вами условия сотрудничества и подтвердит заказ, начнется выполнение работы. Ожидайте, пожалуйста.
<?php endif; ?>

<a href="<?=$order_url?>">Перейти к заказу</a> / <a href="<?=$cancel_url?>">Отменить его</a><?=PHP_EOL?>