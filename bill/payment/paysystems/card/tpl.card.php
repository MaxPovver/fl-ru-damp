<h2 class="b-layout__title b-layout__title_padbot_30">Оплата через пластиковую карту</h2>
<?php if(!$bill->card_merchant) {?>
<form id="<?= $type_payment ?>" name="<?= $type_payment ?>" action="<?= is_release() ? cardpay::URL_ORDER : "/bill/test/card.php"?>" accept-charset="UTF-8" method="post">
    <input type="hidden" name="Merchant_ID" value="<?= cardpay::MERCHANT_ID?>" />
    <input type="hidden" name="OrderNumber" value="<?= $bill->pm->order_id?>" />
    <input type="hidden" name="OrderAmount" id="ammount" value="<?= $payment_sum; ?>" />
    <input type="hidden" name="OrderCurrency" value="RUR" />
    <input type="hidden" name="OrderComment" value="Пополнение счета № <?= $bill->acc['id']?>" />
    <input type="hidden" name="TestMode" value="<?= cardpay::TESTMODE?>" />
    <input name="ieutf" type="hidden" value="&#9760;" />
<?php } else {//if?>
<form id="<?= $type_payment ?>" name="<?= $type_payment ?>" action="<?= is_release() ? $bill->pm->getRedirectUrl($bill->pm->order_id, $payment_sum) : "/bill/test/card.php"?>" accept-charset="UTF-8" method="post">  
    <input type="hidden" name="OrderAmount" id="ammount" value="<?= $payment_sum; ?>" />
    <input type="hidden" name="OrderNumber" value="<?= $bill->pm->order_id?>" />
<?php } //else?>
    <table class="b-layout__table">
        <tbody>
            <?php if(!$bill->card_merchant) {?>

            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Имя</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10">
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_width_280">
                            <input class="b-combo__input-text" name="FirstName" id="FirstName" type="text" size="80" value="<?= $bill->user['uname']?>">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Фамилия</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10">
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_width_280">
                            <input class="b-combo__input-text" name="LastName" id="LastName" type="text" size="80" value="<?= $bill->user['usurname']?>">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Электронная почта</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10">
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_width_280">
                            <input class="b-combo__input-text" name="Email" id="Email" type="text" size="80" value="<?= $bill->user['email']?>">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Город</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10">
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_width_280">
                            <input class="b-combo__input-text" name="City" id="City" type="text" size="80" value="<?= $bill->user['city'] ? city::getCityName($bill->user['city']) : "";?>">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Адрес</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10">
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_width_280">
                            <input class="b-combo__input-text" name="Address" id="Address" type="text" size="80" value="">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Телефон</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10">
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_width_280">
                            <input class="b-combo__input-text" name="Phone" id="Phone" type="text" size="80" value="<?= $bill->pm->reqv[sbr::FT_PHYS]['mob_phone']?>">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td" colspan="2">
                    <div class="b-fon b-fon_bg_fff9bf b-fon_pad_10 b-fon_padleft_35">
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>Обратите внимание</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_11">— Обычно деньги зачисляются сразу. В единичных случаях платеж поступает в срок до 7 рабочих дней.<br>— Подробнее <a class="b-layout__link" href="http://assist.ru/about/security.htm">о безопасности платежей</a>.</div>
                    </div>
                    <? $checked = "checkCardFields";?>
                    <? include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.button_buy.php");?>                       
                </td>
            </tr>
            <?php } else { //if?>

            <tr class="b-layout__tr">
                <td class="b-layout__td" colspan="2">
                    <div class="b-fon b-fon_bg_fff9bf b-fon_pad_10 b-fon_padleft_35">
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>Обратите внимание</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_11">— Обычно деньги зачисляются сразу. В единичных случаях платеж поступает в срок до 7 рабочих дней.</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_11">— Минимальная сумма платежа 10 рублей.</div>
                    </div>
                    <? $disabled = ($payment_sum < 10); ?>
                    <? include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.button_buy.php");?>                       
                </td>
            </tr>
            <?php }?>
        </tbody>
    </table>
</form>
<script>
    $("<?= $type_payment ?>").getElements('input, textarea').addEvent('focus', function() {
        $$('a[data-system=card_systems]').fireEvent('click');
    });
</script>