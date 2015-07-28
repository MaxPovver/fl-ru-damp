<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
if ( isset($_REQUEST['site']) ) {
    // чтобы не подключался класс со старой админкой
    $siteTmp = $_REQUEST['site'];
    $_REQUEST['site'] = '';
    $psbr = sbr_meta::getInstance();
    $_REQUEST['site'] = $siteTmp;
} else {
    $psbr = sbr_meta::getInstance();
}
$psbr->setGetterSchemes(1);
$count_new_sbr = $psbr->getCountCurrentsSbr();
$template_popup = "";
$psbr->setGetterSchemes(0);
$count_old_sbr = $psbr->getCountCurrentsSbr();
if($count_new_sbr <=0 && $count_old_sbr <= 0) {
    $link_sbr = '/' . sbr::NEW_TEMPLATE_SBR . '/';
} elseif($count_new_sbr > 0 && $count_old_sbr <= 0) {
    $link_sbr = '/' . sbr::NEW_TEMPLATE_SBR . '/';
} else {
    $link_sbr = 'javascript:void(0)';
    $onclick  = "$('popup_sbr_menu').toggleClass('b-shadow_hide')";
}

if($count_new_sbr <=0 && $count_old_sbr <= 0) {
    return false;
}

$show_popup = ($old_tip['count'] != 0 || $tip['count'] != 0);
ob_start();
?>
<div id="popup_sbr_menu" class="b-shadow b-shadow_m b-shadow_width_760 b-shadow_zindex_2 b-shadow_top_30 b-shadow_left_-335 b-shadow_hide">
	<div class="b-shadow__right">
		<div class="b-shadow__left">
			<div class="b-shadow__top">
				<div class="b-shadow__bottom">
					<div class="b-shadow__body b-shadow__body_pad_10_15 b-shadow__body_bg_fff">
						<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                        	<tr class="b-layout__tr">
                            	<? if($count_old_sbr > 0) { ?>
                                <td class="b-layout__left b-layout__left_padright_20">
                                	<div class="b-layout__txt">
                                        У Вас <?= $count_old_sbr?> <?= ending($count_old_sbr, 'сделка, открытая', 'сделки, открытые', 'сделок, открытых');?> до 2 октября. Работа с<br /><?= ( $count_old_sbr > 1 ? "ними" : "ней"); ?> &mdash; <a class="b-layout__link b-layout__link_color_0f71c8 b-layout__link_nowrap" href="/norisk2/">в старом интерфейсе Безопасных Сделок</a> 
                                        <?php if($old_tip['count'] > 0) {?>
                                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335">+ Новое событие</div>
                                        <?php }//if?>
                                    </div>
                                </td>
                                <? }//if?>
                                <? if($count_new_sbr > 0) { ?>
                            	<td class="b-layout__right">
                                	<div class="b-layout__txt">
                                        <?= ($count_old_sbr > 0 ? "И" : "У вас")?> <?= $count_new_sbr?> <?= ending($count_new_sbr, 'сделка, открытая', 'сделки, открытые', 'сделок, открытых');?> после 2 октября. Работа с<br /><?= ( $count_new_sbr > 1 ? "ними" : "ней"); ?> &mdash; <a class="b-layout__link b-layout__link_color_0f71c8 b-layout__link_nowrap" href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/">в новом интерфейсе Безопасных Сделок</a> 
                                        <?php if($tip['count'] > 0) {?>
                                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335">+ Новое событие</div>
                                        <?php }//if?>
                                    </div>
                                </td>
                                <? } else {//if?>
                                <td class="b-layout__right">
                                	<div class="b-layout__txt">
                                        С 2 октября работайте в  <a class="b-layout__link b-layout__link_color_0f71c8 b-layout__link_nowrap" href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/">новом интерфейсе Безопасных Сделок</a>.
                                    </div>
                                </td>
                                <? } //else?>
                            </tr>
                        </table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<span class="b-shadow__icon b-shadow__icon_close"></span>
	<span class="b-shadow__icon b-shadow__icon_nosik"></span>
</div>
<?php $template_popup = ob_get_clean();?>