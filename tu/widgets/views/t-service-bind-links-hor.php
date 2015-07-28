<div class="b-pay-tu__hider">
    <table class="b-layout__table b-layout__table_width_full">
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_width_100 b-layout__td_width_null_iphone"></td>
            <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_pad_null_iphone">
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15">
                    Услуга закреплена до <?=$date_stop?>
                </div>
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_6db335 b-layout__txt_fontsize_15 b-layout__txt_padbot_10">
                    Продлите закрепление на 7 или более дней
                    <?php if ($allow_up): ?>
                        или поднимите услугу на 1 место за <?= view_cost_format($bind_up_price, true, false, false) ?>
                    <?php endif; ?>
                </div>
                <a class="b-button b-button_flat b-button_flat_green" href="#"
                   data-popup="<?= quickPaymentPopupTservicebind::getInstance()->getPopupId($tservice_id) ?>">Продлить</a>
                <?php if ($allow_up): ?><a class="b-button b-button_flat b-button_flat_green" href="#"
                       data-popup="<?= quickPaymentPopupTservicebindup::getInstance()->getPopupId($tservice_id) ?>">Поднять</a><?php endif; ?>
            </td>
        </tr>
    </table>
</div>