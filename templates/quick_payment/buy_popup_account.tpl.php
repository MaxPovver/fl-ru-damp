<?php
/**
 * Шаблон по-умолчанию popup-окна нецелевого пополнения счета.
 * Не путать с погашение задолженности
 */
?>
<div id="<?= @$popup_id ?>" data-quick-payment="<?= $unic_name ?>" class="b-shadow b-shadow_block b-shadow_vertical-center b-shadow_width_520 <?= (!@$is_show) ? 'b-shadow_hide' : '' ?> b-shadow__quick">
    <div class="b-shadow__body b-shadow__body_pad_15_20">

        <div class="b-fon <?= @$popup_title_class_bg ?>">
            <div class="b-layout__title b-layout__title_padbot_20">
                <span class="b-icon b-page__desktop b-page__ipad b-icon_float_left b-icon_top_4 <?= @$popup_title_class_icon ?>"></span>
                <?= @$popup_title ?>
            </div>
        </div>
        
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20"><?=$popup_subtitle?></div>
        <table class="b-layout__table b-layout__table_margbot_20 b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padright_35 b-layout__td_padleft_20 b-layout__td_width_210">
                    <form>
                        <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                            <div class="b-combo__input b-combo__input_width_140 ">
                                <input id="account_price" name="price" 
                                    type="text" size="80" maxlength="6" 
                                    class="b-combo__input-text b-combo__input-text_center b-combo__input-text_bold"
                                    data-minimum="<?=$min_price?>" data-maximum="<?=$max_price?>">
                            </div>
                        </div>
                        <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_width_60">руб.</div> 
                    </form>
                </td>
                <td class="b-layout__td">
                    <div class="b-layout__txt b-layout__txt_fontsize_11">
                        Минимум <?=view_cost_format($min_price, false)?> руб.<br />
                        Максимум <?=view_cost_format($max_price, false)?> руб.
                    </div>
                </td>
            </tr>
        </table>
 
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20"><?=$payments_title?></div>
        <div class="b-layout">
            <div data-quick-payment-error-screen="true" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                    <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                    <span data-quick-payment-error-msg="true"></span>
                </div>
            </div>
            <?php if (!empty($payments)): ?>
                <div class="payments">
                    <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> 
                        <?php foreach ($payments as $key => $payment): ?>
                            <?php if (isset($payment['title'])): ?>
                                <a class="b-button b-button_margbot_5 b-button__pm <?= @$payment['class'] ?>" 
                                   href="javascript:void(0);" 
                                   <?=(isset($payment['data-maxprice']))?'data-maxprice="'.$payment['data-maxprice'].'"':''?> 
                                   <?= (isset($payment['wait'])) ? 'data-quick-payment-wait="' . $payment['wait'] . '"' : '' ?> 
                                   data-quick-payment-type="<?= $key ?>"><span class="b-button__txt"><?= @$payment['title'] ?></span></a> 
                               <?php endif; ?>
                           <?php endforeach; ?>
                    </div>
                </div>

                <div data-quick-payment-wait-screen="true" class="b-layout__wait b-layout__txt_fontsize_15 b-layout__txt_color_6db335 b-layout_hide">
                    <span data-quick-payment-wait-msg="true"></span>
                    <div class="b-layout__txt b-layout__txt_center b-layout__txt_padtb_10">
                        <img width="80" height="20" src="/images/Green_timer.gif">
                    </div>
                </div>

                <div class="__quick_payment_form b-layout_hide"></div>
            <?php endif; ?>
        </div>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close" data-quick-payment-close="1"></span>
</div>