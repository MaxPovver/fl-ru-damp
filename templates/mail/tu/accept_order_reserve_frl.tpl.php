<?php

/*
 * Шаблон письма уведомление исполнителю о подстверждении заказа с резервом оплаты. (П-6)
 * Все переводы каретки (\n) будут заменены <br/> при выводе сообщения и при отправке письма
 */

$smail->subject = "Подтверждение заказа на услугу «{$order['title']}»";

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_days = tservices_helper::days_format($order['order_days']);
//@todo: нет даты начала работы пока тк не зарезервированы деньги!
//$order_end_date = date('d.m.Y', strtotime("+ {$order['order_days']} days",strtotime($order['accept_date'])));
//$tax_price = tservices_helper::cost_format($order['tax_price'], true, false, false);
$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$tax = $order['tax']*100;

?>
Здравствуйте.

Только что вы подтвердили заказ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; (предложенный заказчиком <?=$emp_fullname?>). 
Бюджет заказа – <?=$order_price ?>. 
Срок выполнения работы – <?=$order_days?><?php if(false): ?> (до <?=$order_end_date ?>)<?php endif; ?>.

Бюджет будет зарезервирован на сайте FL.ru и выплачен вам после завершения всех работ по заказу. Пожалуйста, не начинайте выполнение работы, пока заказчик не зарезервирует сумму.

<a href="<?=$order_url?>">Перейти к заказу</a> / <a href="<?=$order_url?>">Связаться с заказчиком</a>

<i>
Как работать с заказом:
1. Дождаться резервирования суммы заказчиком и начать выполнение работы;
2. По окончании работ предоставить заказчику результат;
3. Как только заказчик подтвердит закрытие заказа, вы получите зарезервированную сумму и сможете обменяться отзывами.
</i>

<?php if($tax > 0): ?>
Обратите внимание, что при завершении заказа с вашего личного счета на сайте будет списана комиссия в размере <?=$tax ?>% от выплаченной вам суммы за заказ.
<?php endif; ?>

С уважением, 
команда <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>