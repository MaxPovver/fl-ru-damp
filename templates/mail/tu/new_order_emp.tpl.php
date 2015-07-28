<?php
/**
 * Шаблон письма уведомление заказчику о создании заказа услуги (УВ-3)
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
Как только исполнитель обговорит с вами условия сотрудничества и подтвердит заказ, начнется выполнение работы. Ожидайте, пожалуйста.
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
1. Сначала нужно обсудить с исполнителем все условия сотрудничества и способ оплаты;<br/>
2. Дождаться подтверждения заказа исполнителем и перечислить исполнителю предоплату (если обговорена);<br/>
3. В процессе сотрудничества периодически контролировать выполнение работ, связываясь с исполнителем;<br/>
4. По окончании работ получить от исполнителя конечный результат, проверить его и затем произвести полную оплату;<br/>
5. Завершить заказ и обменяться отзывами.
</i>
<br/>
<br/>
В заказе вы выбрали способ оплаты - <b>Прямая оплата исполнителю</b>.<br/>
В этом случае вы несете все риски, связанные с несвоевременным и/или некачественным выполнением работы. И не имеете возможности обратиться в Арбитраж.
<br/>
<br/>
Предлагаем вам <a href="<?=$order_url?>?tu_edit_budjet=1&paytype=1"><b>изменить способ оплаты на Безопасную сделку</b></a><br/>
<a href="https://www.fl.ru/promo/bezopasnaya-sdelka/">Промо-страница Безопасной сделки.</a>
<br/>
<br/>
С уважением, 
<br/>
команда <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>