<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/wizard.common.php");
$xajax->printJavascript('/xajax/'); 

$action = !isset($action)?registration::ACTION_STEP1 : $action;

?>
<div class="b-layout g-txt_center">

    <form id="form_reg" name="form_reg" method="POST">
        <input type="hidden" name="action" value="<?= $action ?>">
        <input type="hidden" name="user_action" value="<?php echo $_user_action?>">
        <input type="hidden" name="redirect" value="<?=$redirectUri?>" />

        
        <?php if($action == registration::ACTION_REGISTRATION): ?>
        
        <div class="b-layout b-layout_padtop_45 b-layout_padleft_20 b-layout_padright_20">
            
            <h1 class="b-layout__title b-layout__title_lh_1 b-layout__title_fs30 b-layout__title_color_333 b-layout__title_padbot_40">
                Логин является вашим именем на портале FL.ru
            </h1>

            <div class="b-layout b-layout_inline-block b-layout_width_330 b-layout_width_full_iphone">
                <table class="b-layout__table b-layout__table_width_full">
                    <tbody>
                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_relative b-layout__td_width_full_ipad">
                                <div class="b-combo b-combo_large">
                                    <div class="b-combo__input <?= $registration->error['login']?"b-combo__input_error":""?>">
                                        <input data-ga-el="<?=$registration->role == registration::ROLE_EMPLOYER?'customer':'freelancer'?>"
                                               data-ga-event="{ec: 'user', ea: 'registration_login_edited',el: ''}" 
                                               type="text" 
                                               maxlength="15" 
                                               onfocus="$$('#error_login').addClass('b-shadow_hide');" 
                                               onblur="registration_value_check('login')" 
                                               value="<?= $registration->login;?>" 
                                               size="80" 
                                               id="reg_login" 
                                               name="login" 
                                               class="b-combo__input-text" 
                                               autocomplete="off" 
                                               placeholder="Логин"/>
                                        <label class="b-combo__label" for="reg_login"></label>
                                    </div>
                                </div>

                                <?php if(false): ?>
                                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">3—15 символов: латинские буквы, цифры,<br>знак подчёркивания (_) и дефис (-)</div>
                                <?php endif; ?>

                                <div class="b-layout__txt 
                                     b-layout__txt_left 
                                     b-layout__txt_float_left 
                                     b-layout__txt_padtop_5 
                                     b-layout__txt_color_c10600 
                                     b-layout__txt_error 
                                     b-layout__txt_error_right_desktop 
                                     <?=($registration->error['login'] ? '' : 'b-shadow_hide')?>"
                                     id="error_login">
                                     <span class="b-icon b-icon_sbr_rattent"></span>
                                     <span id="error_txt_login"><?= $registration->error['login']?></span>
                                </div>                                
                            </td>
                        </tr>

                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_relative b-layout__td_width_full_ipad">
                                <div class="b-buttons"> 
                                    <button data-ga-event="{ec: 'user', ea: 'registration_regbutton2_clicked',el: ''}" 
                                            type="submit" 
                                            onclick="formSubmit(); return false;" 
                                            class="b-button b-button_flat b-button_flat_green b-button_flat_large b-button_flat_width_full" 
                                            id="send_btn">

                                        Завершить регистрацию
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        
            
        </div>
        
        <?php else: ?>
        
        
        <div class="b-layout b-layout_gradpane">
            
            <?php if(!$from_welcome_wizard): ?>
            <h1 class="b-layout__title b-layout__title_lh_1 b-layout__title_fs25 b-layout__title_color_333 b-layout__title_padbot_28">
                Регистрация
            </h1>
            <?php endif; ?>
            
            <div class="b-radio b-radio_layout_horizontal b-radio_box b-radio_box_w333 <?php if (isset($customer_wizard) && $customer_wizard): ?>g-hidden<?php endif; ?> <?= $registration->role == registration::ROLE_EMPLOYER ? 'b-radio_box_bg_53b523' : ''; ?>" data-element-radiobox="role">
                <div class="b-radio__item <?= $registration->role != registration::ROLE_EMPLOYER ? 'b-radio__item_checked' : ''?>">
                    <input data-radiobox-class="b-radio_box_bg_ff7300"
                           data-show-id="freelancer-txt" 
                           type="radio" 
                           name="role" 
                           value="<?= registration::ROLE_FREELANCER?>" <?= $registration->role != registration::ROLE_EMPLOYER ? 'checked="checked"' : ''; ?> 
                           class="b-radio__input b-radio__input_hide" 
                           id="freelancer">
                    <label for="freelancer" class="b-radio__label" data-ga-event="{ec: 'user', ea: 'registration_switcher_used',el: 'freelancer'}">
                        Я фрилансер, ищу работу
                    </label>
                </div>
                <div class="b-radio__item <?= $registration->role == registration::ROLE_EMPLOYER ? 'b-radio__item_checked' : ''?>">
                    <input data-radiobox-class="b-radio_box_bg_53b523" 
                           data-show-id="employer-txt"
                           type="radio" 
                           name="role" 
                           value="<?= registration::ROLE_EMPLOYER?>" <?= $registration->role == registration::ROLE_EMPLOYER ? 'checked="checked"' : ''; ?> 
                           class="b-radio__input b-radio__input_hide" 
                           id="employer">
                    <label for="employer" class="b-radio__label" data-ga-event="{ec: 'user', ea: 'registration_switcher_used',el: 'customer'}">
                        Я заказчик, ищу исполнителя
                    </label>
                </div>
            </div>            
            
            <div id="employer-txt" class="b-layout__txt b-layout__txt_fontsize_30 b-layout__txt_lineheight_44 b-layout__txt_color_333 b-layout__txt_padtop_25 <?= $registration->role != registration::ROLE_EMPLOYER ? 'g-hidden' : ''?>">
                Зарегистрируйся и размести проект: <br/>
                мы подберем тебе <strong>лучших исполнителей</strong> с гарантией <br/>
                выполнения работы <strong>в срок</strong> через <strong>безопасную сделку!</strong>
            </div>            
            
            <div id="freelancer-txt" class="b-layout__txt b-layout__txt_fontsize_30 b-layout__txt_lineheight_44 b-layout__txt_color_333 b-layout__txt_padtop_25 <?= $registration->role == registration::ROLE_EMPLOYER ? 'g-hidden' : ''?>">
                Регистрируйся и зарабатывай <strong>с гарантией оплаты</strong> проектов <br/>
                от более чем <strong>10 000 лучших заказчиков</strong> рунета, <br/>
                увеличивай свой рейтинг и доход <strong>на постоянном потоке заказов.</strong>
            </div>
            
        </div>
        
        
        <div class="b-layout b-layout_pad_40_20_0_20">
            
            <h2 class="b-layout__title b-layout__title_lh_1 b-layout__title_fs24 b-layout__title_color_333 b-layout__title_padbot_25">
                Регистрируйся через почту
            </h2>

            <div class="b-layout b-layout_inline-block b-layout_width_330 b-layout_width_full_iphone b-layout_padbot_70">
                
                <?php if ($alert_message): ?>
                    <div class="b-fon b-fon_padbot_20">
                        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
                           <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25"></span>
                           <?php echo $alert_message; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <table class="b-layout__table b-layout__table_width_full">
                    <tbody>
                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_relative b-layout__td_width_full_ipad">
                                <div class="b-combo b-combo_large">
                                    <div class="b-combo__input <?= $registration->error['email']?"b-combo__input_error":""?>">
                                        <input data-ga-event="{ec: 'user', ea: 'registration_form_edited',el: ''}" 
                                               type="text" 
                                               onblur="registration_value_check('email')" 
                                               onkeyup="registration_value_check('email', $('reg_email').value, 0)" 
                                               value="<?= stripslashes($registration->email);?>" 
                                               size="80" 
                                               id="reg_email" 
                                               name="email" 
                                               class="b-combo__input-text"
                                               placeholder="Email"/>
                                        <label class="b-combo__label" for="reg_email"></label>
                                    </div>
                                </div>

                                <div class="b-layout__txt 
                                     b-layout__txt_left 
                                     b-layout__txt_float_left 
                                     b-layout__txt_padtop_5 
                                     b-layout__txt_color_c10600 
                                     b-layout__txt_error 
                                     b-layout__txt_error_right_desktop 
                                     <?=($registration->error['email'] ? '' : 'b-shadow_hide')?>"
                                     id="error_email">
                                     <span class="b-icon b-icon_sbr_rattent"></span>
                                     <span id="error_txt_email"><?= $registration->error['email']?></span>
                                </div>
                            </td>
                        </tr>

                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_relative b-layout__td_width_full_ipad">
                                <div class="b-combo b-combo_large">
                                    <div class="b-combo__input b-eye <?= $registration->error['password']?"b-combo__input_error":""?>"> 
                                        <a onclick="show_password()" href="javascript:void(0)" class="b-eye__link b-eye__link_right_null " tabindex="10000"><span class="b-eye__icon b-eye__icon_close"></span></a>
                                        <input type="password" 
                                               onblur="registration_value_check('password');" 
                                               onfocus="$$('#error_password').addClass('b-shadow_hide');" 
                                               onkeyup="registration_value_check('password', this.value, 0);"
                                               value="" 
                                               size="80" 
                                               id="reg_password" 
                                               name="password" 
                                               class="b-combo__input-text" 
                                               autocomplete="off" 
                                               placeholder="Пароль"/>
                                        <label class="b-combo__label" for="reg_password"></label>
                                    </div>
                                </div>

                                <div class="b-layout__txt 
                                     b-layout__txt_left 
                                     b-layout__txt_float_left 
                                     b-layout__txt_padtop_5 
                                     b-layout__txt_color_c10600 
                                     b-layout__txt_error 
                                     b-layout__txt_error_right_desktop 
                                     <?=($registration->error['password'] ? '' : 'b-shadow_hide')?>"
                                     id="error_password">
                                     <span class="b-icon b-icon_sbr_rattent"></span>
                                     <span id="error_txt_password"><?= $registration->error['password']?></span>
                                </div>
                            </td>
                        </tr>                 


                        <tr class="b-layout__tr <?=($registration->error['captcha'] ? '' : 'g-hidden')?>" data-captcha-block="true">
                            <td class="b-layout__td b-layout__td_left b-layout__td_padbot_37 b-layout__td_relative b-layout__td_width_full_ipad">
                                <div class="b-captcha">
                                    <table class="b-layout__table b-layout__table_width_full">
                                        <tr class="b-layout__tr">
                                            <td class="b-layout__td_width_140">
                                                <img width="110" height="58" 
                                                     class="b-captcha__img b-captcha__img_bord_ebe8e8" 
                                                     id="rndnumimage" 
                                                     src="/image.php?num=<?=$registration->captchanum?>" 
                                                     alt="" 
                                                     onClick="$('rndnumimage').set('src','/image.php?num='+$('captchanum').get('value')+'&r='+Math.random());"/>
                                                <div class="b-captcha__txt b-captcha__txt_inline-block b-layout__txt_padtop_20">&nbsp;&rarr;&nbsp;</div>                                                        
                                            </td>
                                            <td>
                                                <div class="b-combo b-combo_inline-block b-combo_large">
                                                    <div class="b-combo__input <?= $registration->error['captcha']?"b-combo__input_error":""?>" id="error_captchanum">
                                                        <input id="captchanum" name="captchanum" type="hidden" value="<?=$registration->captchanum?>" />
                                                        <input <?= ($registration->error['captcha']?'title="'. $registration->error['captcha'] .'"':"")?> 
                                                            data-ga-event="{ec: 'user', ea: 'registration_captcha_edited',el: ''}" 
                                                            class="b-combo__input-text b-combo__input-text_center" 
                                                            id="reg_rndnum" 
                                                            name="rndnum" 
                                                            type="text" 
                                                            size="80" 
                                                            autocomplete="off" 
                                                            onkeyup="registration_value_check('rndnum', $('reg_rndnum').value, 0);" 
                                                            onblur="registration_value_check('rndnum');" 
                                                            onfocus="clearCaptchaError();" 
                                                            onKeyPress="clear_error('reg_rndnum'); if(event.keyCode==13) { $('frm').submit(); }" 
                                                            maxlength="5"/>
                                                        <label class="b-combo__label" for="reg_rndnum"></label>
                                                    </div>
                                                </div>                                                        
                                            </td>
                                        </tr>
                                    </table>
                                    <div class="b-captcha__txt b-captcha__txt_padtop_5">
                                        <a onclick="return updateCaptchaImage();" href="javascript:void(0)" class="b-captcha__link">Обновить картинку</a>
                                    </div>
                                </div>

                                <div class="b-layout__txt 
                                     b-layout__txt_left 
                                     b-layout__txt_float_left 
                                     b-layout__txt_padtop_5 
                                     b-layout__txt_color_c10600 
                                     b-layout__txt_error 
                                     b-layout__txt_error_right_desktop 
                                     <?=($registration->error['captcha'] ? '' : 'b-shadow_hide')?>"
                                     id="captcha_error">
                                     <span class="b-icon b-icon_sbr_rattent"></span>
                                     <span id="error_txt_captchanum"><?= $registration->error['captcha']?></span>
                                </div>
                            </td>
                        </tr>

                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padbot_10 b-layout__td_relative b-layout__td_width_full_ipad">
                                <div class="b-buttons"> 
                                    <button type="submit" 
                                            class="b-button 
                                                   b-button_flat 
                                                   b-button_flat_green 
                                                   b-button_flat_large 
                                                   b-button_flat_width_full 
                                                   b-button_disabled <?php if($customer_wizard): ?>b-button_flat_normal<?php endif; ?>" 
                                            id="send_btn" 
                                            data-ga-event="{ec: 'user', ea: 'registration_regbutton1_clicked',el: ''}" 
                                            onclick="formSubmit(); return false;">
                                        Зарегистрироваться
                                        <?php if($customer_wizard): ?>
                                        <br/>и опубликовать проект    
                                        <?php endif; ?>
                                    </button>
                                    <div class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_fontsize_12 b-layout__txt_color_838383">
                                        Нажимая на кнопку &laquo;Зарегистрироваться&raquo;, я соглашаюсь с 
                                        <a href="/about/agreement_site.pdf" class="b-txt__lnk b-txt__lnk_color_0f71c8 b-txt__lnk_underline" target="_blank">публичной офертой ООО «Ваан»</a> 
                                        и <a href="/about/appendix_2_regulations.pdf" class="b-txt__lnk b-txt__lnk_color_0f71c8 b-txt__lnk_underline" target="_blank">правилами сайта</a>.
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_relative b-layout__td_width_full_ipad">
                                <div class="b-check">
                                    <input id="subscribe" class="b-check__input" name="subscribe" type="checkbox" value="1" checked="checked" />
                                    <label for="subscribe" class="b-check__label b-check__label_fontsize_12 b-layout__txt_color_838383">
                                        Получать новости и рассылки от команды FL.ru
                                    </label>
                                </div>                            
                            </td>
                        </tr>                        
                        
                    </tbody>
                </table>

            </div>    
            
            <h2 class="b-layout__title b-layout__title_lh_1 b-layout__title_fs24 b-layout__title_color_333 b-layout__title_padbot_25">
                 Быстрая регистрация
            </h2>  
            
            <div class="b-layout__txt b-layout__txt_padbot_65">
                <?php
                    view_social_buttons(false, array(
                        'vkontakte' => 'data-ga-event="{ec: \'user\', ea: \'registration_socialnet_clicked\',el: \'vk\'}"',
                        'facebook' => 'data-ga-event="{ec: \'user\', ea: \'registration_socialnet_clicked\',el: \'fb\'}"',
                        'odnoklassniki' => 'data-ga-event="{ec: \'user\', ea: \'registration_socialnet_clicked\',el: \'od\'}"'
                    ));
                ?>
                <?php if (isset($_SESSION['opauth_error']) && $_SESSION['opauth_error']): ?>
                    <div class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_color_c4271f">
                        <?=$_SESSION['opauth_error']?>
                    </div>
                    <?php unset($_SESSION['opauth_error']); ?>
                <?php endif; ?>
            </div>
            
            <div class="b-layout__txt">
                <a href="/login/" class="b-layout__link b-layout__link_fontsize_18 b-layout__link_no-decorat">
                    У меня есть аккаунт
                </a>
            </div>
        </div>
        
        <?php endif; ?>
        

    </form>
</div>