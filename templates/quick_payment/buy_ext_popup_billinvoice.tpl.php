<?php

/**
 * Общий оплаты для BillInvoice
 */

?>
<div id="<?=@$popup_id?>" 
     data-quick-ext-payment="<?=$unic_name?>" 
     class="
        b-shadow 
        b-shadow_block 
        b-shadow_center 
        b-shadow_width_520 <?=(!@$is_show)?'b-shadow_hide':'' ?> 
        b-shadow_zindex_11">
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

            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">
                Ваши реквизиты:
            </div>
            <table class="b-layout__table b-layout__table_width_full">
                <?php if(isset($reqvs) && !empty($reqvs)): ?>
                    <?php foreach($reqvs as $reqv):
                            $padbot = @$reqv['padbot'];
                            $label_width = ($rt_ru)?160:200;
                    ?>
                <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_<?=$label_width?><?php if($padbot): ?> b-layout__td_padbot_<?=$padbot?><?php endif; ?>">
                        <span class="b-layout__txt b-layout__txt_fontsize_13">
                            <?=$reqv['label']?>
                        </span>
                    </td>
                    <td class="b-layout__td <?php if($padbot): ?>b-layout__td_padbot_<?=$padbot?><?php endif; ?>">
                        <span class="b-layout__txt b-layout__txt_fontsize_13 b-layout__txt_bold">
                            <?=$reqv['value']?>
                        </span>
                    </td>
                </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php
                    if(isset($payments) && !empty($payments)):
                ?>
                <tr class="b-layout__tr">
                    <td class="b-layout__td" colspan="2">
                        <div data-quick-payment-error-screen="true" class="b-fon b-fon_margtop_20 b-layout_hide">
                            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                                <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                                <span data-quick-payment-error-msg="true"></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__td"></td>
                    <td class="b-layout__td b-layout__td_padtop_20 b-layout__td_padbot_5">
                        <div data-quick-payment-list="true">
                            <div class="b-buttons"> 
                                <?php foreach($payments as $key => $payment): ?>
                                    <?php if (isset($payment['title'])): ?>
                                        <a class="b-button b-button_flat b-button_flat_green b-button_nowrap <?=@$payment['class']?>" 
                                           href="javascript:void(0);" 
                                           <?=(isset($payment['wait']))?'data-quick-payment-wait="'.$payment['wait'].'"':''?> 
                                           data-quick-payment-type="<?=$key?>"><?=@$payment['title']?></a>
                                   <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php
                    endif;
                ?>
            </table>
<?php
            if(isset($payments) && !empty($payments)):
?>
            <div data-quick-payment-wait-screen="true" class="b-layout__wait b-layout__txt_fontsize_15 b-layout__txt_color_6db335 b-layout_hide">
                <span data-quick-payment-wait-msg="true"></span>
                <div class="b-layout__txt b-layout__txt_center b-layout__txt_padtb_10">
                    <img width="80" height="20" src="<?=WDCPREFIX?>/images/Green_timer.gif">
                </div>
            </div>
            <div data-quick-payment-success-screen="true" class="b-layout__success b-layout__txt_fontsize_15 b-layout__txt_color_323232 b-layout_hide">
                <span data-quick-payment-success-msg="true"></span>
                <div class="b-buttons b-buttons_center b-button_margtop_15">
                    <a data-quick-payment-close="true" href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green">Закрыть</a>
                </div>
            </div>
<?php
            endif;
?>
        </div>
    </div>
    <span data-quick-payment-close="true" class="b-shadow__icon b-shadow__icon_close"></span>
</div>