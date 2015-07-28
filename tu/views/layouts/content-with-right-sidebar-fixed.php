<?php
if(!defined('IN_STDF')) 
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * Разметка страницы с правым сайдбаром фиксированной ширины
 */

// @var CController $this        контролер страницы
// @var string $content          основная часть страницы

// @var int $g_page_id    уникальный код страницы для таргетинга блочной рекламы по страницам
global $g_page_id; // уникальный код страницы для таргетинга блочной рекламы по страницам

?>


<div class="b-layout b-layout__page">
    <div class="b-layout__right b-layout__right_float_right b-layout__right_width_240 b-page__desktop">
        <?php echo $this->renderClip('sidebar') ?>
    </div>
    <div class="b-layout__one b-layout__left_margright_260 b-layout__one_width_full_ipad">
        <?php echo $content ?>
    </div>
</div>