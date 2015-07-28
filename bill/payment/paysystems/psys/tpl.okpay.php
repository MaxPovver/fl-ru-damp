<h2 class="b-layout__title b-layout__title_padbot_30">Оплата через Okpay</h2>

<table class="b-layout__table b-layout__table_width_full">
    <tbody>
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_padright_20">
                <form method="POST" action="<?= ( is_release() ? "https://www.okpay.com/process.html" : "/bill/test/okpay.php" )?>" id="<?= $type_payment ?>" name="<?= $type_payment ?>">
                    <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5 b-page__desktop b-page__ipad">Мобильный телефон</div>
                    <div class="b-combo b-combo_inline-block b-combo_padbot_20">
                        <div class="b-combo__input <?= $bill->error['phone'] ? "b-combo__input_error" : ""?> b-combo__input_width_170 b-combo__input_tel
                               b-combo__input_phone_countries_dropdown b-combo__input_visible_items_5 use_scroll show_all_records
                                                    b-combo__input_init_countryPhoneCodes">
                            <input type="text" maxlength="15" id="reg_phone" class="b-combo__input-text payment-system js-payform_input" name="phone" size="80" value="7">
                            <span class="b-combo__tel"><span class="b-combo__flag" style="background-position:0 -660px"></span></span> 
                        </div>
                    </div>
                    
                    <div class="i-shadow">
                        <div id="error_phone" class="b-shadow b-shadow_zindex_3 b-shadow_m <?= $bill->error['phone'] ? "" : "b-shadow_hide"?>" style="top:-50px !important; left:308px;">
                            <div class="b-shadow__right">
                                <div class="b-shadow__left">
                                    <div class="b-shadow__top">
                                        <div class="b-shadow__bottom">
                                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                                <div id="error_txt_phone" class="b-layout__txt b-layout__txt_padright_15 b-layout__txt_color_c4271f" style="width:200px"><span class="b-form__error"></span><?= $bill->error['phone']?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12"></span>
                            <span class="b-shadow__icon b-shadow__icon_nosik-left b-shadow__icon_top_20 b-shadow__icon_left_5"></span>
                        </div>                                
                    </div>
                    
                    <input type="hidden" name="ok_receiver" value="<?=OKPAY_WALLETID?>"/>
                    <input type="hidden" name="ok_currency" value="RUB"/>
                    <input type="hidden" name="ok_item_1_type" value="digital"/>
                    <input type="hidden" name="ok_item_1_price" value="<?= $payment_sum ?>"/>
                    <input type="hidden" name="ok_item_1_name" value="<?= (iconv('CP1251', 'UTF-8', "Оплата за услуги сайта www.free-lance.ru, в том числе НДС - 18%. Счет #" . $bill->acc['id'] . ", логин " . $bill->user['login'])) ?>"/>
                    <input type="hidden" name="ok_fees" value="1"/>
                    <input type="hidden" name="ok_return_success" value="<?=$host?>/bill/"/>
                    <input type="hidden" name="ok_ipn" value="<?=$host?>/income/okpay.php"/>
                    <input type="hidden" name="ok_return_fail" value="<?=$host?>/bill"/>
                    <input type="hidden" name="ok_f_bill_id" value="<?= $bill->acc['id'] ?>">
                    <input type="hidden" name="ok_f_uid" value="<?= get_uid(false) ?>">


                </form>
                <div class="b-fon b-fon_bg_fff9bf b-fon_pad_10 b-fon_padleft_35">
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>Обратите внимание</div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11">— Оплата услуг осуществляется в течение 2-3 минут.</div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11">— После нажатия на кнопку «Оплатить» вы будете перенаправлены на сайт Okpay.</div>
                </div>
                <? $checked = "checkOKPAYFields";?>
                <? include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.button_buy.php");?>                
            </td>
            <td class="b-layout__td b-layout__td_padleft_30 b-layout__td_width_50ps">
            </td>
        </tr>
    </tbody>
</table>
<script>
    $("<?= $type_payment ?>").getElements('input, textarea').addEvent('focus', function() {
        $$('a[data-system=psys_systems]').fireEvent('click');
    });
</script>