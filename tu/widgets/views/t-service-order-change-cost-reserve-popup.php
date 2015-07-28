<?php

/**
* Шаблон окошка редактирования бюджета и сроков заказа ТУ c учетом возможности выбрать тип оплаты
*/

$is_show = __paramInit('bool','tu_edit_budjet','tu_edit_budjet',false);
$is_paytype = __paramInit('bool','paytype','paytype',false);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);

if ($is_paytype) {
    $order['pay_type'] = TServiceOrderModel::PAYTYPE_RESERVE;
}

?>
<div id="tu_edit_budjet" class="b-shadow<?php if(!$is_show): ?> b-shadow_hide<?php endif; ?> b-shadow_pad_20 b-shadow_center b-shadow_width_500 b-shadow_zindex_4">
    <h2 class="b-layout__txt b-layout__txt_fontsize_18">
        Изменение заказа
    </h2>
    <div class="b-layout__txt b-layout__txt_padbot_20">Заказ "<span class="b-layout__bold"><?= $title ?></span>"</div>
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
                        <input tabindex="1" class="b-combo__input-text" id="tu_edit_budjet_price" type="text" size="80" value="<?= $order['order_price'] ?>" />
                    </div>
                </div>
            </td>
            <td class="b-layout__td b-layout__td_padbot_10 b-layout__td_padleft_10">
                <div class="b-layout__txt b-layout__txt_padtop_5">
                    руб.
                </div>
            </td>
            <td class="b-layout__td">
                <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_fontsize_11">
                    Не менее 300 руб.
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
                        <input tabindex="2" class="b-combo__input-text" id="tu_edit_budjet_days" type="text" size="80" value="<?= $order['order_days'] ?>" />
                    </div>
                </div>
            </td>
            <td class="b-layout__td b-layout__td_padbot_10 b-layout__td_padleft_10">
                <div class="b-layout__txt b-layout__txt_padtop_5">
                    дн.
                </div>
            </td>
            <td class="b-layout__td">
                <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_fontsize_11">
                    Не менее 1 дня.
                </div>
            </td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__td" colspan="4">
                <div class="b-radio b-radio_layout_vertical b-radio_padtop_20">
                    <div class="b-radio__item b-radio__item_padbot_10">
                        <input tabindex="3"<?php if($order['pay_type'] == TServiceOrderModel::PAYTYPE_RESERVE): ?> checked="checked"<?php endif; ?> type="radio" value="1" name="paytype" class="b-radio__input" id="paytype1"/>
                        <label for="paytype1" class="b-radio__label b-radio__label_bold b-radio__label_fontsize_13 b-radio__label_margtop_-1">
                            Безопасная сделка (с резервированием бюджета) &#160;<a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" target="_blank"><span class="b-shadow__icon b-shadow__icon_quest2 b-icon_top_2"></span></a>
                        </label>
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                            Безопасное сотрудничество с гарантией возврата средств. Вы резервируете бюджет заказа на сайте FL.ru - а мы гарантируем вам возврат суммы, если работа будет выполнена Исполнителем некачественно или не в срок.
                        </div>
                    </div>
                    <div class="b-radio__item b-radio__item_padbot_20">
                        <input tabindex="4"<?php if($order['pay_type'] == TServiceOrderModel::PAYTYPE_DEFAULT): ?> checked="checked"<?php endif; ?> type="radio" value="0" name="paytype" class="b-radio__input" id="paytype0">
                        <label for="paytype0" class="b-radio__label b-radio__label_bold b-radio__label_fontsize_13 b-radio__label_margtop_-1">
                            Прямая оплата Исполнителю на его кошелек/счет
                        </label>
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                            Сотрудничество без участия сайта в процессе оплаты. Вы сами договариваетесь с Исполнителем о способе и порядке оплаты. И самостоятельно регулируете все претензии, связанные с качеством и сроками выполнения работы.
                        </div>
                    </div>
                </div>
            </td>
        </tr>        
    </table>
    <div class="b-buttons b-buttons_padleft_20">
        <a class="b-button b-button_flat b-button_flat_green" 
           onclick="yaCounter6051055.reachGoal('zakaz_change_ok');TServices_Order.changePriceAndDays(<?= $order['id'] ?>);" 
           href="javascript:void(0);">
            Сохранить
        </a>
        &#160;&#160;
        <span class="b-buttons__txt">или 
            <a data-popup-ok="true" class="b-layout__link b-layout__link_bordbot_dot_ee1d16" href="javascript:void(0);">
                закрыть без изменений
            </a>
        </span>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>