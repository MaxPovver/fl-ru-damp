<?php

/*
 * Шаблон пиьсма уведомление исполнителю о старте работ и списании суммы, с задолженностью. (УВ-5)
 */

$smail->subject = "Подтверждение заказа на услугу «{$order['title']}»";

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_days = tservices_helper::days_format($order['order_days']);
$order_end_date = date('d.m.Y', strtotime("+ {$order['order_days']} days",strtotime($order['accept_date'])));
$tax_price = tservices_helper::cost_format($order['tax_price'], true, false, false);
$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$tax = $order['tax']*100;


?>
Здравствуйте.
<br/>
<br/>
Только что вы подтвердили заказ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; (предложенный заказчиком <?=$emp_fullname?>) и начали его выполнение. 
Сумма заказа – <?=$order_price?>. 
Срок выполнения работы – <?=$order_days?> (до <?=$order_end_date?>).
<br/>
За предоставление сервиса на сайте FL.ru с вашего личного счета была удержана комиссия в размере <?=$tax_price?> (<?=$tax?>% от суммы заказа). 
В результате списания на личном счете образовалась задолженность, погасить которую вам необходимо до <?=date('d.m.Y', $debt_timestamp)?>, во избежание блокировки услуг. 
Желаем удачного сотрудничества!
<br/>
<br/>
<a href="<?=$order_url?>">Перейти к заказу</a> / 
<a href="<?=$order_url?>">Связаться с заказчиком</a> / 
<a href="<?php echo "{$GLOBALS['host']}" ?>/bill/">Погасить задолженность</a>
<br/>
<br/>
<i>
Как работать с заказом:<br/>
1. Получить от заказчика предоплату (если обговорена) и начать выполнение работы;<br/>
2. По окончании работ предоставить заказчику результат и получить от него оплату;<br/>
3. Погасить задолженность на личном счете;<br/>
4. Завершить заказ и обменяться отзывами;
</i>
<br/>
<br/>
Напоминаем, что в процессе сотрудничества вы самостоятельно договариваетесь с заказчиком о сроках и способе оплаты. 
И самостоятельно несете все риски, связанные с оплатой работы и претензиями по качеству ее выполнения.
<br/>
<br/>
С уважением, 
<br/>
команда <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>