<?php

/**
 * Шаблон оплаты ПРО
 */

?>
<div id="<?=@$popup_id?>" 
     data-quick-ext-payment="<?=$unic_name?>" 
     class="b-shadow 
            b-shadow_center 
            b-shadow_width_520 
            <?=(!@$is_show)?'b-shadow_hide':'' ?> 
            b-shadow__quick">
    <div class="b-shadow__body b-shadow__body_pad_30 g-txt_center">

        <div class="b-fon b-fon_bg_pro">
            <div class="b-layout__title b-layout__title_pro">
                <?php if(isset($popup_title)): ?>
                <small>
                    <?=$popup_title?>
                </small>
                <?php endif; ?>
                <div data-quick-payment-title="true"></div>
                <?php if(isset($popup_subtitle)): ?>
                    <?=$popup_subtitle?>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="b-layout b-layout_waiting_-10">
            
            <?=$content?>
            
            <?php if ($acc_sum > 0): ?>
            <div class="b-layout__txt b-layout__txt_fontsize_12 b-layout__txt_padbot_20">
                <span data-quick-payment-paynone="true" class="b-layout__txt_hide">
                    Cумма будет списана с личного счета, на нем 
                    <span data-quick-payment-accsum="<?=$acc_sum?>">
                        <?=view_cost_format($acc_sum, false)?>
                    </span> руб.
                </span>
                <span data-quick-payment-paypart="true" class="b-layout__txt_hide">
                    Часть суммы будет списана с личного счета, на нем <?=view_cost_format($acc_sum, false)?> руб.<br />
                    Остаток (<span data-quick-payment-partsum="true"></span> руб.) вам нужно оплатить одним из способов ниже.
                </span>        
                <span data-quick-payment-payfull="true" class="b-layout__txt_hide"><?php //@todo: пока не используется ?></span>                
            </div>            
            <?php endif; ?> 
            
            
            <div data-quick-payment-list="true" 
                 class="b-layout__txt 
                        b-layout__txt_bold 
                        b-layout__txt_fontsize_15 
                        b-layout__txt_padbot_10 
                        b-layout__txt_color_333">
                Оплати одним из способов:
            </div>

            <div data-quick-payment-error-screen="true" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                    <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                    <span data-quick-payment-error-msg="true"></span>
                </div>
            </div>
            
<?php
            if(isset($payments) && !empty($payments)):
?>
            <div data-quick-payment-list="true" 
                 class="b-buttons 
                        b-buttons_center 
                        b-buttons_marg_lr_-10 
                        b-buttons_padbot_10"> 
                <?php foreach($payments as $key => $payment): ?>
                    <?php if (isset($payment['title'])): ?>
                        <a class="b-button 
                                  b-button_left 
                                  b-button_marg_10_8 
                                  b-button__pm 
                                  b-button__pm_tide 
                                  <?=@$payment['class']?>" 
                           data-ga-event="<?php if($is_emp): ?>{ec: 'customer', ea: 'customer_propopup_paybutton_clicked'<?php else: ?>{ec: 'freelancer', ea: 'freelancer_propopup_paybutton_clicked'<?php endif; ?>,el: '<?=@$payment['short']?>'}" 
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
            <div data-quick-payment-account="true" class="b-layout_hide">
                <div class="b-buttons b-buttons_center">
                    <a class="b-button b-button_inline-block b-button_flat b-button_flat_green" 
                       href="javascript:void(0);" 
                       data-ga-event="<?php if($is_emp): ?>{ec: 'customer', ea: 'customer_propopup_paybutton_clicked'<?php else: ?>{ec: 'freelancer', ea: 'freelancer_propopup_paybutton_clicked'<?php endif; ?>,el: 'flbill'}"
                       data-quick-payment-type="<?=$payment_account?>">
                        Оплатить <span data-quick-payment-account-price="true"></span> руб.
                    </a> 
                </div>
            </div>
            <?php endif; ?>


            <?php if(isset($clientside_templates)): ?>
                <?php foreach ($clientside_templates as $key => $value): ?>
            <script type="text/template" id="<?=$key?>">
                <?=$value?>
            </script>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <span data-ga-event="<?php if($is_emp): ?>{ec: 'customer', ea: 'customer_propopup_closed'<?php else: ?>{ec: 'freelancer', ea: 'freelancer_propopup_closed'<?php endif; ?>,el: ''}" 
          data-quick-payment-close="true" 
          class="b-shadow__icon b-shadow__icon_close"></span>
</div>