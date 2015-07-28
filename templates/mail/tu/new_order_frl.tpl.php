<?php
/**
 * Шаблон письма уведомление исполнителю о создании заказа (УВ-2)
 * Так же используется при отправле ЛС поэтому все переводы каретки (\n) будут заменены <br/> при выводе сообщения и при отправке письма
 */

/**
 * Тема письма
 */
$smail->subject = "Заказ на услугу «{$order['title']}»";

$tax_price = tservices_helper::cost_format($order['tax_price'], true, false, false);
$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$order_days = tservices_helper::days_format($order['order_days']);
$tax = $order['tax'] * 100;

$accept_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'accept', $order['frl_id']);
$decline_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'decline', $order['frl_id']);

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

<a href="<?=$order_url?>">Перейти к заказу</a> / <a href="<?=$accept_url?>">Подтвердить его</a> / <a href="<?=$decline_url?>">Отказаться от выполнения</a>

<i>Как работать с заказом:
1. Сначала нужно обсудить с заказчиком все условия сотрудничества и способ оплаты;
2. Затем подтвердить заказ <?php if($tax > 0): ?>(при этом с вашего личного счета на сайте будет удержана комиссия <?=$tax_price?> за предоставление сервиса)<? endif; ?>;
3. Получить от заказчика предоплату (если обговорена) и начать выполнение работы;
4. По окончании работ предоставить заказчику результат и получить от него оплату;
5. Завершить заказ и обменяться отзывами.
</i>

Обратите внимание, что в процессе сотрудничества вы самостоятельно договариваетесь с заказчиком о сроках и способе оплаты. И самостоятельно несете все риски, связанные с оплатой работы и претензиями по качеству ее выполнения.

С уважением, 
команда <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>