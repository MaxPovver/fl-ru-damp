<?php

/**
 * Шаблон виджета TServiceOrderFeedback
 * Popup окошка для отправки отзыва и/или закрытии заказа
 */

$is_reserve = tservices_helper::isOrderReserve($pay_type);

        
?>
<div id="<?=static::getPopupId($idx)?>" 
     data-order-feedback="true" 
     class="b-shadow b-shadow_center b-shadow_block b-shadow_width_<?=($is_reserve)?'580':'520'?> b-shadow_hide b-shadow__quick b-shadow_zindex_110">
  <div class="b-shadow__body b-shadow__body_pad_15_20">
      <h2 class="b-layout__title">
          Завершение сотрудничества
      </h2>
      <div class="b-layout">
          
            <div data-popup-wait-screen="true" class="b-layout__wait b-layout__txt_fontsize_15 b-layout__txt_color_6db335 b-layout_hide">
                <span data-popup-wait-msg="true"></span>
                <div class="b-layout__txt b-layout__txt_center b-layout__txt_padtb_10">
                    <img width="80" height="20" src="<?=WDCPREFIX?>/images/Green_timer.gif">
                </div>
            </div>
          
            <?php if(!$is_close): ?>   
            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_10 __tservices_orders_feedback_label">
                <?php if($is_reserve): ?>
                Пожалуйста, оставьте ваш отзыв о сотрудничестве и подтвердите закрытие заказа с выплатой всей суммы исполнителю.
                <?php else: ?>
                Пожалуйста, оставьте ваш отзыв о сотрудничестве и подтвердите закрытие заказа.
                <?php endif; ?>
            </div>
            <?php endif; ?>   
            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_10">
                Ваш отзыв о сотрудничестве:
            </div>
            <div class="b-layout b-layout_padleft_20">
                <form action="" method="post">
                <input type="hidden" name="oid" value="<?=$idx?>" />
                <input type="hidden" name="hash" value="<?=$hash?>" />
                <div class="b-radio b-radio_layout_horizontal">
                   <div class="b-radio__item b-radio__item_padbot_20 b-radio__item_padright_20">
                       <input<?=($rating >= 0)?' checked':''?> data-validators="fbtype" type="radio" value="1" name="fbtype" class="b-radio__input" id="plus-<?=$idx?>">
                       <label for="plus-<?=$idx?>" class="b-radio__label b-radio__label_fontsize_13 b-radio__label_color_6db335">Положительный</label>
                   </div>
                   <div class="b-radio__item b-radio__item_padbot_20">
                       <input<?=($rating < 0)?' checked':''?> data-validators="fbtype" type="radio" value="-1" name="fbtype" class="b-radio__input" id="minus-<?=$idx?>">
                       <label for="minus-<?=$idx?>" class="b-radio__label b-radio__label_fontsize_13 b-radio__label_color_c10600">Отрицательный</label>
                   </div>
                </div>
                <div class="b-textarea">
                    <textarea data-validators="maxLength:500 <?=($is_close)?'minLength:4 required':''?>" 
                              <?=($is_close)?'data-order-feedback-is-close="true"':''?>
                              class="b-textarea__textarea b-textarea__textarea_italic" 
                              rows="5" cols="80" maxlength="500" name="feedback" 
                              placeholder="Введите текст отзыва"></textarea>
                </div> 
                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">
                    Не более 500 символов.
                </div>      
                <div class="b-buttons b-buttons_padtop_20">
                      <a href="javascript:void(0);" 
                         data-order-feedback-submit="true" 
                         class="b-button b-button_flat b-button_flat_green" 
                         onclick="">
                          <?php if($is_reserve): ?>
                             <?php if($is_close): ?>
                             Оставить отзыв
                             <?php else: ?>
                             Закрыть заказ
                             <?php endif; ?>
                          <?php else: ?>
                          <span class="__tservices_orders_feedback_submit_label">
                              <?php if($is_close): ?>
                              Оставить отзыв
                              <?php else: ?>
                              Закрыть заказ
                              <?php endif; ?>
                          </span>                 
                          <?php endif; ?>
                      </a>
                      <?php if(false): ?>
                      <span class="b-layout__txt b-layout__txt_fontsize_11">&#160; или 
                          <a class="b-layout__link" 
                             data-order-feedback-close="true" 
                             href="javascript:void(0);" 
                             onclick="">продолжить сотрудничество</a>
                      </span>
                      <?php endif; ?>
                </div>
                </form>
                <?php if($is_reserve && !$is_close): ?>        
                 <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_10">
                     Нажатием кнопки "Закрыть заказ" вы подтверждаете отсутствие претензий к выполненной работе и даете согласие на выплату исполнителю ранее зарезервированной суммы.
                 </div>
                 <?php endif; ?> 
            </div>
        </div>
   </div>    
   <span data-popup-close="true" class="b-shadow__icon b-shadow__icon_close"></span>
</div>