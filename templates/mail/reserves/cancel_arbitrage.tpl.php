<?php
/**
 * Шаблон письма уведомление об отмене арбитража (П-16, П-17)
 */

/**
 * Тема письма
 */
$smail->subject = "Арбитраж по заказу «{$order['title']}» отменен";

$role = $is_emp ? 'Исполнитель продолжил' : 'Вы можете продолжить';
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
?>
Здравствуйте.
<br/>
<br/>
По согласованию Сторон Арбитражное рассмотрение заказа «<?=$title?>» было отменено. <?=$role?> выполнение работы.
<br/><br/>
<a href="<?=$order_url?>">Перейти к заказу</a>
<br/>
<br/>
-----
<br/>
<br/>
С уважением, команда <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>