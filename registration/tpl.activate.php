<script>
function error_clear(obj) {
    $(obj).getParent().removeClass('b-combo__input_error');
    var error = $(obj).getParent('.b-captcha').getElement('.b-layout-error');
    if(error != undefined) error.dispose();
} 
</script>
<div class="b-layout">
    <div class="b-layout__right b-layout__right_width_73ps b-layout__right_float_right">
        <h1 class="b-page__title b-page__title_padbot_30">Активация учётной записи</h1>
    </div>
    <div class="b-layout__right b-layout__right_width_73ps b-layout__right_float_right">
        <?php if($code) {?>
        <form method="POST" name="form_act" id="form_act">
            <input type="hidden" name="action" value="<?= registration::ACTION_ACTIVATE;?>">
            <div class="b-fon b-fon_inline-block b-fon_padbot_100">
                <div class="b-fon__body b-fon__body_pad_15  b-fon__body_padleft_30 b-fon__body_padright_40 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_lineheight_18">
                    <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Пожалуйста, введите символы с картинки и нажмите «Активировать».
                    <div class="b-captcha b-captcha_padtop_15 b-captcha_padleft_90 b-form">
                        <img width="132" height="62" class="b-captcha__img b-captcha__img_bord_ebe8e8" id="rndnumimage" src="/image.php?num=<?=$registration->captchanum?>" alt=""  onclick="$('rndnumimage').set('src','/image.php?num='+$('captchanum').get('value')+'&r='+Math.random());"/>
                        <div class="b-captcha__txt b-captcha__txt_inline-block b-captcha__txt_padtop_20">&rarr;&#160;</div>
                        <div class="b-combo b-combo_inline-block">
                            <input id="captchanum" name="captchanum" type="hidden" value="<?=$registration->captchanum?>" />
                            <div class="b-combo__input b-combo__input_width_105 b-combo__input_height_58 <?= $registration->error['rndnum'] ? "b-combo__input_error" : ""?>">
                                <input  class="b-combo__input-text b-combo__input-text_center" name="rndnum" type="text" size="80"  onclick="error_clear(this);"/>
                            </div>
                        </div>
                        <div class="b-captcha__txt b-captcha__txt_padbot_20 b-captcha__txt_padtop_5">
                            <a class="b-captcha__link" href="javascript:void(0)" onclick="$('rndnumimage').set('src','/image.php?num='+$('captchanum').get('value')+'&r='+Math.random()); return false;">Обновить картинку</a>
                        </div>
                        <?php if($registration->error['rndnum']) {?>
                        <div class="b-captcha__txt b-captcha__txt_color_c4271f b-layout-error"><span class="b-form__error"></span>Введены неверные символы</div>
                        <?php }//if?>
                        <div class="b-buttons b-buttons_padtop_20 b-buttons_padleft_50 b-buttons_padbot_10">
                            <a href="javascript:void(0)" onclick="$('form_act').submit()" class="b-button b-button_rectangle_color_green">
                                <span class="b-button__b1">
                                    <span class="b-button__b2">
                                        <span class="b-button__txt">Активировать</span>
                                    </span>
                                </span>
                            </a>						
                        </div>
                    </div>
                </div>
            </div>
        </form>   
        <?php } else {//if?>
            <div class="b-captcha__txt b-captcha__txt_color_c4271f"><span class="b-form__error"></span>
                Произошла ошибка! Ваш аккаунт уже активирован, либо введенный код не найден.<br>
                Если у вас не получается залогиниться на сайте, попробуйте снова зарегистрировать аккаунт.
            </div>
        <?php }//else?>
    </div>
</div>