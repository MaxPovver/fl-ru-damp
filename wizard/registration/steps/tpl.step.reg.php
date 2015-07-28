<?php
if(__paramInit('string', null, 'action')!='registration' || ($registration->error['captcha'] && __paramInit('string', null, 'action')=='registration') ) {
  unset($_SESSION['w_reg_captcha_num']);
  $captchanum = uniqid('',true);
  $captcha = new captcha($captchanum);
  $captcha->setNumber();
} else {
  $captchanum = $_SESSION['w_reg_captcha_num'];
  $captcha = new captcha($captchanum);
}

?>
<script type="text/javascript">
    var smsIsRequested = '<?=intval($_SESSION['smsIsRequested']) ?>';
    var smsNumber = '<?=($_SESSION["reg_phone"]?$_SESSION["reg_phone"]:'00000000000') ?>';
</script>
<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
    <?php if($this->status == step_wizard::STATUS_CONFIRM) { ?>
    <div class="b-fon b-fon_inline-block b-fon_padbot_10">
        <div class="b-fon__body b-fon__body_pad_15  b-fon__body_padleft_30 b-fon__body_padright_40 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_lineheight_18">
            <span class="b-fon__ok"></span>На адрес <?= $_SESSION['email']?> было отправлено письмо. Для завершения регистрации на сайте<br />пройдите по ссылке в письме.  
        </div>
    </div>
    <?php if($_SESSION['suspect'] == false) {?>
        <form name="form_mail_send" id="form_mail_send" method="POST">
            <input type="hidden" name="action" value="<?= registration::ACTION_SEND_MAIL; ?>">
        </form>
        <div class="b-layout__txt"><a class="b-layout__link" href="javascript:void(0)" onclick="$('form_mail_send').submit();">Выслать письмо</a> еще раз.</div>
    <?php }//if?>
    <?php } else { //if?>
    <div id="reg-block"<?= $action == 'authorization' ? ' class="b-layout_hide"' : '' ?>>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padtop_20" style="padding-left:6px;">
        <? if ($type_user == step_wizard_registration::TYPE_WIZARD_EMP) { ?>
        Для того чтобы завершить поиск исполнителя, необходимо зарегистрироваться.
        <? } else { //if?>
        Для того чтобы завершить поиск работы, необходимо зарегистрироваться.
        <? } //else?>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_20" style="padding-left:6px;">
        <a id="open-auth-block" class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)">Я уже зарегистрирован</a>
    </div>
    
    <form method="POST" id="frm">
        <input type="hidden" name="action" value="registration">
        <div class="b-layout b-layout_margleft_-55">
            <table class="b-layout__table" border="0" cellpadding="0" cellspacing="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55"><div class="b-layout__txt b-layout__txt_padtop_5">Логин</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_padright_10" style="padding-bottom:0">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_280 <?= $error['login']?"b-combo__input_error":""?>">
                                <input type="text" autocomplete="off" class="b-combo__input-text" <?= ($error['login']?'title="'. $error['login'] .'"':"")?> name="login" id="reg_login" size="80" value="<?= stripslashes($login); ?>" maxlength="15" onkeyup="registration_value_check('login', $('reg_login').value, 0)" onblur="registration_value_check('login')" />
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__right"><div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">3—15 символов: латинские буквы, цифры, знак подчеркивания (_) и дефис (-)</div></td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55" style="height:28px"></td>
                    <td colspan="2" class="b-layout__middle_padtop_10" id="error_login" <?php if (!$error["login"]) {?>style="display:none"<?} ?>>
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10" id="error_txt_login">
                                <span class="b-form__error"></span><?=$error["login"] ?>
                        </div>
                    </td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55 b-layout__middle_padtop_10"><div class="b-layout__txt b-layout__txt_padtop_5">E-mail</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_padright_10 b-layout__middle_padtop_10" style="padding-bottom:0">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_280 <?= $error['email']?"b-combo__input_error":""?>">
                                <input type="text" class="b-combo__input-text" <?= ($error['email']?'title="'. $error['email'] .'"':"")?> name="email" id="reg_email" size="80" value="<?= stripslashes($email);?>" onkeyup="registration_value_check('email', $('reg_email').value, 0)" onblur="registration_value_check('email')"/>
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__right b-layout__middle_padtop_10"><div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">Нигде не публикуется</div></td>
                </tr>
                
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55" style="height:28px"></td>
                    <td colspan="2" class="b-layout__middle_padtop_10" id="error_email" <?php if (!$error["email"]) {?>style="display:none" <?} ?>>
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10" id="error_txt_email">
                                <span class="b-form__error"></span><?=$error["email"] ?>
                        </div>
                    </td>
                </tr>
                
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55 b-layout__middle_padtop_10"><div class="b-layout__txt b-layout__txt_padtop_5">Пароль</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_padright_10 b-layout__middle_padtop_10" style="padding-bottom:0">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_280 b-eye <?= $error['pwd']?"b-combo__input_error":""?>">
                                <a tabindex="10000" class="b-eye__link b-eye__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="show_password()"><span class="b-eye__icon b-eye__icon_close"></span></a>
                                <input type="password" autocomplete="off"  class="b-combo__input-text" <?= ($error['pwd']?'title="'. $error['pwd'] .'"':"")?> name="password" id="reg_password" size="80" value="" maxlength="24" onblur="registration_value_check('password')" />
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__right">&nbsp;</td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55" style="height:28px"></td>
                    <td colspan="2" class="b-layout__middle_padtop_10" id="error_password" <?php if (!$error["password"]) {?>style="display:none"<?} ?>>
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10" id="error_txt_password">
                                <span class="b-form__error"></span><?=$error["password"] ?>
                        </div>
                    </td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55">&#160;</td>
                    <td class="b-layout__middle b-layout__middle_padright_10" style="padding-bottom:0;">
                        <div class="b-check">
                            <input id="reg_agree" class="b-check__input" name="agree" type="checkbox" value="1" <?=($agree ? 'checked' : '')?> <? browserCompat($b, $v); if ($b == 'msie' && intval($v) < 9) print "onclick"; else print "onchange"?>="onCheckAgreeClick()" />
                            <label class="b-check__label" for="agree">Я ознакомлен с <a href="<?=WDCPREFIX?>/about/documents/appendix_2_regulations.pdf" target="_blank" class="b-layout__link">правилами сайта</a></label>
                        </div>
                    </td>
                    <td class="b-layout__right">&nbsp;</td>
                </tr>
                
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55" style="height:28px"></td>
                    <td colspan="2" id="error_agree" <?php if(!$error['agree']) { ?>style="display:none" <?} ?>>
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10" id="error_txt_agree" >
                            <span class="b-form__error"></span> <?= $error['agree']?>
                        </div>
                    </td>
                </tr>
                
                <? if (!$_SESSION["regform_captcha_entered"])  {?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55"><div class="b-layout__txt b-layout__txt_padtop_5"></div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_padright_10 b-layout__middle_padtop_10">
                        <div class="b-captcha b-captcha_padtop_15 b-form" style="height:107px">
                            <img id="rndnumimage" class="b-captcha__img b-captcha__img_bord_ebe8e8" style="width:132px;" width="132" height="62" src="/image.php?num=<?=$captchanum?>" alt="" onClick="$('rndnumimage').set('src','/image.php?num='+$('captchanum').get('value')+'&r='+Math.random());"/><div
                             class="b-captcha__txt b-captcha__txt_inline-block b-captcha__txt_padtop_20">&#160;&rarr;&#160;</div><div
                             class="b-combo b-combo_inline-block">
                                <div id="error_captchanum" class="b-combo__input b-combo__input_width_122 b-combo__input_height_60 <?= $error['captcha']?"b-combo__input_error":""?>">
                                    <input id="captchanum" name="captchanum" type="hidden" value="<?=$captchanum?>" />
                                    <input <?= ($error['captcha']?'title="'. $error['captcha'] .'"':"")?> class="b-combo__input-text b-combo__input-text_center" id="reg_rndnum" name="rndnum" type="text" size="80" autocomplete="off" onfocus="$$('#captcha_error').setStyle('display', 'none');" onKeyPress="clear_error('reg_rndnum'); if(event.keyCode==13) { $('frm').submit(); }" maxlength="5" />
                                </div>
                            </div>

                            <div class="b-captcha__txt b-captcha__txt_padbot_5 b-captcha__txt_padtop_5"><a class="b-captcha__link" href="javascript:void(0)" onClick="return updateCaptchaImage();">Обновить картинку</a></div>
                        <div id="captcha_error" class="b-captcha__txt b-captcha__txt_color_c4271f b-layout-error" style="display:none">
                            <span id="error_txt_captchanum"><span class="b-form__error"></span><?= $error['captcha'] ?></span>
                        </div>
                        </div>
                        
                    </td>
                    <td class="b-layout__right b-layout__txt_padtop_20"><div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_30">Пожалуйста, введите символы с картинки</div></td>
                </tr>
                <?} ?>
                <!-- телефон -->
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55"><div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_padright_10">Телефон</div></td>
                    <td class="b-layout__middle b-layout__middle_padright_10" colspan="2" style="padding-bottom:0">
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_170 b-combo__input_tel b-combo__input_phone_countries_dropdown b-combo__input_visible_items_5 use_scroll show_all_records b-combo__input_init_countryPhoneCodes">
                                <input type="text" value="<?=($phone ? $phone : ($_SESSION['reg_phone'] ? $_SESSION['reg_phone'] : '')) ?>" size="80" name="phone" class="b-combo__input-text" id="reg_phone" onblur="registration_value_check('phone')" onchange="onChangeCode()" maxlength="15">
                                <span class="b-combo__tel"><span style="background-position:0 -660px" class="b-combo__flag"></span></span>
                            </div>
                        </div> &nbsp;&nbsp;<div  class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5 b-layout__txt_inline-block">Введите номер телефона без пробелов и дефисов</div>
                    </td>
                </tr>
                <?php //if ($error["phone"]) {?>          
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55" style="height:28px"></td>
                    <td colspan="2" class="b-layout__middle_padtop_10" id="error_phone" <?=(strlen($error["phone"]) ? '' : 'style="display:none"') ?>>
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10" id="error_txt_phone">
                            <span class="b-form__error"></span><?=$error["phone"] ?>
                        </div>
                    </td>
                </tr>
                <?php //}?>
                <!-- // телефон -->
                
                <!-- подтверждение по смс -->
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55"><div class="b-layout__txt b-layout__txt_padtop_5"></div></td>
                    <td class="b-layout__middle b-layout__middle_padright_10 b-layout__middle_padtop_10" colspan="2" style="padding-bottom:0">
                        <label class="b-layout__txt">Введите код
                            <div class="b-combo__input" style="margin:0 2px 0 0px;">
                                <input type="text" value="<?=$smscode?>" name="smscode" id="reg_smscode" class="b-combo__input-text b-layout__left_width_70" onblur="checkValueAllInputs()" />
                            </div>
                        </label>
                        <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">
                            <a id="getsms" class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="#">Получить смс с кодом</a>
                        </span>
                    </td>
                </tr>
                <?php //if ($error["smscode"]) {?>          
                <tr class="b-layout__tr" >
                    <td class="b-layout__left b-layout__left_width_55" style="height:28px"></td>
                    <td colspan="2" class="b-layout__middle_padtop_10" id="error_smscode" <?=(strlen($error["smscode"]) ? '' : 'style="display:none"') ?>>
                        <div class="b-layout__txt b-layout__txt_color_c4271f" id="error_txt_smscode">
                            <span class="b-form__error"></span><?=$error["smscode"] ?>
                        </div>
                    </td>
                </tr>
                <?php //}?> 
                <!-- подтверждение по смс -->  
            </table>
        </div>
        <div class="b-layout__txt b-layout__txt_padbot_15" style="padding-left:6px;padding-top:15px;">Вы будете зарегистрированы как <?= $type_user == step_wizard_registration::TYPE_WIZARD_EMP ?"работодатель":"фрилансер"?></div>
        <div class="b-layout__txt b-layout__txt_padbot_30" style="padding-left:6px;">
            <a class="b-layout__link" href="/wizard/registration/?step=1&role=<?= ($type_user == step_wizard_registration::TYPE_WIZARD_EMP ? 2 : 1); ?>">
                <? if ($type_user == step_wizard_registration::TYPE_WIZARD_EMP) { ?>
                    Зарегистрироваться как фрилансер
                <? } else { //if?>
                    Зарегистрироваться как работодатель
                <?php }//else?>
            </a>
        </div>				

        <div class="b-buttons">
            <a href="javascript:void(0)" id="send_btn" class="b-button b-button_rectangle_color_green <?= (count($error) > 0 ?"b-button_disabled":"")?>" onclick="if(!$(this).hasClass('b-button_rectangle_color_disable')) $('frm').submit();">
                <span class="b-button__b1">
                    <span class="b-button__b2 b-button__b2_padlr_15">
                        <span class="b-button__txt">Продолжить</span>
                    </span>
                </span>
            </a>&#160;&#160;
            <!-- <a href="#" class="b-buttons__link">пропустить этот шаг</a> -->
            <span class="b-buttons__txt">&#160;или&#160;</span>
            <a href="/wizard/registration/?action=exit" class="b-buttons__link b-buttons__link_color_c10601">выйти из мастера</a>
        </div>
    </form>
    </div>
    <div id="auth-block"<?= $action != 'authorization' ? ' class="b-layout_hide"' : '' ?>>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padtop_20">
        <? if ($type_user == step_wizard_registration::TYPE_WIZARD_EMP) { ?>
        Для того чтобы завершить поиск исполнителя, необходимо войти.
        <? } else { //if?>
        Для того чтобы завершить поиск работы, необходимо войти.
        <? } //else?>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_20">
        <a id="open-reg-block" class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)">Я еще не зарегистрирован</a>
    </div>
    
    <form method="POST" id="frm-auth">
        <input type="hidden" name="action" value="authorization">
        <div class="b-layout b-layout_margleft_-55">
            <table class="b-layout__table" border="0" cellpadding="0" cellspacing="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55"><div class="b-layout__txt b-layout__txt_padtop_5">Логин</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_padright_10">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_260<?= $auth_error['login'] ? " b-combo__input_error" : "" ?>">
                                <input<?= $auth_error['login'] ? ' title="' . $auth_error['login'] . '"' : "" ?> type="text" autocomplete="off" class="b-combo__input-text" name="auth_login" id="auth_login" size="80" value="<?= $auth_login;?>" maxlength="15" onfocus="clear_error('auth_login');$$('#error_auth_login').dispose()" />
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__right"></td>
                </tr>
                <?php if ($auth_error["login"]) {?>
                <tr class="b-layout__tr" id="error_auth_login">
                    <td class="b-layout__left b-layout__left_width_55"></td>
                    <td colspan="2">
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                            <span class="b-form__error"></span><?= $auth_error["login"] ?>
                        </div>
                    </td>
                </tr>
                <?php }?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55"><div class="b-layout__txt b-layout__txt_padtop_5">Пароль</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_padright_10">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_245 b-eye<?= $auth_error['password'] ? " b-combo__input_error" : "" ?>">
                                <a tabindex="10000" class="b-eye__link b-eye__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="show_password('auth_password')"><span class="b-eye__icon b-eye__icon_open"></span></a>
                                <input<?= $auth_error['password'] ? ' title="' . $auth_error['password'] . '"' : "" ?> type="text" autocomplete="off"  class="b-combo__input-text" name="auth_password" id="auth_password" size="80" value="" maxlength="24" onfocus="clear_error('auth_password');$$('#error_auth_password').dispose()" onkeyup="wizardLoginFormSubmit(event)"/>
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__right"></td>
                </tr>
                <?php if ($auth_error["password"]) {?>
                <tr class="b-layout__tr" id="error_auth_password">
                    <td class="b-layout__left b-layout__left_width_55"></td>
                    <td colspan="2">
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                            <span class="b-form__error"></span><?= $auth_error["password"] ?>
                        </div>
                    </td>
                </tr>
                <?php }?>
            </table>
        </div>
        <div class="b-buttons">
            <a href="javascript:void(0)" id="send_btn" class="b-button sendBtnUnique b-button_rectangle_color_green" onclick="if(!$(this).hasClass('b-button_rectangle_color_disable')) $('frm-auth').submit();">
                <span class="b-button__b1">
                    <span class="b-button__b2 b-button__b2_padlr_15">
                        <span class="b-button__txt">Продолжить</span>
                    </span>
                </span>
            </a>&#160;&#160;
            <span class="b-buttons__txt">&#160;или&#160;</span>
            <a href="/wizard/registration/?action=exit" class="b-buttons__link b-buttons__link_color_c10601">выйти из мастера</a>
        </div>
    </form>
    </div>
    <?php }//else?>
</div>