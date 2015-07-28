<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/remind.common.php");
$xajax->printJavascript('/xajax/');


$captchanum = uniqid('',true);

if ($_SESSION) {
    foreach ($_SESSION as $k => $v) { 
        if (strpos("image_number", $k) === 0) {
            unset($_SESSION[$k]); 
        }
    }
}

$captcha = new captcha($captchanum);
$captcha->setNumber();
$captcha2 = new captcha('2_'.$captchanum);
$captcha2->setNumber();

?>

<div class="b-layout b-layout_padtop_45">
    
    <div class="g-txt_center" id="email_remind">

        <h1 class="b-layout__title b-layout__title_lh_1 b-layout__title_fs24 b-layout__title_color_333 b-layout__title_padbot_25">
            Для восстановления доступа к аккаунту заполните форму ниже
        </h1>

        <div class="b-layout b-layout_inline-block b-layout_width_330 b-layout_width_full_iphone">
            <table class="b-layout__table b-layout__table_width_full">
                <tbody>
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_relative b-layout__td_width_full_ipad">
                            <div class="b-combo b-combo_large">
                                <div class="b-combo__input">
                                    <input data-validators="required" 
                                           type="text" 
                                           size="80" 
                                           id="remind_email" 
                                           name="remind_email" 
                                           class="b-combo__input-text"
                                           placeholder="Email, логин или телефон"/>
                                    <label class="b-combo__label" for="remind_email"></label>
                                </div>
                            </div>

                            <div class="b-layout__txt 
                                 b-layout__txt_left 
                                 b-layout__txt_float_left 
                                 b-layout__txt_padtop_5 
                                 b-layout__txt_color_c10600 
                                 b-layout__txt_error 
                                 b-layout__txt_error_right_desktop 
                                 b-shadow_hide"
                                 id="remind_email_error">
                                 <span class="b-icon b-icon_sbr_rattent"></span>
                                 <span id="remind_email_error_txt">Ошибка</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="b-layout__tr g-hidden" data-captcha-block="true">
                        <td class="b-layout__td b-layout__td_left b-layout__td_padbot_37 b-layout__td_relative b-layout__td_width_full_ipad">
                            <div class="b-captcha">
                                <table class="b-layout__table b-layout__table_width_full">
                                    <tr class="b-layout__tr">
                                        <td class="b-layout__td_width_140">
                                            <img width="110" height="58" 
                                                 class="b-captcha__img b-captcha__img_bord_ebe8e8" 
                                                 id="image_rnd" 
                                                 src="/image.php?num=<?=$captchanum?>" 
                                                 alt="captcha" 
                                                 onClick="$('image_rnd').set('src','/image.php?num='+$('captchanum').get('value')+'&r='+Math.random());"/>
                                            <div class="b-captcha__txt b-captcha__txt_inline-block b-layout__txt_padtop_20">&nbsp;&rarr;&nbsp;</div>                                                        
                                        </td>
                                        <td>
                                            <div class="b-combo b-combo_inline-block b-combo_large">
                                                <div class="b-combo__input">
                                                    <input id="captchanum" name="captchanum" type="hidden" value="<?=$captchanum?>" />
                                                    <input class="b-combo__input-text b-combo__input-text_center" 
                                                        id="remind_captcha" 
                                                        data-validators="required minLength:4" 
                                                        name="remind_captcha" 
                                                        type="text" 
                                                        size="80" 
                                                        autocomplete="off" 
                                                        maxlength="5" />
                                                    <label class="b-combo__label" for="remind_captcha"></label>
                                                </div>
                                            </div>                                                        
                                        </td>
                                    </tr>
                                </table>
                                <div class="b-captcha__txt b-captcha__txt_padtop_5">
                                    <a onClick="$('image_rnd').set('src','/image.php?num='+$('captchanum').get('value')+'&r='+Math.random());" 
                                       href="javascript:void(0)" 
                                       class="b-captcha__link">Обновить картинку</a>
                                </div>
                            </div>

                            <div class="b-layout__txt 
                                 b-layout__txt_left 
                                 b-layout__txt_float_left 
                                 b-layout__txt_padtop_5 
                                 b-layout__txt_color_c10600 
                                 b-layout__txt_error 
                                 b-layout__txt_error_right_desktop 
                                 b-shadow_hide" 
                                 id="remind_captcha_error">
                                 <span class="b-icon b-icon_sbr_rattent"></span>
                                 <span id="remind_captcha_error_txt">Ошибка</span>
                            </div>
                        </td>
                    </tr>
                    <tr id="block_role" class="b-layout__tr b-layout_hide">
                        <td class="b-layout__td b-layout__td_left b-layout__td_padbot_37 b-layout__td_relative b-layout__td_width_full_ipad">
                            <div class="b-combo b-combo_large">
                                <div class="
                                     b-combo__input 
                                     b-combo__input_multi_dropdown 
                                     b-combo__input_init_roleList 
                                     show_all_records 
                                     b-combo__input_resize 
                                     multi_drop_down_default_column_0 
                                     b-combo__input_arrow_yes 
                                     disallow_null">
                                    <input class="b-combo__input-text b-combo__input-text_pointer" 
                                           value="" 
                                           id="role" 
                                           name="role" 
                                           type="text" 
                                           size="80" 
                                           placeholder="Тип аккаунта"
                                           readonly="readonly"/>
                                    <label for="role" class="b-combo__label"></label>
                                    <span class="b-combo__arrow"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_padbot_10 b-layout__td_relative b-layout__td_width_full_ipad">
                            <div class="b-buttons"> 
                                <button class="b-button b-button_flat b-button_flat_green b-button_flat_large b-button_flat_width_full b-button_disabled" 
                                        id="remind_button_email" 
                                        onclick="yaCounter6051055.reachGoal('get_remind_frl'); RemindByEmail();">
                                    Восстановить доступ
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
    
    
    <div id="remind_ok_email" style="display:none;">
        <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_padleft_20">
            <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>
            На регистрационный e-mail (<span id="remind_ok_email_txt">al*****23@m**l.ru</span>) аккаунта отправлено письмо<br>с дальнейшими инструкциями по восстановлению доступа.
        </div>
        <div class="b-layout__txt b-layout__txt_padbot_20">Если письмо не будет получено в течении 10 минут - пожалуйста, проверьте папку СПАМ<br>в указанном почтовом ящике или повторите процедуру восстановления доступа.</div>
        <div class="b-buttons b-buttons_padbot_20">
            <a class="b-button b-button_flat b-button_flat_green" href="/login/">Авторизоваться</a>
            
        </div>
    </div>

    <div id="remind_ok_phone" style="display:none;">
        <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_padleft_20">
            <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>
            На привязанный к аккаунту телефон (<span id="remind_ok_phone_txt">?+7960****123</span>) отправлено СМС-сообщение с логином<br>и новым паролем, с помощью которых вы сможете авторизоваться и продолжить работу на сайте.
        </div>
        <div class="b-layout__txt b-layout__txt_padbot_20">Если сообщение не будет получено в течении 10 минут - рекомендуем повторить <br>процедуру восстановления доступа.</div>
        <div class="b-buttons b-buttons_padbot_20">
            <a class="b-button b-button_flat b-button_flat_green" href="/login/">Авторизоваться</a>
            
        </div>
    </div>    
    

</div>