<?php

/*
    Уведомление заказчику о завершении проекта исполнителем и получении отзыва (П-7)
 */
?>
<p>Здравствуйте.</p>
<p>Исполнитель <?php echo $frl_fullname ?> завершил сотрудничество с вами по проекту «<a href="<?php echo $project_url ?>"><?php echo $project_title ?></a>» и оставил <?php echo $rating > 0 ? 'положительный':'отрицательный'?> отзыв:</p>
<br />
<p><em><?php echo $text ?></em></p>
<br />
<p>Ознакомиться с ним и оставить ответный отзыв (в течение 7 дней) вы можете в проекте «<a href="<?php echo $project_url ?>"><?php echo $project_title ?></a>» или в разделе «<a href="<?php echo $opinions_url ?>">Отзывы</a>» профиля.</p>
<p><a href="<?php echo $opinions_url ?>">Перейти к отзыву</a> / <a href="<?php echo $opinions_url ?>">Оставить ответный отзыв</a></p>
<br />
-----<br />
С уважением, команда FL.ru