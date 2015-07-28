<?php

/**
 * Общий шаблон popup-окна
 */

?>
<div id="<?=$popup_id?>" data-popup-window="true" 
     class="b-shadow 
            b-shadow_center 
            <?php if(isset($popup_width)): ?> b-shadow_width_<?=$popup_width?><?php endif; ?> 
            <?=(!isset($is_show))?'b-shadow_hide':'' ?> 
            b-shadow_adaptive">
    <div class="b-shadow__body b-shadow__body_pad_15_20">
        
        <?php if(isset($popup_title)): ?>
        <div class="b-fon <?=@$popup_title_class_bg?>">
            <div class="b-layout__title b-layout__title_padbot_5">
                <span class="b-icon b-page__desktop b-page__ipad b-icon_float_left b-icon_top_4 <?=@$popup_title_class_icon?>"></span>
                <?=$popup_title?>
                <?php if(isset($popup_subtitle)): ?>
                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_1 b-page__destop b-page__ipad">
                    <?=$popup_subtitle?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="b-layout <?php //b-layout_waiting ?>">
            <?php if(isset($items_title)): ?>
            <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_fontsize_15">
                <?=$items_title?>
            </div> 
            <?php endif; ?>
            
            <?=$content?>

            <div data-popup-error-screen="true" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                    <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                    <span data-popup-error-msg="true"></span>
                </div>
            </div>

            <div data-popup-wait-screen="true" class="b-layout__wait b-layout__txt_fontsize_15 b-layout__txt_color_6db335 b-layout_hide">
                <span data-popup-wait-msg="true"></span>
                <div class="b-layout__txt b-layout__txt_center b-layout__txt_padtb_10">
                    <img width="80" height="20" src="<?=WDCPREFIX?>/images/Green_timer.gif">
                </div>
            </div>
            
            <div data-popup-success-screen="true" class="b-layout__success b-layout__txt_fontsize_15 b-layout__txt_color_323232 b-layout_hide">
                <span data-popup-success-msg="true"></span>
                <div class="b-buttons b-buttons_center b-button_margtop_15">
                    <a data-popup-close="true" href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green">Закрыть</a>
                </div>
            </div>

            <div data-popup-buttons="true" class="b-buttons b-buttons_align_right">
                <a class="b-button b-button_disabled b-button_margbot_5 b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   data-popup-save="true">
                       Сохранить
                </a>
                <a class="b-button b-button_margbot_5 b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   data-popup-close="true">
                       Отмена
                </a>                
            </div>
            
        </div>
    </div>
    <span data-popup-close="true" class="b-shadow__icon b-shadow__icon_close"></span>
</div>