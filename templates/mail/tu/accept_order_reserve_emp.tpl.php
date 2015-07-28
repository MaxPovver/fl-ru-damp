<?php

/*
 * Шаблон письма уведомление заказчику о подтверждении заказа с резервом оплаты. (П-5)
 * Все переводы каретки (\n) будут заменены <br/> при выводе сообщения и при отправке письма
 */


$smail->subject = "Исполнитель подтвердил заказ на услугу «{$order['title']}»";

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$order_days = tservices_helper::days_format($order['order_days']);
//@todo: нет даты начала работы пока тк не зарезервированы деньги!
//$order_end_date = date('d.m.Y', strtotime("+ {$order['order_days']} days",strtotime($order['accept_date'])));

$reserve_price = tservices_helper::cost_format($order['reserve_data']['reserve_price'], true, false, false);
$reserve_tax = $order['reserve_data']['tax']*100;

?>
Здравствуйте.

Исполнитель <?=$frl_fullname?> подтвердил ваш заказ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; и начнет его выполнение, как только вы зарезервируете сумму. 
Бюджет заказа – <?=$order_price ?>. 
Сумма резервирования – <?=$reserve_price?> (бюджет + <?=$reserve_tax?>% комиссии).

Срок выполнения работы – <?=$order_days ?><?php if(false): ?> (до <?=$order_end_date?>)<?php endif; ?>.
Рекомендуем вам всю переписку с исполнителем вести непосредственно в заказе. 

<a href="<?=$order_url?>">Перейти к заказу и зарезервировать сумму</a> / <a href="<?=$order_url?>">Связаться с исполнителем</a>

<i>Как работать с заказом:
1. В процессе сотрудничества периодически контролировать выполнение работ, связываясь с исполнителем;
2. По окончании работ получить от исполнителя конечный результат, проверить его и затем завершить заказ, подтвердив выплату суммы;
3. Обменяться отзывами.</i>

-----
С уважением,
команда <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>