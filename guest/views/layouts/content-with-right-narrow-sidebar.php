<?php
if(!defined('IN_STDF')) 
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * Разметка страницы с правым узким сайдбаром
 */

?>
<div class="b-layout__right b-layout__right_float_right b-layout__right_width_240 b-page__desktop">
    <?php echo $this->renderClip('sidebar') ?>
</div>
<div class="b-layout__one b-layout__left_margright_260 b-layout__one_width_full_ipad">
    <?php echo $content ?>
</div>