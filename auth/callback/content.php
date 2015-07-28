<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/wizard.common.php");
$xajax->printJavascript('/xajax/');

?>
<div class="b-layout g-txt_center">
    <form method="post" action="" id="form-opauth">
        <?php if($redirectUri): ?>
        <input type="hidden" name="redirect" value="<?=$redirectUri?>" />
        <?php endif; ?>
        
        <div class="b-layout b-layout_padtop_45">
            
            <h1 class="b-layout__title b-layout__title_lh_1 b-layout__title_fs30 b-layout__title_color_333 b-layout__title_padbot_40">
                Ваши данные для регистрации на FL.ru
            </h1>            
            
            <div class="b-layout b-layout_inline-block b-layout_width_330 b-layout_width_full_iphone">
                <table class="b-layout__table b-layout__table_width_full">
                    <tbody>
                        <?php if(!isset($emp_redirect) || empty($emp_redirect)): ?>
                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_relative b-layout__td_width_full_ipad">
                                
                                <div class="b-combo b-combo_large">
                                    <div class="
                                         b-combo__input 
                                         b-combo__input_multi_dropdown 
                                         b-combo__input_init_roleList 
                                         show_all_records 
                                         b-combo__input_resize 
                                         multi_drop_down_default_column_0  
                                         drop_down_default_<?= $registrationData['role'] ?> 
                                         b-combo__input_arrow_yes 
                                         disallow_null">
                                        <input class="b-combo__input-text b-combo__input-text_pointer" 
                                               value="" 
                                               id="role" 
                                               name="role" 
                                               type="text" 
                                               size="80" 
                                               readonly="readonly"/>
                                        <label for="role" class="b-combo__label"></label>
                                        <span class="b-combo__arrow"></span>
                                    </div>
                                </div>
                                
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_relative b-layout__td_width_full_ipad">
                                <div class="b-combo b-combo_large">
                                    <div class="b-combo__input <?= $registration->error['email']?"b-combo__input_error":""?>">
                                        <input data-ga-event="{ec: 'user', ea: 'registration_form_edited',el: ''}" 
                                               type="text" 
                                               value="<?= stripslashes($registrationData['email']); ?>" 
                                               size="80" 
                                               id="reg_email" 
                                               name="email" 
                                               class="b-combo__input-text"
                                               placeholder="Почта"/>
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
                                    <div class="b-combo__input <?= $registration->error['login']?"b-combo__input_error":""?>">
                                        <input data-ga-event="{ec: 'user', ea: 'registration_login_edited',el: ''}" 
                                               type="text" 
                                               maxlength="15" 
                                               onfocus="$$('#error_login').addClass('b-shadow_hide');" 
                                               value="<?=$registrationData['login']?>" 
                                               size="80" 
                                               id="reg_login" 
                                               name="login" 
                                               class="b-combo__input-text" 
                                               autocomplete="off" 
                                               placeholder="Логин"/>
                                        <label class="b-combo__label" for="reg_login"></label>
                                    </div>
                                </div>

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
                                    <button id="opauth-save-btn"
                                            data-ga-event="{ec: 'user', ea: 'registration_regbutton2_clicked',el: ''}" 
                                            class="b-button b-button_flat b-button_flat_green b-button_flat_large b-button_flat_width_full">

                                        Зарегистрироваться
                                    </button>
                                    <div class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_fontsize_12 b-layout__txt_color_838383">
                                        Нажимая на кнопку &laquo;Зарегистрироваться&raquo;, я соглашаюсь с 
                                        <a href="/about/agreement_site.pdf" class="b-txt__lnk b-txt__lnk_color_0f71c8 b-txt__lnk_underline" target="_blank">публичной офертой ООО «Ваан»</a> 
                                        и <a href="/about/appendix_2_regulations.pdf" class="b-txt__lnk b-txt__lnk_color_0f71c8 b-txt__lnk_underline" target="_blank">правилами сайта</a>.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>            
            
        </div>
        
    </form>
</div>