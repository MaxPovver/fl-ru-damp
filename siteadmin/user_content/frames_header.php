<?php
/**
 * Модерирование пользовательского контента. Фреймы. Шаблон шапки фрейма.
 */
?>
<span class="b-icon b-icon_float_right b-icon_dop_close" onclick="user_content.releaseStream(<?=$aOne['content_id']?>, '<?=$aOne['stream_id']?>');" title="Закрыть поток"></span>
<span id="sound-control-<?=$aOne['stream_id']?>" class="b-icon b-icon_float_right b-icon_margright_5"></span>
<h3 class="b-layout__h3 b-layout__h3_frame">
    <span id="span_num_<?=$aOne['stream_id']?>" class="b-layout__bord b-layout__bord_pad_2_5 b-layout__bord_a7">#<?=$aOne['title_num']?></span>&#160;&#160;&#160;<?=$sContentName?>
</h3>

<?php if ( $aCounters ) { ?>
<div class="i-shadow">
    <div id="<?=('counters_' . $aOne['content_id'] . '_' . $aOne['stream_id'])?>" class="b-shadow b-shadow_m" style="display: <?=($bShow && $bFirstIn ? '' : 'none')?>">
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div class="b-shadow__body b-shadow__body_pad_15 b-shadow__body_bg_fff">
                            <?php foreach ( $aCounters as $nKey => $aCnt ) { ?>
                            <div class="b-shadow__txt"><a class="b-shadow__link" href="<?=$aCnt['link']?>" target="_blank"><?=$aCnt['name']?></a> <span id="<?=($aOne['stream_id'] . '_counters' . $nKey)?>">(<?=$aCnt['counter']?>)</span></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <span class="b-shadow__icon b-shadow__icon_close" onclick="$(this).getParent().addClass('b-shadow_hide');$(this).getParent().setStyle('display', 'none');"></span>
    </div>
</div>
<?php } ?>

<div class="b-menu b-menu_rubric  b-menu_width_330">
    <ul class="b-menu__list b-menu__list_margleft_0">
        <li id="stream_<?=$aOne['stream_id']?>_tab_i0" class="b-menu__item b-menu__item_active b-menu__item_margright_15 b-menu__item_fontsize_11"><span class="b-menu__b1"><span class="b-menu__b2 ">непроверенные</span></span></li>
        <?php if ( !in_array($aOne['content_id'], user_content::$aNoApproved) ) { ?>
        <li id="stream_<?=$aOne['stream_id']?>_tab_i1" class="b-menu__item  b-menu__item_margright_15 b-menu__item_fontsize_11"><a class="b-menu__link b-menu__link_color_41" href="javascript:void(0);" onclick="user_content.tabMenu(<?=$aOne['content_id']?>, '<?=$aOne['stream_id']?>', 1);">проверенные</a></li>
        <?php } ?>
        <?php if ( !in_array($aOne['content_id'], user_content::$aNoRejected) ) { ?>
        <li id="stream_<?=$aOne['stream_id']?>_tab_i2" class="b-menu__item  b-menu__item_margright_15 b-menu__item_fontsize_11"><a class="b-menu__link b-menu__link_color_41" href="javascript:void(0);" onclick="user_content.tabMenu(<?=$aOne['content_id']?>, '<?=$aOne['stream_id']?>', 2);">заблокированные</a></li>
        <?php } ?>
    </ul>
</div>
<div class="b-buttons b-buttons_padtb_10">
    <a id="a_mass_<?=$aOne['stream_id']?>" class="b-button b-button_flat b-button_flat_grey b-button_float_right" href="javascript:void(0);" onclick="$('<?=$aOne['stream_id']?>').contentWindow.user_content.mass_submit();">Одобрить выделенное</a>
    <div class="b-check b-check_padtop_10">
        <input id="check_<?=$aOne['stream_id']?>" class="b-check__input" type="checkbox" onclick="user_content.mass_check(this, '<?=$aOne['stream_id']?>')" />
        <label class="b-check__label b-check__label_fontsize_13" for="check_<?=$aOne['stream_id']?>">Выбрать все</label>
    </div>
</div>

<?php if ( isset($bInFrames) && $bInFrames ) { 
    $sApproved = in_array($aOne['content_id'], user_content::$aNoApproved) ? '' : 'проверенные';
    $sRejected = in_array($aOne['content_id'], user_content::$aNoRejected) ? '' : 'заблокированные';
    ?>
<script type="text/javascript">
    user_content.tabMenuItems['<?=$aOne['stream_id']?>'] = ['непроверенные', '<?=$sApproved?>', '<?=$sRejected?>'];
</script>
<?php } ?>