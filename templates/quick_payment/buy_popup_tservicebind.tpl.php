<?php
/**
 * Шаблон поумолчанию popup-окна "быстрой" оплаты
 */
?>
<div id="<?= @$popup_id ?>" data-quick-payment="<?=$unic_name ?>" class="b-shadow b-shadow_block b-shadow_center b-shadow_width_520 <?= (!@$is_show) ? 'b-shadow_hide' : '' ?> b-shadow__quick quick_payment_tservicebind" style="display:block;">
    <div class="b-shadow__body b-shadow__body_pad_15_20">

        <div class="b-fon <?= @$popup_title_class_bg ?>">
            <div class="b-layout__title b-layout__title_padbot_5">
                <span class="b-icon b-page__desktop b-page__ipad b-icon_float_left b-icon_top_4 <?= @$popup_title_class_icon ?>"></span>
                <?= @$popup_title ?>
            </div>
        </div>

        <form>
            <input type="hidden" value="<?= $ammount ?>" class="input-ammount" disabled="disabled" />
            <input type="hidden" name="kind" value="<?=$kind?>" />
            <input type="hidden" name="prof_id" value="<?=$prof_id?>" />
            <input type="hidden" name="is_prolong" value="<?=(bool)$date_stop?>" />
            <input type="hidden" name="redirect" class="input-redirect" value="" />
            <?php if ($disable_tservices): ?>
                <input type="hidden" name="tservice_text_db_id" value="<?=$tservices_cur?>" />
            <?php endif; ?>
            
            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">Услуга
            <?php if ($disable_tservices): ?>
                <strong>"<?=$tservices_cur_text?>"</strong>
            </div>
            <?php else: ?>
            </div>
            <div class="b-layout b-layout_padleft_20 b-layout_margbot_20">
                <script type="text/javascript"> var tservicesList = <?=$tservices?></script>
                <div class="b-combo b-combo_zindex_4">
                    <div class="
                         b-combo__input 
                         b-combo__input_multi_dropdown 
                         show_all_records 
                         b-combo__input_width_440 
                         b-combo__input_width_220_iphone
                         multi_drop_down_default_column_0  
                         b-combo__input_arrow_yes disallow_null
                         b-combo__input_init_tservicesList 
                         drop_down_default_<?=$tservices_cur?>">
                        <input id="tservice_text" class="b-combo__input-text b-combo__input-text_fontsize_15" name="" type="text" size="80" readonly="readonly" value=""/>
                    </div>                    
                </div>
            </div>
            <?php endif; ?>
            
            
            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20"><?=$popup_subtitle?></div>

            <table class="b-layout__table b-layout__table_margbot_20 b-layout__table_width_full">
                <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_padright_35 b-layout__td_padleft_20 b-layout__td_width_160 b-layout__td_pad_null_iphone">
                        <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                            <a class="b-button b-button_poll_plus b-button_absolute b-button_z-index_3 b-button_top_8 b-button_right_5" href="javascript:void(0)"></a>
                            <a class="b-button b-button_poll_minus b-button_absolute b-button_z-index_3 b-button_top_8 b-button_left_5" href="javascript:void(0)"></a>
                            <div class="b-combo__input b-combo__input_width_80 ">
                                <input name="weeks" type="text" size="80" value="0" maxlength="3" class="b-combo__input-text b-combo__input-text_center b-combo__input-text_bold input-weeks">
                            </div>
                        </div>
                        <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_width_60 tservicebind_weeks">неделя</div> 
                    </td>
                    <td class="b-layout__td">
                        <div class="b-layout__txt b-layout__txt_fontsize_11">
                            Размещение до <span class="b-layout__bold tservicebind_date"><?=$date_stop?></span><br>
                            <?= $profession ?><br>
                            <?php if ($addprof): ?>Эта специализация будет добавлена в ваш профиль.<?php endif;?>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">Сумма и способ оплаты</div>

        <div class="b-layout <?php //b-layout_waiting  ?>">
            <div data-quick-payment-error-screen="true" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                    <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                    <span data-quick-payment-error-msg="true"></span>
                </div>
            </div>

            <?=$promo_code?>
            
            <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_padleft_null_iphone b-layout__txt_padbot_20 b-layout__txt_fontsize_11">
                Сумма к оплате: <span class="b-layout__bold"><span class="quick_sum_pay"></span> руб.</span><br/>
                <span class="pay_none">Она будет списана с личного счета, на нем <strong class="ac_sum"><?= $ac_sum ?></strong> руб.</span>
                <span class="pay_part">
                    Часть суммы (<?= $ac_sum ?> руб.) есть на Вашем личном счете.<br />
                    Остаток (<span class="quick_sum_part"></span> руб.) вам нужно оплатить одним из способов:
                </span>
                <span class="pay_full">Ее вы можете оплатить одним из способов:</span>
            </div>
            <?php
            if (!empty($payments)):
                ?>
                <div class="payments">
                    <div class="b-buttons b-buttons_padleft_20 b-layout__txt_padleft_null_iphone b-buttons_padbot_10"> 
                        <?php foreach ($payments as $key => $payment): ?>
                            <?php if (isset($payment['title'])): ?>
                                <a class="b-button b-button_margbot_5 b-button__pm <?= @$payment['class'] ?>" 
                                   href="javascript:void(0);" 
                                   <?=(isset($payment['data-maxprice']))?'data-maxprice="'.$payment['data-maxprice'].'"':''?> 
                                   <?= (isset($payment['wait'])) ? 'data-quick-payment-wait="' . $payment['wait'] . '"' : '' ?> 
                                   data-quick-payment-type="<?= $key ?>"><span class="b-button__txt"><?= @$payment['title'] ?></span></a> 
                                <?php if (isset($payment['content_after'])): ?>
                                <div class="<?=$key?>_text b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_valign_middle b-layout__txt_width_440">
                                    <?=$payment['content_after']?>
                                </div>
                                <?php endif; ?>
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
                <?php
            endif;
            ?>
            <div class="payment_account">
                <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10">
                    <a class="b-button b-button_flat b-button_flat_green" 
                       href="javascript:void(0);" 
                       data-quick-payment-type="<?= $payment_account ?>">Оплатить <span class="quick_sum_pay_acc"></span> руб.</a> </div>
            </div>

        </div>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>
