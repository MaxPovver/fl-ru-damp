<?php

/**
* Шаблон окошка редактирования бюджета и сроков заказа ТУ
*/

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);

?>
<div id="tu_edit_budjet" class="b-shadow b-shadow_hide b-shadow_pad_20 b-shadow_center b-shadow_width_320 b-shadow_zindex_4">
    <h2 class="b-layout__txt b-layout__txt_fontsize_18">
        Редактирование бюджетов и сроков
    </h2>
    <div class="b-layout__txt b-layout__txt_padbot_10">Заказ "<span class="b-layout__bold"><?= $title ?></span>"</div>
    <table class="b-layout__table b-layout__table_width_full">
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_10">
                <div class="b-layout__txt b-layout__txt_padtop_5">
                    Бюджет
                </div>
            </td>
            <td class="b-layout__td b-layout__td_width_100 b-layout__td_padbot_10">
                <div class="b-combo">
                    <div class="b-combo__input">
                        <input class="b-combo__input-text" id="tu_edit_budjet_price" type="text" size="80" value="<?= $order['order_price'] ?>" />
                    </div>
                </div>
            </td>
            <td class="b-layout__td b-layout__td_padbot_10 b-layout__td_padleft_10">
                <div class="b-layout__txt b-layout__txt_padtop_5">
                    рублей
                </div>
            </td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_10">
                <div class="b-layout__txt b-layout__txt_padtop_5">
                    Срок
                </div>
            </td>
            <td class="b-layout__td b-layout__td_width_100 b-layout__td_padbot_10">
                <div class="b-combo">
                    <div class="b-combo__input">
                        <input class="b-combo__input-text" id="tu_edit_budjet_days" type="text" size="80" value="<?= $order['order_days'] ?>" />
                    </div>
                </div>
            </td>
            <td class="b-layout__td b-layout__td_padbot_10 b-layout__td_padleft_10">
                <div class="b-layout__txt b-layout__txt_padtop_5">
                    дней
                </div>
            </td>
        </tr>
    </table>
    <div class="b-layout__txt b-layout__txt_fontsize_11">Минимальный бюджет заказа &mdash; 300 рублей.<br>Минимальный срок выполнения &mdash; 1 день.</div>
    <div class="b-buttons b-buttons_padtop_10">
        <a class="b-button b-button_flat b-button_flat_green " onclick="yaCounter6051055.reachGoal('zakaz_change_ok');TServices_Order.changePriceAndDays(<?= $order['id'] ?>);" href="javascript:void(0);">Сохранить</a>
        &#160;&#160;<span class="b-buttons__txt">или <a data-popup-ok="true" class="b-layout__link b-layout__link_bordbot_dot_ee1d16" href="javascript:void(0);">закрыть без изменений</a></span>
    </div>
</div>