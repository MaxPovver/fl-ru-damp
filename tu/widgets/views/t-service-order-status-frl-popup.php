<?php

/**
 *  Ўаблок окошка подтверждени€ заказа дл€ фрилансера
 *  учитываетс€ вариант с резервированием средств
 */

$title = reformat(htmlspecialchars($title), 30, 0, 1);
$price = tservices_helper::cost_format($price, true, false, false);
$days = $days . ' ' . ending($days, 'день', 'дн€', 'дней');
$accept_url = tservices_helper::getOrderStatusUrl($idx, 'accept');
$is_reserve = tservices_helper::isOrderReserve($pay_type);
$tax = $tax*100;

?>
<div id="tservices_orders_status_popup_<?=$idx?>" class="b-shadow b-shadow_center b-shadow_width_580 b-shadow_hide b-shadow__quick __tservices_orders_status_popup_hide" style="display:block;">
    <div class="b-shadow__body b-shadow__body_pad_20">
        <h2 class="b-layout__title">
            ѕодтверждение заказа
        </h2>
        <div class="b-layout__txt b-layout__txt_padbot_10">
            <b><?=$title?></b>
            <br/><br/>
            —умма заказа: <b><?=$price?></b><br/>
            —рок выполнени€: <b><?=$days?></b>
            <?php if($tax > 0): ?>
            <br/><br/>
                <?php if($is_reserve):?>
                    <b>ќбратите внимание:</b> при завершении сотрудничества с вашего личного 
                    счета на сайте будет списана комисси€ (<?=$tax?>% от выплаченной вам суммы). 
                <?php else: ?>
                    <b>ќбратите внимание:</b> при успешном завершении сотрудничества и закрытии 
                    заказа с вашего личного счета на сайте будет списана комисси€ (<?=$tax?>% от суммы в заказе).
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="b-buttons b-buttons_padtop_20">
            <a href="<?=$accept_url?>" class="b-button b-button_flat b-button_flat_green">
                <span class="__tservices_orders_feedback_submit_label">
                    ѕодтвердить заказ
                </span>
            </a>
            <span class="b-layout__txt b-layout__txt_fontsize_11">&#160; или 
                <a class="b-layout__link" href="javascript:void(0);" onclick="TServices_Order.closeAcceptPopup(<?=$idx?>);">не подтверждать</a>
            </span>
        </div>
<?php if($is_reserve): ?>        
        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_13 b-layout__txt_padtop_10">
            Ќажима€ кнопку "ѕодтвердить заказ", € принимаю услови€ <a href="http://st.fl.ru/about/documents/reserve_offer_contract.pdf">ƒоговора</a> 
            и соглашаюсь на сотрудничество в его рамках.
        </div>
<?php endif; ?>        
   </div>    
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>