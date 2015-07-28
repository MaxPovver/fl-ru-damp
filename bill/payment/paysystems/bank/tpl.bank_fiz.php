<h2 class="b-layout__title b-layout__title_padbot_30">Оплата через банк для физических лиц</h2>

<form id="<?= $type_payment ?>" name="<?= $type_payment ?>" method="POST" action="<?= "/bill/payment/?type={$type_payment}"?>">
    <input type="hidden" name="action" value="payment"/>
    <input type="hidden" name="id" value="<?= $bill->pm->id?>"/>
    <input type="hidden" name="bc" value="<?= $bill->pm->bank_code?>"/>
    <input type="hidden" name="sum" value="<?= $payment_sum; ?>"/>

    <table class="b-layout__table b-layout__table_width_full">
        <tbody>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padright_20">

                    <table class="b-layout__table b-layout__table_width_full">
                        <tbody>
                            <tr class="b-layout__tr">
                                <td class="b-layout__td b-layout__td_padbot_20"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">ФИО</div></td>
                                <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padbot_20">
                                    <div class="b-combo">
                                        <div class="b-combo__input <?= $bill->error['fio'] ? "b-combo__input_error" : ""?>">
                                            <input type="text" id="fio" name="fio"  class="b-combo__input-text js-payform_input" size="80" value="<?= stripcslashes($bill->pm->fio)?>">
                                        </div>
                                    </div>                        
                                </td>
                            </tr>
                            <tr class="b-layout__tr">
                                <td class="b-layout__td b-layout__td_padbot_20"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Адрес плательщика</div></td>
                                <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padbot_20">
                                    <div class="b-textarea <?= $bill->error['address'] ? "b-textarea_error" : ""?>">
                                        <textarea rows="5" cols="80" id="address" name="address" class="b-textarea__textarea js-payform_input"><?=stripcslashes($bill->pm->address)?></textarea>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="b-fon b-fon_bg_fff9bf b-fon_pad_10 b-fon_padleft_35">
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>Обратите внимание</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_11">— Период зачисления средств — до 7 рабочих дней.<br>— Банковский перевод для физических лиц.</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_11">— Минимальная сумма платежа 10 рублей.</div>
                    </div>
                    <? 
                    $checked  = "checkBankFizFields";
                    $disabled = ($payed_sum < 10); 
                    ?>
                    <? include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.button_buy.php");?>                   
                </td>
                <td class="b-layout__td b-layout__td_padleft_30 b-layout__td_bordleft_e6 b-layout__td_width_50ps">
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_15">После нажатия кнопки &laquo;Оплатить&raquo; вам будет сформирована квитанция, оплатить которую вы можете в любом банке, расположенном на территории Российской Федерации.</div>

                </td>
            </tr>
        </tbody>
    </table>
</form>
<script>
    $("<?= $type_payment ?>").getElements('input, textarea').addEvent('focus', function() {
        $$('a[data-system=bank_systems]').fireEvent('click');
    });
</script>