<?php

/**
 * Часть шаблона письма уведомление исполнителю об изменении заказа
 * содержащая изменения в заказе
 */

$templ = '%s >> %s';

$order_price_txt = isset($order['old_order_price'])?sprintf($templ,
        tservices_helper::cost_format($order['old_order_price'], false),
        tservices_helper::cost_format($order['order_price'], true, false, false)):
        tservices_helper::cost_format($order['order_price'], true, false, false);       


$order_days_txt = isset($order['old_order_days'])?sprintf($templ,
        $order['old_order_days'],
        tservices_helper::days_format($order['order_days'])):
        tservices_helper::days_format($order['order_days']);

$is_new_reserve = tservices_helper::isOrderReserve($order['pay_type']);
$order_paytype_txt = $is_new_reserve?"С резервированием":"Без резервирования";

if(isset($order['old_pay_type']))
{
    $is_old_reserve = tservices_helper::isOrderReserve($order['old_pay_type']);
    $from_txt = $is_old_reserve?"С резервированием":"Без резервирования";
    $to_txt = !$is_old_reserve?"С резервированием":"Без резервирования";
    $order_paytype_txt = sprintf($templ,$from_txt,$to_txt);
}

?>
Бюджет: <?=$order_price_txt . PHP_EOL?>
Срок: <?=$order_days_txt . PHP_EOL?>
Тип оплаты: <?=$order_paytype_txt . PHP_EOL?>