<?php

/**
 * П-21 - Исполнителю об успешно зарезервированной сумме
 */

$smail->subject = "Сумма по заказу «{$order['title']}» зарезервирована";

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$reserve_price = tservices_helper::cost_format($order['reserve_data']['price'], true, false, false);

?>
Заказчик зарезервировал сумму <?=$reserve_price?> в заказе &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;. Далее вы можете начать выполнение работы по заказу.
Успешного вам сотрудничества!

<a href="<?=$order_url?>">Перейти к заказу</a>
