<?php

/**
 * Попап при заказе ТУ для анонимуса c интерфейсом резерва по "новой БС"
 */

$title = reformat($title, 30, 0, 1);
$price = tservices_helper::cost_format($price,true, false, false);

$show_popup = (isset($_POST['popup']));

?>
<div class="b-shadow b-shadow_center b-shadow_width_520 <?php if(!$show_popup){ ?>b-shadow_hide <?php } ?>b-shadow__quick b-shadow_overflow_visible"  id="tesrvices_order_auth_popup" style="display:block;">
  <div class="b-shadow__body b-shadow__body_pad_20">
    <h2 class="b-layout__title">
        Заказ услуги
    </h2>
    <div class="b-layout__txt b-layout__txt_padbot_20">
        Для заказа услуги "<?=$title?>", вам необходимо зарегистрироваться (указав ФИО и e-mail и способ оплаты) или авторизоваться.
    </div>
    <div class="b-layout b-layout_padleft_15">
        <table class="b-layout__table">
            <tbody>
                <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_10 b-layout__td_width_null_iphone">
                        <div class="b-layout__txt b-layout__txt_padtop_4 b-page__desktop b-page__ipad">Имя</div>
                    </td>
                    <td class="b-layout__td b-layout__td_padbot_10">
                        <div class="b-layout__txt b-layout__txt_padbot_5 b-page__iphone">Имя</div>
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_260">
                                <input tabindex="1" class="b-combo__input-text b-combo__input-text_italic" type="text" placeholder="Ваше имя, не более 21 символа" size="21" maxlength="21" id="reg_name">
                            </div>
                        </div>         
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_10 b-layout__td_width_null_iphone">
                        <div class="b-layout__txt b-layout__txt_padtop_4 b-page__desktop b-page__ipad">Фамилия</div>
                    </td>
                    <td class="b-layout__td b-layout__td_padbot_10">
                        <div class="b-layout__txt b-layout__txt_padbot_5 b-page__iphone">Фамилия</div>
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_260">
                                <input tabindex="2" class="b-combo__input-text b-combo__input-text_italic" type="text" placeholder="Ваша фамилия, не более 21 символа" size="21" maxlength="21" id="reg_surname">
                            </div>
                        </div>         
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_20 b-layout__td_width_null_iphone">
                        <div class="b-layout__txt b-layout__txt_padtop_4 b-page__desktop b-page__ipad">E-mail</div>
                    </td>
                    <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_270">
                        <div class="b-layout__txt b-layout__txt_padbot_5 b-page__iphone">E-mail</div>
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_260">
                                <input tabindex="3" class="b-combo__input-text b-combo__input-text_italic" type="text" onblur="TServices_Order_Auth.checkEmail(1);" onkeyup="TServices_Order_Auth.checkEmail(0);" placeholder="Введите ваш e-mail" size="80" id="reg_email" name="email">
                            </div>
                        </div>         
                    </td>
                    <td class="b-layout__td">
                        <div class="i-shadow">
                            <div style="z-index: 40" class="b-shadow b-shadow_m b-shadow_top_0 b-shadow_hide" id="error_email">
                                <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                    <div class="b-layout__txt b-layout__txt_nowrap b-layout__txt_padright_15 b-layout__txt_color_c4271f" id="error_txt_email"><span class="b-form__error"></span></div>
                                </div>
                                <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12"></span>
                                <span class="b-shadow__icon b-shadow__icon_nosik-left b-shadow__icon_top_10 b-shadow__icon_left_-4"></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__td" colspan="3">
                        <div class="b-txt b-txt_padbot_20">
                            Бюджет <strong><span class="__tservice_price2"><?=$price?></span></strong> 
                            <span class="__tservice_paytype"><strong>+ 10%</strong> комиссии сервису</span>
                        </div>
                        <div class="b-radio b-radio_layout_vertical">
                            <div class="b-radio__item b-radio__item_padbot_10">
                                <input data-show-class=".__tservice_paytype" data-show-display="inline" tabindex="4" checked="checked" type="radio" value="1" name="paytype" class="b-radio__input" id="paytype1"/>
                                <label for="paytype1" class="b-radio__label b-radio__label_bold b-radio__label_fontsize_13 b-radio__label_margtop_-1">
                                    Безопасная сделка (с резервированием бюджета) &#160;<a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" target="_blank"><span class="b-shadow__icon b-shadow__icon_quest2 b-icon_top_2"></span></a>
                                </label>
                                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                                    Безопасное сотрудничество с гарантией возврата средств. Вы резервируете бюджет заказа на сайте FL.ru - а мы гарантируем вам возврат суммы, если работа будет выполнена Исполнителем некачественно или не в срок.
                                </div>
                            </div>
                            <div class="b-radio__item b-radio__item_padbot_20">
                                <input data-hide-class=".__tservice_paytype" tabindex="5" type="radio" value="0" name="paytype" class="b-radio__input" id="paytype0">
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
            </tbody>
        </table>
        <div class="b-buttons b-buttons_padleft_null_iphone">
            <a href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="TServices_Order_Auth.submitForm(); return false;">Зарегистрироваться<span class="b-page__ipad b-page__desktop"> и заказать услугу</span></a>
            <span class="b-layout__txt b-layout__txt_fontsize_11">&nbsp;<span class="b-page__iphone"> и заказать услугу</span> или <a class="b-layout__link" href="/registration/?type=empl&user_action=tu">авторизоваться</a></span>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_10">
                После нажатия на кнопку "Зарегистрироваться и заказать услугу" на указанный адрес будет отправлено письмо со ссылкой. 
                Перейдя по ней, вы подтвердите регистрацию и заказ услуги.
            </div>
        </div>
    </div>
  </div>
  <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>