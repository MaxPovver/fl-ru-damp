<?php
if(!defined('IN_STDF')) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * Разметка страницы с правым сайдбаром
 */

// @var CController $this        контролер страницы
// @var string $content          основная часть страницы


// @var int $prof_id      код категории для таргетинга блочной рекламы по категориям работ
// @var int $g_page_id    уникальный код страницы для таргетинга блочной рекламы по страницам
global $g_page_id; // уникальный код страницы для таргетинга блочной рекламы по страницам

$uid = get_uid(false);


unset($g_page_id);
include (ABS_PATH . '/templates/top/new_project_button.php'); 

?>
<a name="frl" id="frl_anc"></a>

<?php echo $this->renderClip('header') ?>

<div class="b-layout__one b-layout__one_width_25ps b-layout__one_float_right b-page__desktop">
    <?php if(!isset($new_project_button_is_visible) && (is_emp() || !$uid)):  ?>
        <div class="b-layout b-layout_padbot_20">
            <a class="b-button b-button_flat b-button_flat_orange2 b-button_block b-button_margtop_-1" href="/public/?step=1&kind=1&red=">Бесплатно опубликовать задание</a>
        </div>
    <?php endif; ?>

	<!-- Banner 240x400 -->
        <?= printBanner240(false); ?>
	<!-- end of Banner 240x400 -->
    
    <?php if(!$uid): ?>
        <div id="seo_block" class="b-layout b-layout_padtop_20">
            <h2 class="b-layout__txt b-layout__txt_color_666 b-layout__txt_bold b-layout__txt_padbot_10">
                <?php echo SeoTags::getInstance()->getSideHead() ?>
            </h2>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_666">
                <?php echo SeoTags::getInstance()->getSideText() ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="b-layout__one b-layout__one_relative b-layout__one_width_72ps b-layout__one_width_full_ipad">
    <?php echo $this->renderClip('content_top') ?>
    <?php echo $this->renderClip('categories') ?>
	<?php echo $this->renderClip('sidebar') ?>
	<?php echo $content ?>

</div>

<a id="upper" class="b-page__up" href="#" style=" visibility:hidden;"></a>

<?php echo $this->renderClip('footer') ?>