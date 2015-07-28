<?php

/*
    Уведомление заказчику об отказе от проекта со стороны исполнителя (П-13)
 */
?>
<p>Здравствуйте.</p>
<p>По результатам сотрудничества в проекте «<a href="<?php echo $project_url ?>"><?php echo $project_title ?></a>» заказчик <?php echo $emp_fullname ?> оставил вам положительный отзыв:</p>
<br />
<p><em><?php echo $text ?></em></p>
<br />
<p>Ознакомиться с отзывом мы можете в заказе «<a href="<?php echo $project_url ?>"><?php echo $project_title ?></a>» или в разделе «<a href="<?php echo $opinions_url ?>">Отзывы</a>» профиля.</p>
<p><a href="<?php echo $project_url ?>">Перейти в проект</a> / <a href="<?php echo $opinions_url ?>">Перейти к отзыву</a></p>
<br />
-----<br />
С уважением, команда FL.ru