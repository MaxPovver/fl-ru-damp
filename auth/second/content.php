<?php

if (!defined('IN_STDF')) { 
    header ("Location: /404.php");
    exit;
}

?>
<div class="b-layout">
<?php if ($alert_message): ?>
    <div class="b-fon b-fon_padbot_20">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
           <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25"></span>
           <?php echo $alert_message; ?>
        </div>
    </div>
<?php endif; ?>    
    <table class="b-layout__table">
        <tr class="b-layout__tr">
            <td class="b-layout__td">
                <div class="b-fon b-fon_bg_f2  b-fon_pad_20">
                    <h1 class="b-layout__title b-layout__title_padbot_20">
                        Авторизация
                    </h1>
                    <div class="b-layout__txt b-layout__txt_padbot_20">
                        <?php if ($_2fa_provider > 0): ?>
                        У вас включена двухэтапная аутентификация, пожалуйста, авторизуйтесь в социальной сети.
                        <?php else: ?>
                        У вас включена двухэтапная аутентификация, пожалуйста, авторизуйтесь на сайте.    
                        <?php endif; ?>
                    </div>
                    <?php if ($_2fa_provider > 0): ?>
                    <div class="b-layout__txt">
                        <?php if($_2fa_provider == OpauthModel::TYPE_VK): ?>
                        <a href="/auth/?param=vkontakte" class="b-auth_btn b-auth_btn_flat b-auth_btn_vk b-auth_btn_h40">ВКонтакте</a>
                        <?php elseif($_2fa_provider == OpauthModel::TYPE_FACEBOOK): ?>
                        <a href="/auth/?param=facebook" class="b-auth_btn b-auth_btn_flat b-auth_btn_facebook b-auth_btn_h40">Facebook</a>
                        <?php else: ?>
                        <a href="/auth/?param=odnoklassniki" class="b-auth_btn b-auth_btn_flat b-auth_btn_odnoklassniki b-auth_btn_h40">Одноклассники</a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                        <form name="form_reg" id="login_form" method="POST" action="/">
                            <input type="hidden" name="action" value="login" />
                            <?php if(isset($_user_action)): ?>
                            <input type="hidden" name="user_action" value="<?=$_user_action?>" />
                            <?php endif; ?>
                            <?php if(isset($redirectUri)): ?>
                            <input type="hidden" name="redirect" value="<?=$redirectUri?>" />    
                            <?php endif; ?>
                            <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                                <tbody>
                                    <tr class="b-layout__tr">
                                        <td class="b-layout__td b-layout__td_width_120">
                                            <label class="b-layout__txt b-layout__txt_block b-layout__txt_nowrap b-layout__txt_lineheight_1" for="loginEmail">
                                                Логин, телефон&#160;<br/>или e-mail
                                            </label>
                                        </td>
                                        <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_280">
                                            <div class="b-combo">
                                                <div class="b-combo__input b-combo__input_width_280 ">
                                                    <input id="loginEmail" type="text" name="login" value="<?=$_2fa_login?>" size="80"  class="b-combo__input-text">
                                                </div>
                                            </div>
                                        </td>
                                        <td class="b-layout__td">&#160;</td>
                                    </tr>

                                    <tr class="b-layout__tr">
                                        <td class="b-layout__td b-layout__txt_nowrap b-layout__td_width_120">
                                                <label class="b-layout__txt b-layout__txt_block b-layout__txt_padtop_5" for="pass">
                                                    Пароль
                                                </label>
                                        </td>

                                        <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_280">
                                            <div class="b-combo">
                                                <div class="b-combo__input b-combo__input_width_280 b-combo__input_width_280 b-eye ">
                                                    <a onclick="show_password('login_pass')" href="javascript:void(0)" class="b-eye__link b-eye__link_right_null " tabindex="10000"><span class="b-eye__icon b-eye__icon_close"></span></a>
                                                    <input type="password"  value="" size="80" id="login_pass" name="passwd" class="b-combo__input-text" autocomplete="off">
                                                </div>
                                            </div>

                                            <div class="b-check b-check_padtop_10">
                                                <input id="rem" class="b-check__input" name="autologin" type="checkbox" value="1" />
                                                <label for="rem" class="b-check__label b-check__label_ptsans_fs_11">
                                                    Запомнить меня
                                                </label>
                                            </div>                            
                                        </td>
                                        <td class="b-layout__td">&#160;</td>
                                    </tr>

                                    <tr class="b-layout__tr">
                                        <td class="b-layout__td b-layout__td_width_120"></td>
                                        <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_280">
                                            <div class="b-buttons">
                                                <button class="b-button b-button_flat b-button_flat_green" id="send_btn" onclick="yaCounter6051055reachGoal('avtorizacia'); return true;" type="submit">Войти</button> &#160;&#160;&#160;
                                                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_15 b-layout__txt_inline-block">или <a class="b-layout__link" href="/remind/">восстановить пароль</a></div>
                                            </div>
                                        </td>
                                        <td class="b-layout__td">&#160;</td>
                                    </tr>
                                </tbody>
                            </table>
                      </form>
                    <?php endif; ?>
                </div>
                
                <?php if ($_2fa_provider == 0): ?>
                <div class="b-layout__txt b-layout__txt_padtop_15">
                    Номер телефона указывается вместе с кодом страны (в формате +7..., +380... и т.д.)
                </div>
                <?php endif; ?>
                
            </td>
        </tr>
    </table>
</div>