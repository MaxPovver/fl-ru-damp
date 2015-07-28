<?php
/**
 * Шаблон письма уведомление заказчику о создании заказа услуги c резервированием суммы (П-2)
 */

/**
 * Тема письма
 */
$smail->subject = "Ваш заказ «{$order['title']}» успешно создан";

$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$tu_url = $GLOBALS['host'] . tservices_helper::card_link($order['tu_id'], $order['title']);
$order_days = tservices_helper::days_format($order['order_days']);
$cancel_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'cancel', $order['emp_id']);

?>
Здравствуйте.
<br/>
<br/>
Ваш заказ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; успешно создан, а исполнитель <?=$frl_fullname?> получил уведомление о нем. 
<br/>
Как только исполнитель обговорит с вами условия сотрудничества и подтвердит заказ, вы сможете зарезервировать бюджет заказа. 
После этого начнется выполнение работы. Ожидайте, пожалуйста.
<br/><br/>
<?php if($order['type'] == TServiceOrderModel::TYPE_TSERVICE): ?>
Напоминаем, что вы заказали услугу &laquo;<a href="<?=$tu_url?>"><?=$title?></a>&raquo; 
<?php if($order['order_extra']){ ?>
и дополнительно:
<br/>
    <?php foreach($order['order_extra'] as $idx ){ ?>
        <?php if(!isset($order['extra'][$idx])) continue; ?>
        - <?php echo reformat(htmlspecialchars($order['extra'][$idx]['title']), 30, 0, 1); ?><br/>
    <?php } ?>
<?php } ?>
на сумму <?=$order_price ?> со сроком выполнения <?=$order_days?>.
<?php else: ?>
Напоминаем, что заказ на сумму <?=$order_price ?> со сроком выполнения <?=$order_days?>.
<?php endif; ?>
<br/><br/>
<a href="<?=$order_url?>">Перейти к заказу</a> / 
<a href="<?=$cancel_url?>">Отменить его</a>
<br/><br/>
<i>Как работать с заказом:
<br/>
1. Сначала нужно обсудить с исполнителем все условия сотрудничества;<br/>
2. Дождаться подтверждения заказа исполнителем и зарезервировать на сайте необходимую сумму;<br/>
3. В процессе сотрудничества периодически контролировать выполнение работ, связываясь с исполнителем;<br/>
4. По окончании работ получить от исполнителя конечный результат, проверить его и затем завершить заказ (тем самым подтвердив выплату суммы);<br/>
5. Обменяться отзывами.
</i>
<br/>
<br/>
Обратите внимание, что при разногласиях в процессе сотрудничества вы всегда можете обратиться в Арбитраж. 
И вернуть зарезервированную сумму, если работа выполнена некачественно или не в срок.
<br/>
<br/>
С уважением, 
<br/>
команда <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>