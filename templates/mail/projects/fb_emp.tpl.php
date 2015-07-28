<?php

/*
    Уведомление заказчику о том, что исполнитель оставил отзыв (П-12)
 */
?>
<p>Здравствуйте.</p>
<p>По результатам сотрудничества в проекте «<a href="<?php echo $project_url ?>"><?php echo $project_title ?></a>» исполнитель <?php echo $frl_fullname ?> оставил вам <?php echo $rating > 0 ? 'положительный':'отрицательный'?> отзыв:</p>
<br />
<p><em><?php echo $text ?></em></p>
<br />
<p>Ознакомиться с отзывом мы можете в проекте «<a href="<?php echo $project_url ?>"><?php echo $project_title ?></a>» или в разделе «<a href="<?php echo $opinions_url ?>">Отзывы</a>» профиля.</p>
<p><a href="<?php echo $project_url ?>">Перейти в проект</a> / <a href="<?php echo $opinions_url ?>">Перейти к отзыву</a></p>
<br />
-----<br />
С уважением, команда FL.ru