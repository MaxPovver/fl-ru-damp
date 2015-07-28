<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/wizard.common.php");
$xajax->printJavascript('/xajax/'); 
?>
<div class="b-layout">
    <div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
        <h1 class="b-page__title b-page__title_padbot_30">Регистрация</h1>
    </div>
    <div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
        <div class="b-layout b-layout_margleft_-80" id="form_elements">
            <form method="POST" name="form_reg" id="form_reg">
                <input type="hidden" name="action" value="<?= registration::ACTION_REGISTRATION?>">
                <input type="hidden" name="user_action" value="<?php echo $_user_action?>">
            <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                <tbody>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_80"><div class="b-layout__txt b-layout__txt_padtop_5"><label for="login">Логин</label></div></td>
                        <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_padright_10">
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_width_280 <?= $registration->error['login']?"b-combo__input_error":""?>">
                                    <input type="text" autocomplete="off" class="b-combo__input-text" name="login" id="reg_login" size="80" value="<?= $registration->login;?>" onblur="registration_value_check('login')" onfocus="$$('#error_login').addClass('b-shadow_hide');" maxlength="15"/>
                                </div>
                            </div>
                        </td>
                        <td class="b-layout__right b-layout__left_width_410">
                            <div class="i-shadow">
                                <div id="error_login" class="b-shadow b-shadow_m b-shadow_top_0 <?=($registration->error['login'] ? '' : 'b-shadow_hide')?>" style="z-index: 50">
                                    <div class="b-shadow__right">
                                        <div class="b-shadow__left">
                                            <div class="b-shadow__top">
                                                <div class="b-shadow__bottom">
                                                    <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                                        <div id="error_txt_login" class="b-layout__txt b-layout__txt_padright_15 b-layout__txt_color_c4271f"><span class="b-form__error"></span><?= $registration->error['login']?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12"></span>
                                    <span class="b-shadow__icon b-shadow__icon_nosik-left b-shadow__icon_top_20 b-shadow__icon_left_5"></span>
                                </div>                                
                            </div>
                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">3&mdash;15 символов: латинские буквы, цифры, подчёркивание (_) и дефис (-)</div>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_80"><div class="b-layout__txt b-layout__txt_padtop_5"><label for="email">E-mail</label></div></td>
                        <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_padright_10">
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_width_280 <?= $registration->error['email']?"b-combo__input_error":""?>">
                                    <input type="text" class="b-combo__input-text" name="email" id="reg_email" size="80" value="<?= stripslashes($registration->email);?>" onkeyup="registration_value_check('email', $('reg_email').value, 0)" onblur="registration_value_check('email')"/>
                                </div>
                            </div>
                        </td>
                        <td class="b-layout__right">
                            <div class="i-shadow">
                                <div id="error_email" class="b-shadow b-shadow_m b-shadow_top_0 <?=($registration->error['email'] ? '' : 'b-shadow_hide')?>" style="z-index: 40">
                                    <div class="b-shadow__right">
                                        <div class="b-shadow__left">
                                            <div class="b-shadow__top">
                                                <div class="b-shadow__bottom">
                                                    <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                                        <div id="error_txt_email" class="b-layout__txt b-layout__txt_padright_15 b-layout__txt_color_c4271f"><span class="b-form__error"></span><?= $registration->error['email']?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12"></span>
                                    <span class="b-shadow__icon b-shadow__icon_nosik-left b-shadow__icon_top_20 b-shadow__icon_left_5"></span>
                                </div>                                
                            </div>
                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">Нигде не публикуется</div>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_80"><div class="b-layout__txt b-layout__txt_padtop_5"><label for="parol">Пароль</label></div></td>
                        <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_padright_10">
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_width_280 b-combo__input_width_280 b-eye <?= $registration->error['password']?"b-combo__input_error":""?>">
                                    <a tabindex="10000" class="b-eye__link b-eye__link_right_null" href="javascript:void(0)" onclick="show_password()"><span class="b-eye__icon b-eye__icon_close"></span></a>
                                    <input type="password" autocomplete="off" class="b-combo__input-text" name="password" id="reg_password" size="80" value="" onfocus="$$('#error_password').addClass('b-shadow_hide');" onblur="registration_value_check('password')" />
                                </div>
                            </div>
                        </td>
                        <td class="b-layout__right">
                            <div class="i-shadow">
                                <div id="error_password" class="b-shadow b-shadow_m b-shadow_top_-5 <?=($registration->error['password'] ? '' : 'b-shadow_hide')?>">
                                    <div class="b-shadow__right">
                                        <div class="b-shadow__left">
                                            <div class="b-shadow__top">
                                                <div class="b-shadow__bottom">
                                                    <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                                        <div id="error_txt_password" class="b-layout__txt b-layout__txt_padright_15 b-layout__txt_color_c4271f"><span class="b-form__error"></span><?= $registration->error['password']?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12"></span>
                                    <span class="b-shadow__icon b-shadow__icon_nosik-left b-shadow__icon_top_20 b-shadow__icon_left_5"></span>
                                </div>                                
                            </div>
                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">От 6 до 24 символов. Допустимы латинские буквы, цифры и знак подчёркивания (_) </div>
                        </td>
                    </tr>
                    <?php /*if($registration->error['password']) { ?>
                    <tr class="b-layout__tr" id="error_password">
                        <td class="b-layout__left b-layout__left_width_80"></td>
                        <td colspan="2" class="">
                            <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                                <span class="b-form__error"></span> <?= $registration->error['password']?>
                            </div>
                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">От 6 до 24 символов на латинице</div>
                        </td>
                    </tr>
                    <?php }*/ ?>
                    <?php //if(!$_SESSION["regform_captcha_entered"]) { ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_80"></td>
                        <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_padright_10">
                            <div class="b-captcha">
                                <img width="132" height="62" class="b-captcha__img b-captcha__img_bord_ebe8e8" id="rndnumimage" src="/image.php?num=<?=$registration->captchanum?>" alt="" onClick="$('rndnumimage').set('src','/image.php?num='+$('captchanum').get('value')+'&r='+Math.random());"/><div
                                 class="b-captcha__txt b-captcha__txt_inline-block b-captcha__txt_padtop_20">&#160;&rarr;&#160;</div><div
                                 class="b-combo b-combo_inline-block">
                                    <div id="error_captchanum" class="b-combo__input b-combo__input_width_122 b-combo__input_height_60 <?= $registration->error['captcha']?"b-combo__input_error":""?>">
                                        <input id="captchanum" name="captchanum" type="hidden" value="<?=$registration->captchanum?>" />
                                        <input <?= ($registration->error['captcha']?'title="'. $registration->error['captcha'] .'"':"")?> class="b-combo__input-text b-combo__input-text_center" id="reg_rndnum" name="rndnum" type="text" size="80" autocomplete="off" onfocus="clear_error('reg_rndnum');$$('#captcha_error').addClass('b-shadow_hide');" onKeyPress="clear_error('reg_rndnum'); if(event.keyCode==13) { $('frm').submit(); }" maxlength="5"/>
                                    </div>
                                </div>

                                <div class="b-captcha__txt b-captcha__txt_padtop_5"><a class="b-captcha__link" href="javascript:void(0)" onClick="return updateCaptchaImage();">Обновить картинку</a></div>
                            </div>
                        </td>
                        <td class="b-layout__right">
                            <div class="i-shadow">
                                <div id="captcha_error" class="b-shadow b-shadow_m b-shadow_top_0 <?=($registration->error['captcha'] ? '' : 'b-shadow_hide')?>">
                                    <div class="b-shadow__right">
                                        <div class="b-shadow__left">
                                            <div class="b-shadow__top">
                                                <div class="b-shadow__bottom">
                                                    <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                                        <div id="error_txt_captchanum" class="b-layout__txt b-layout__txt_padright_15 b-layout__txt_color_c4271f"><span class="b-form__error"></span><?= $registration->error['captcha']?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12"></span>
                                    <span class="b-shadow__icon b-shadow__icon_nosik-left b-shadow__icon_top_20 b-shadow__icon_left_5"></span>
                                </div>                                
                            </div>
                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_15">Пожалуйста, введите символы с картинки</div>
                        </td>
                    </tr>
                    <?php //} ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_80">&#160;</td>
                        <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_padright_10" colspan="2">
                            <div class="b-radio b-radio_layout_vertical b-radio_inline-block">
                                <div class="b-radio__item b-radio__item_padbot_10">
                                    <input id="freelancer" class="b-radio__input" name="role" type="radio" value="<?= registration::ROLE_FREELANCER?>" <?= $registration->role != registration::ROLE_EMPLOYER ? 'checked="checked"' : ''; ?>/>
                                    <label class="b-radio__label b-radio__label_fontsize_13" for="freelancer">Фрилансер</label>
                                </div>
                                <div class="b-radio__item">
                                    <input id="employer" class="b-radio__input" name="role" type="radio" value="<?= registration::ROLE_EMPLOYER?>" <?= $registration->role == registration::ROLE_EMPLOYER ? 'checked="checked"' : ''; ?>/>
                                    <label class="b-radio__label b-radio__label_fontsize_13" for="employer" >Работодатель</label>
                                </div>
                            </div>
                           <div class="b-fon b-fon_inline-block b-fon_pad_5_10 b-fon_bg_fff9bf b-fon_margleft_20 b-fon_valign_top b-fon_margtop_-5">
                              <div class="b-layout__txt b-layout__txt_fontsize_11">Каждому пользователю разрешается регистрировать<br>по одному аккаунту Фрилансера и Работодателя (<a href="<?=WDCPREFIX?>/about/documents/appendix_2_regulations.pdf" class="b-layout__link " target="_blank">Правила</a> сайта, п.1.4).<br>Все аккаунты, созданные в обход данного правила, могут быть заблокированы.</div>
                           </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            </form>
        </div>
        
        <div class="b-buttons b-buttons_padtop_20 b-buttons_padbot_100">
            <a href="javascript:void(0)" id="send_btn" class="b-button b-button_rectangle_color_green b-button_rectangle_color_disable" onclick="formSubmit();">
                <span class="b-button__b1">
                    <span class="b-button__b2 b-button__b2_padlr_15">
                        <span class="b-button__txt">Зарегистрироваться</span>
                    </span>
                </span>
            </a>&#160;&#160;

            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_10">
                Нажимая на кнопку &laquo;Зарегистрироваться&raquo;, я соглашаюсь с <a href="<?=WDCPREFIX?>/about/documents/agreement_site.pdf" class="b-layout__link " target="_blank">публичной офертой ООО «Ваан»</a> и <a href="<?=WDCPREFIX?>/about/documents/appendix_2_regulations.pdf" class="b-layout__link " target="_blank">правилами сайта</a>.
            </div>
        </div>
    </div>
</div>
