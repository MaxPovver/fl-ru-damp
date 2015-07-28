<div class="b-shadow__right" id="sms_is_load">
    <div class="b-shadow__left">
        <div class="b-shadow__top">
            <div class="b-shadow__bottom">
                <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                    <h3 class="b-shadow__title b-shadow__title_fontsize_15 b-shadow__title_bold b-shadow__title_padbot_15">Отвязать телефон</h3>
                    <div class="b-shadow__txt b-shadow__txt_padbot_5">На номер <?= $ureqv['mob_phone']?> было отправлено СМС с <?= sms_gate::LENGTH_CODE; ?> цифрами. Введите их:</div>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_50">
                            <input id="i_sms_code" onfocus="$('a_sms_act').removeClass('b-button_disabled'); $('sms_error').addClass('b-layout__txt_hide'); $(this).getParent().removeClass('b-combo__input_error')" name="sms_code" type="text" size="80" value="<?= ($code_debug ? $code_debug : '')?>" maxlength="<?= sms_gate::LENGTH_CODE;?>" class="b-combo__input-text b-combo__input-text_center b-combo__input-text_fontsize_18 b-combo__input-text_bold"/>
                        </div>
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padleft_5">
                        <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_c10600 b-layout__txt_hide" id="sms_error">В СМС были другие <?= sms_gate::LENGTH_CODE;?> цифры.</span>
                        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="xajax_unactivateAuth('<?=$_SESSION['uid']?>', 'resend')" id="a_sms_resend">СМС не пришло</a>
                    </div>
                    <div class="b-buttons b-buttons_padtop_20 b-buttons_padbot_10">
                        <a href="javascript:void(0)" onclick="<?= ( $callback_js ? $callback_js : 'a_sms_act' ); ?>(this);" class="b-button b-button_flat b-button_flat_green <?= $code_debug ? '' : 'b-button_disabled'?>" id="a_sms_act">Отвязать
                                    <img class="b-button__load" width="26" height="6" alt="" src="/css/block/b-button/b-button__load.gif" style="display:none" /></a>&#160;
                        <span class="b-buttons__txt">или</span>
                        <a href="javascript:void(0)" onclick="$('sms_is_load').getParent().addClass('b-shadow_hide');" class="b-buttons__link b-buttons__link_dot_c10601">закрыть</a>
                    </div>  
                </div>
            </div>
        </div>
    </div>
</div>
<div class="b-shadow__tl"></div>
<div class="b-shadow__tr"></div>
<div class="b-shadow__bl"></div>
<div class="b-shadow__br"></div>
<span class="b-shadow__icon b-shadow__icon_close" onclick="$('sms_is_load').getParent().addClass('b-shadow_hide');"></span>