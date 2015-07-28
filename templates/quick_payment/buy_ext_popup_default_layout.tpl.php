<?php
/**
 * Общий шаблон оплаты
 */


$pay_sum = $price - $acc_sum;
$pay_sum = ($pay_sum > 0 && $pay_sum < $minimum_payed_sum)?$minimum_payed_sum:$pay_sum;
$is_paynone = $pay_sum <= 0;
$is_paypart = ($pay_sum > 0) && ($acc_sum > 0);
$is_payfull = ($pay_sum > 0) && ($acc_sum <= 0);

?>
<div id="<?=@$popup_id?>" 
     data-quick-ext-payment="<?=$unic_name?>" 
     class="b-shadow b-shadow_block b-shadow_vertical-center b-shadow_width_520 <?=(!@$is_show)?'b-shadow_hide':'' ?> b-shadow__long b-shadow_zindex_11">
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
                Сумма и способ оплаты
            </div>
            
            <?=$promo_code?>
            
            <div data-quick-payment-error-screen="true" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                    <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                    <span data-quick-payment-error-msg="true"></span>
                </div>
            </div>
            
            <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_fontsize_11">
                Сумма к оплате: 
                <strong data-quick-payment-price="<?=$price?>">
                    <?=view_cost_format($price, false)?>
                </strong> руб.<br/>
            </div>
            
            <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_padleft_20 b-layout__txt_fontsize_11">
                <?php if ($acc_sum > 0): ?>
                <span data-quick-payment-paynone="true" <?php if(!$is_paynone): ?>class="b-layout__txt_hide"<?php endif; ?>>
                    Она будет списана с личного счета, на нем 
                    <strong data-quick-payment-accsum="<?=$acc_sum?>">
                        <?=view_cost_format($acc_sum, false)?>
                    </strong> руб.
                </span>
                <span data-quick-payment-paypart="true" <?php if(!$is_paypart): ?>class="b-layout__txt_hide"<?php endif; ?>>
                    Часть суммы (<strong><?=view_cost_format($acc_sum, false)?></strong> руб.) есть на Вашем личном счете.<br />
                    Остаток (<strong data-quick-payment-partsum="true"><?=view_cost_format($pay_sum, false)?></strong> руб.) вам нужно оплатить одним из способов:
                </span>
                <?php endif; ?>
                <span data-quick-payment-payfull="true" <?php if(!$is_payfull): ?>class="b-layout__txt_hide"<?php endif; ?>>
                    Ее вы можете оплатить одним из способов:
                </span>
            </div>
<?php
            if(isset($payments) && !empty($payments)):
?>
            <div data-quick-payment-list="true" <?php if($is_paynone): ?>class="b-layout_hide"<?php endif; ?>>
                <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> 
                    <?php foreach($payments as $key => $payment): ?>
                        <?php if (isset($payment['title'])): ?>
                            <a class="b-button b-button_margbot_5 b-button__pm <?=@$payment['class']?>" 
                               href="javascript:void(0);" 
                               <?=(isset($payment['data-maxprice']))?'data-maxprice="'.$payment['data-maxprice'].'"':''?> 
                               <?=(isset($payment['wait']))?'data-quick-payment-wait="'.$payment['wait'].'"':''?> 
                               data-quick-payment-type="<?=$key?>"><span class="b-button__txt"><?=@$payment['title']?></span></a> 
                            <?php if (isset($payment['content_after'])): ?>
                            <div class="<?=$key?>_text b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_valign_middle b-layout__txt_padleft_5 b-layout__txt_padbot_5">
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
            <?php if ($acc_sum > 0): ?>
            <div data-quick-payment-account="true" <?php if(!$is_paynone): ?>class="b-layout_hide"<?php endif ?>>
                <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10">
                    <a class="b-button b-button_flat b-button_flat_green" 
                       href="javascript:void(0);" 
                       data-quick-payment-type="<?=$payment_account?>">
                        Оплатить 
                        <span data-quick-payment-account-price="true">
                            <?=view_cost_format($price, false)?>
                        </span> руб.
                    </a> 
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <span data-quick-payment-close="true" class="b-shadow__icon b-shadow__icon_close"></span>
</div>