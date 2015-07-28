<?php

/**
 * П-20 - Заказчику об успешно зарезервированной сумме
 */

$smail->subject = "Сумма по заказу «{$order['title']}» зарезервирована";

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$reserve_price = tservices_helper::cost_format($order['reserve_data']['price'], true, false, false);

?>
Cумма <?=$reserve_price?> по заказу &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; зарезервирована. Мы уведомили об этом Исполнителя, и он приступил к выполнению работы по заказу.
Успешного вам сотрудничества!

<a href="<?=$order_url?>">Перейти к заказу</a>
