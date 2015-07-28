<?php
/**
 * Шаблон письма уведомление исполнителю о создании заказа с резервированием суммы (П-1)
 * Так же используется при отправле ЛС поэтому все переводы каретки (\n) будут заменены <br/> при выводе сообщения и при отправке письма
 */

/**
 * Тема письма
 */
$smail->subject = "Заказ на услугу «{$order['title']}» с резервированием суммы";

$tax_price = tservices_helper::cost_format($order['tax_price'], true, false, false);
$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$order_days = tservices_helper::days_format($order['order_days']);

$accept_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'accept', $order['frl_id']);
$decline_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'decline', $order['frl_id']);

$tax = $order['tax'] * 100;
?>
Здравствуйте.

Заказчик <?=$emp_fullname?> предлагает вам заказ на услугу &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;
<?php if($order['order_extra']){ ?>
и дополнительно:
<?php 
    foreach($order['order_extra'] as $idx )
    {
        if(!isset($order['extra'][$idx])) continue; 
        echo '- ' . reformat(htmlspecialchars($order['extra'][$idx]['title']), 30, 0, 1).PHP_EOL;
    }
 } 
?>
на сумму <?php echo $order_price ?> со сроком выполнения <?=$order_days?>.

Сумма оплаты будет зарезервирована на сайте FL.ru и выплачена вам после завершения всех работ по заказу.

<a href="<?=$order_url?>">Перейти к заказу</a> / <a href="<?=$accept_url?>">Подтвердить его</a> / <a href="<?=$decline_url?>">Отказаться от выполнения</a>

<i>Как работать с заказом:
1. Сначала нужно обсудить с заказчиком все условия сотрудничества;
2. Затем подтвердить заказ;
3. Дождаться, пока заказчик зарезервирует сумму, и после этого начать выполнение работы;
4. По окончании работ предоставить заказчику результат, чтобы он принял его;
5. Завершить заказ, обменяться отзывами и получить оплату.
</i>
<?php if($tax > 0): ?>

Обратите внимание, что при выплате суммы с вас будет удержана комиссия сервиса в размере <?=$tax?>% от бюджета заказа.

<?php endif; ?>
С уважением, 
команда <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>