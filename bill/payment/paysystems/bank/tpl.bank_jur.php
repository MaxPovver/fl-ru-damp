<h2 class="b-layout__title b-layout__title_padbot_30">Оплата через банковский перевод <span class="b-layout__bold">Б-<?=$bill->acc['id']?>-<?=($bill->pm->billNum+1)?></span></h2>

<form id="<?= $type_payment ?>" name="<?= $type_payment ?>" method="POST" action="<?= "/bill/payment/?type={$type_payment}"?>">
    <input type="hidden" name="action" value="payment"/>
    <input type="hidden" name="sum" value="<?= $payment_sum; ?>"/>
    
    <table class="b-layout__table">
        <tbody>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Название организации</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input  <?= $bill->error['org_name'] ? "b-combo__input_error" : ""?>">
                            <input class="b-combo__input-text js-payform_input" name="org_name"  id="org_name" type="text" size="80" value="<?= stripslashes($bill->pm->org_name); ?>">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Телефон</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input   <?= $bill->error['phone'] ? "b-combo__input_error" : ""?>">
                            <input class="b-combo__input-text js-payform_input" name="phone" type="text" size="80" value="<?=stripslashes(($bill->pm->phone))?>">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Электронная почта</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input   <?= $bill->error['email'] ? "b-combo__input_error" : ""?>">
                            <input class="b-combo__input-text js-payform_input" name="email"  id="email"  type="text" size="80" value="<?=stripslashes(($bill->pm->email))?>">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Страна</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input  <?= $bill->error['country_id'] ? "b-combo__input_error" : ""?> b-combo__input_multi_dropdown  b-combo__input_width_270 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_on_load_request_id_getrelevantcountries all_value_id_0_0_Все+страны exclude_value_1_0 drop_down_default_<?= $bill->pm->country_id ? $bill->pm->country_id : 0?> multi_drop_down_default_column_0">
                            <input class="b-combo__input-text  js-payform_input <?= $bill->pm->country_id ? '' : 'b-combo__input-text_color_67' ?>" name="country" id="country" onchange="loadCities()" type="text" size="80" value="<?= $bill->pm->country ? $bill->pm->country : "Выберите из списка"?>"><span class="b-combo__arrow"></span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Город</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input  <?= $bill->error['city_id'] ? "b-combo__input_error" : ""?> b-combo__input_multi_dropdown  b-combo__input_width_270 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_max-width_450 all_value_id_0_0_Все+города drop_down_default_<?= $bill->pm->city_id ? $bill->pm->city_id : 0?> multi_drop_down_default_column_0 <?= $country_id > 0 ? "b-combo__input_on_load_request_id_getcitiesbyid?id=".$bill->pm->country : "" ?>">
                            <input class="b-combo__input-text  js-payform_input <?= $bill->pm->city_id ? '' : 'b-combo__input-text_color_67' ?>" name="city" id="city" type="text" size="80" value="<?= $bill->pm->city ? $bill->pm->city: "Выберите из списка"?>"><span class="b-combo__arrow"></span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Почтовый индекс</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input  <?= $bill->error['index'] ? "b-combo__input_error" : ""?>">
                            <input class="b-combo__input-text  js-payform_input" name="index" id="index" type="text" size="80" value="<?=stripslashes(($bill->pm->index))?>">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Почтовый адрес</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input  <?= $bill->error['address'] ? "b-combo__input_error" : ""?>">
                            <input class="b-combo__input-text  js-payform_input" name="address" id="address" type="text" size="80" value="<?=stripslashes(($bill->pm->address))?>" >
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">ИНН</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input  <?= $bill->error['inn'] ? "b-combo__input_error" : ""?>">
                            <input class="b-combo__input-text  js-payform_input" name="inn"  id="inn" type="text" size="80" value="<?=stripslashes(($bill->pm->inn))?>">
                        </div>
                    </div>
                    <? if ($bill->pm->country_id != 1) { ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_3">Укажите по желанию</div>
                    <? } ?>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Полное название организации</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input  <?= $bill->error['full_name'] ? "b-combo__input_error" : ""?>">
                            <input class="b-combo__input-text  js-payform_input" name="full_name" id="full_name" type="text" size="80" value="<?=stripslashes(($bill->pm->full_name))?>">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Юридический адрес</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input  <?= $bill->error['address_jry'] ? "b-combo__input_error" : ""?>">
                            <input class="b-combo__input-text js-payform_input" name="address_jry" id="address_jry"  type="text" size="80" value="<?=stripslashes(($bill->pm->address_jry))?>">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Расчетный счет</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input ">
                            <input class="b-combo__input-text js-payform_input" name="bank_rs"  id="bank_rs" type="text" size="80" value="<?=stripslashes(($bill->pm->bank_rs))?>">
                        </div>
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_3">Укажите по желанию</div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Название банка</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input ">
                            <input class="b-combo__input-text js-payform_input" name="bank_name" id="bank_name" type="text" size="80" value="<?=stripslashes(($bill->pm->bank_name))?>" >
                        </div>
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_3">Укажите по желанию</div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_20">
                    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3 js-payform_input">Корреспондентский счет</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_padleft_10 b-layout__td_width_240">
                    <div class="b-combo">
                        <div class="b-combo__input ">
                            <input class="b-combo__input-text" name="bank_ks" id="bank_ks" type="text" size="80" value="<?=stripslashes(($bill->pm->bank_ks))?>">
                        </div>
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_3">Укажите по желанию</div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td" colspan="2">
                    <div class="b-fon b-fon_bg_fff9bf b-fon_pad_10 b-fon_padleft_35">
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>Обратите внимание</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_11">— Период зачисления средств — до 7 рабочих дней.<br>— Банковский перевод для юридических лиц и ИП.</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_11">— Минимальная сумма платежа 10 рублей.</div>
                    </div>
                    <? $disabled = ($payment_sum < 10); ?>
                    <? include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.button_buy.php");?>                       
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