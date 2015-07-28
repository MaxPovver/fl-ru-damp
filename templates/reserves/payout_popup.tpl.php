<?php
/**
 * Шаблон popup-окна подтверждения выплаты средств
 */

//$fn_url = sprintf("/users/%s/setup/finance/", $_SESSION['login']);

?>
<div id="<?=ReservesPayoutPopup::getPopupId($idx)?>" 
     data-reserves-payout="true" 
     class="b-shadow b-shadow_block b-shadow_center b-shadow_width_520 <?=(!@$is_show)?'b-shadow_hide':'' ?> b-shadow__quick">
    <div class="b-shadow__body b-shadow__body_pad_15_20">
        <h2 class="b-layout__title">
            Выплата суммы
        </h2>

        <div class="b-layout <?php //b-layout_waiting ?>">

            <?php if($is_feedback || !$is_allow_feedback): ?>
                <form action="" method="post">
                    <input type="hidden" name="oid" value="<?= $idx ?>" />
                    <input type="hidden" name="hash" value="<?= $hash ?>" />
                </form>
            <?php else: ?>
            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_10">
                Ваш отзыв о сотрудничестве:
            </div>
            <div class="b-layout b-layout_padleft_20 b-layout_padbot_20">
                <form action="" method="post">
                    <input type="hidden" name="oid" value="<?= $idx ?>" />
                    <input type="hidden" name="hash" value="<?= $hash ?>" />
                    <div class="b-radio b-radio_layout_horizontal">
                        <div class="b-radio__item b-radio__item_padbot_20 b-radio__item_padright_20">
                            <input<?= ($rating >= 0) ? ' checked' : '' ?> data-validators="fbtype" type="radio" value="1" name="fbtype" class="b-radio__input" id="plus-<?= $idx ?>">
                            <label for="plus-<?= $idx ?>" class="b-radio__label b-radio__label_fontsize_13 b-radio__label_color_6db335">Положительный</label>
                        </div>
                        <div class="b-radio__item b-radio__item_padbot_20">
                            <input<?= ($rating < 0) ? ' checked' : '' ?> data-validators="fbtype" type="radio" value="-1" name="fbtype" class="b-radio__input" id="minus-<?= $idx ?>">
                            <label for="minus-<?= $idx ?>" class="b-radio__label b-radio__label_fontsize_13 b-radio__label_color_c10600">Отрицательный</label>
                        </div>
                    </div>
                    <div class="b-textarea">
                        <textarea data-validators="maxLength:500" 
                                  class="b-textarea__textarea b-textarea__textarea_italic" 
                                  rows="5" cols="80" maxlength="500" name="feedback" 
                                  placeholder="Введите текст отзыва"></textarea>
                    </div> 
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">
                        Не более 500 символов.
                    </div>
                </form>
            </div>             
            <?php endif; ?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_15">
                Сумма выплаты
            </div>
            
            <div data-reserves-payout-error-screen="true" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                    <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                    <span data-reserves-payout-error-msg="true"></span>
                </div>
            </div>
            
            <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_padbot_10 b-layout__txt_fontsize_13">
                Сумма оплаты за работу: <?=$price_all?><br>
                <?php if ($price_ndfl): ?>
                    Налог НДФЛ (13%): <?=$price_ndfl?><br>
                <?php endif; ?>
                <div class="b-layout__bold">Итого к выплате: <span><?=$price?></span></div>
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_15">
                Способ выплаты
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_padleft_20 b-layout__txt_fontsize_11">
                Ваш статус<?php if(@$fn_url): ?> (<a class="b-layout__link" href="<?=$fn_url?>">изменить</a>)<?php endif; ?>: <?=$form_txt?>, <?=$rez_txt?><br>
                Вам доступны следующие способы выплаты:
            </div>
<?php
            if(!empty($payments)):
?>
            <div>
                <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> 
                    <?php foreach($payments as $key => $payment): 
                            $pay_num = (isset($payment['num']) && !empty($payment['num']))?$payment['num']:null;
                    ?>
                    <div class="b-button_inline-block b-button_margbot_5">
                        <a class="b-button b-button_margbot_5 b-button__pm<?=(!$pay_num)?' b-button_disabled':''?> <?=@$payment['class']?>" 
                           href="javascript:void(0);" 
                           <?=(isset($payment['wait']))?'data-reserves-payout-wait="'.$payment['wait'].'"':''?> 
                           <?php if($pay_num): ?>data-reserves-payout-type="<?=$key?>"<?php endif; ?>><span class="b-button__txt"><?=@$payment['title']?></span></a>
                        <br/>
                        <span class="b-button__txt b-button__txt_fontsize_11 b-button__txt_center">
                            <?php
                                if($pay_num):
                            ?>
                                <?=$pay_num?>
                                <?php if(@$fn_url): ?>
                                <br/>
                                <a href="<?=$fn_url?>">изменить</a>
                                <?php endif; ?>
                            <?php
                                else:
                            ?>
                                реквизиты не указаны
                                <?php if(@$fn_url): ?>
                                <br/>
                                <a href="<?=$fn_url?>">указать</a>
                                <?php endif; ?>
                            <?php
                                endif;
                            ?>
                        </span>
                        
                    </div>   
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div data-reserves-payout-wait-screen="true" class="b-layout__wait b-layout__txt_fontsize_15 b-layout__txt_color_6db335 b-layout_hide">
                <span data-reserves-payout-wait-msg="true"></span>
                <div class="b-layout__txt b-layout__txt_center b-layout__txt_padtb_10">
                    <img width="80" height="20" src="/images/Green_timer.gif">
                </div>
            </div>
<?php
            endif;
?>
        </div>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>