<?php

/**
 * Попап при заказе ТУ для заказчика c интерфейсом резерва по "новой БС"
 */

$title = reformat($title, 30, 0, 1);
$days = $days . ' ' . ending($days, 'день', 'дня', 'дней');
$priceFormated = tservices_helper::cost_format($price,true, false, false);
$priceWithTaxFormated = tservices_helper::cost_format($priceWithTax,true, false, false);

$show_popup = (isset($_POST['popup']));

?>
<script type="text/javascript">
    var RESERVE_ALL_TAX = <?=$reserveAllTaxJSON?>;
</script>
<div id="tservices_orders_status_popup" class="b-shadow b-shadow_center b-shadow_width_520 <?php if(!$show_popup){ ?>b-shadow_hide <?php } ?>b-shadow__quick" style="display:block;">
    <div class="b-shadow__body b-shadow__body_pad_20">
        <h2 class="b-layout__title">
            Заказ услуги
        </h2>
        <div class="b-layout__txt b-layout__txt_padbot_20">
            Для заказа услуги вам необходимо выбрать способ оплаты работы (с резервированием суммы или без него).
        </div>
        <div class="b-layout b-layout_padleft_15">
            <div class="b-layout__txt b-layout__txt_padbot_20">
                Исполнитель <b><?=$frl_fullname?></b><br/>
                Услуга &laquo;<b><?=$title?></b>&raquo; за <b><span class="__tservice_days"><?=$days?></span></b><br/>
                Сумма оплаты <b>
                    <span class="__tservice_price3"><?=$priceWithTaxFormated?></span>
                    <span class="__tservice_price2" style="display: none"><?=$priceFormated?></span>
                </b> 
                <span class="__tservice_paytype"> (с учетом <strong><span class="__tservice_reserve_tax"><?=$reserveTax?></span>%</strong> комиссии сервису)</span>
            </div>
            <div class="b-radio b-radio_layout_vertical">
                <div class="b-radio__item b-radio__item_padbot_10">
                    <input data-hide-class=".__tservice_price2" data-show-class=".__tservice_paytype,.__tservice_price3" data-show-display="inline" tabindex="4" checked="checked" type="radio" value="1" name="paytype" class="b-radio__input" id="paytype1"/>
                    <label for="paytype1" class="b-radio__label b-radio__label_fontsize_13 b-radio__label_bold b-radio__label_margtop_-1">
                        Безопасная сделка (с резервированием бюджета) &#160;<a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" target="_blank"><span class="b-shadow__icon b-shadow__icon_quest2 b-icon_top_2"></span></a>
                    </label>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        Безопасное сотрудничество с гарантией возврата средств. Вы резервируете бюджет заказа на сайте FL.ru - а мы гарантируем вам возврат суммы, если работа будет выполнена Исполнителем некачественно или не в срок.
                    </div>
                </div>
                <div class="b-radio__item">
                    <input data-hide-class=".__tservice_paytype,.__tservice_price3" data-show-class=".__tservice_price2" data-show-display="inline" tabindex="5" type="radio" value="0" name="paytype" class="b-radio__input" id="paytype0">
                    <label for="paytype0" class="b-radio__label b-radio__label_fontsize_13 b-radio__label_bold b-radio__label_margtop_-1">
                        Прямая оплата Исполнителю на его кошелек/счет
                    </label>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        Сотрудничество без участия сайта в процессе оплаты. Вы сами договариваетесь с Исполнителем о способе и порядке оплаты. И самостоятельно регулируете все претензии, связанные с качеством и сроками выполнения работы.
                    </div>
                </div>
            </div>
            <div class="b-buttons b-buttons_padtop_20">
                <a href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="yaCounter6051055.reachGoal('zakaz_tu'); TServices.onSendToCbr(this, '__form_tservice');">
                    <span class="__tservices_orders_feedback_submit_label">Создать заказ и перейти в него</span>
                </a>
                <span class="b-layout__txt b-layout__txt_fontsize_11">&#160; или 
                    <a class="b-layout__link" href="javascript:void(0);" onclick="TServices.closePopup();">отменить заказ</a>
                </span>
            </div>
        </div>
   </div>    
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>