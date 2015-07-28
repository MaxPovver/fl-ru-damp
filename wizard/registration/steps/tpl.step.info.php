<script type="text/javascript">
window.addEvent('domready', 
    function() {
        $$('.b-eye-enable').removeEvents("click").addEvent("click", function(){
            info_for_reg(this);
        });
        
        $$('.b-button_admin_del').addEvent("click", function() {
            delete_field_info(this);
        });
        
        <?php if($country>0) { ?>
            loadCities(1);
        <?php } ?>
            
        <?php if($info) foreach($info as $name=>$inf) {?>
            <?php foreach($inf as $i=>$value) { ?>
            add_option_field($('option_<?=$name?>').getElement('a.b-button_poll_plus'), 
                             '<?=$name?>', 
                             { value: '<?= html_entity_decode($value, ENT_QUOTES);?>', 
                               info_for_reg: '<?= $info_for_reg[$name . "_{$i}"]?>',
                               error: '<?= !$i ? $error[$name] : $error[$name . "_{$i}"]?>'});    
            <?php }//foreach?>
            clear_empty_field('<?= $name?>');
        <?php }//foreach?>
    }
);
function tplStepInfoPhp_leapYear(year) {
    year = Number(year);
    var r = false;
    var y = year;
    if (y % 4 == 0) {
        if (y % 100 == 0){
		    if (y % 400 == 0) return true;
            return false;
        }
        return true;
    }
    return false;
}
function tplStepInfoPhp_onChangeMonth(evt, callFromYear) {
    if (callFromYear) {
        if ($('byear').value.length < 4) {
            return;
        }
        var max = parseInt($('byear').get('max'));
        if ($('byear').value > max) {
        	$('byear').value = max;
        }
        var min = parseInt($('byear').get('min'));
        if ($('byear').value < min) {
            $('byear').value = min;
        }
    }
    var y = parseInt($('byear').value);
    var m = parseInt($('bmonth_db_id').value);
    var qDay  = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    if (m) {
        if (tplStepInfoPhp_leapYear(y)) {
            qDay[1] = 29;
        }
        var n = qDay[--m];        
        $("bday").set("max", n);
        if (parseInt($("bday").value) > n) {
        	$("bday").value = n;
        }
    } 
}
</script>
<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
    <form method="POST" id="frm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upd_info">
        <? if (!$_COOKIE['master_auth']) { ?>
        <div class="b-fon b-fon_inline-block b-fon_padbot_20" id="wizard_reg_succ">
            <div class="b-fon__body b-fon__body_pad_15  b-fon__body_padleft_30 b-fon__body_padright_40 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_nowrap">
                <span class="b-fon__ok"></span>Вы успешно зарегистрированы. Теперь вы — часть Free-lance.ru :) 
            </div>
            <span class="b-fon__close b-fon__close_top_20" id="wizard_reg_succ_close"></span>
        </div>
        <? } ?>
        
        <?php if($type_role == step_wizard_registration::TYPE_WIZARD_EMP) { ?>
        <div class="b-layout__txt b-layout__txt_padbot_40">Free-lance.ru – это не только поиск исполнителей, но и большое сообщество пользователей, работодателей и фрилансеров, готовых поделиться опытом, дать совет в трудной ситуации или ответить на все ваши вопросы. Для того чтобы общение на сайте было более продуктивным, пожалуйста, заполните информацию о себе.</div>		
        <?php } else { //if?>
        <div class="b-layout__txt b-layout__txt_padbot_40">Free-lance.ru – это не только поиск работы, но и большое сообщество пользователей, работодателей и фрилансеров, готовых поделиться опытом, дать совет в трудной ситуации или ответить на все ваши вопросы. Для того чтобы общение на сайте было более продуктивным, пожалуйста, заполните информацию о себе.</div>
        <?php }//else?>
        
        <h2 class="b-layout__title b-layout__title_padbot_20">Личная информация</h2>
        <? if ( $error['save'] ) { ?>
        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
            <span class="b-form__error"></span> <?= $error['save']?>
        </div>
        <? } ?>
        
        <div class="b-layout b-layout_margleft_-110">
            <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_4">Ваше имя</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_260">
                                <input type="text" class="b-combo__input-text" name="uname" size="80" value="<?= stripslashes($uname)?>" onfocus="clearErrorBlock(this)">
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center">&#160;</td>
                    <td class="b-layout__right b-layout__right_padbot_20">
                        <div class="b-eye_inline-block">
                           <span class="b-eye__icon b-eye__icon_open b-eye__icon_margright_5"></span><span class="b-eye__txt b-eye__txt_fontsize_11">Эту информацию видят все</span>
                        </div>
                    </td>
                </tr>
                <? if ( $error['uname'] ) { ?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55"></td>
                    <td colspan="3" class="">
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                            <span class="b-form__error"></span> <?= $error['uname']?>
                        </div>
                    </td>
                </tr>
                <? } ?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_4">Фамилия</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_260">
                                <input type="text" class="b-combo__input-text" name="usurname" size="80" value="<?= stripslashes($usurname);?>" onfocus="clearErrorBlock(this)">
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center">&#160;</td>
                    <td class="b-layout__right b-layout__right_padbot_20">
                        <div class="b-eye_inline-block">
                           <span class="b-eye__icon b-eye__icon_open b-eye__icon_margright_5"></span><span class="b-eye__txt b-eye__txt_fontsize_11">Эту информацию видят все</span>
                        </div>
                    </td>
                </tr>
                <? if ( $error['usurname'] ) { ?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55"></td>
                    <td colspan="3" class="">
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                            <span class="b-form__error"></span> <?= $error['usurname']?>
                        </div>
                    </td>
                </tr>
                <? } ?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_4">День рождения</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270">
                        <div class="b-combo b-combo_inline-block b-combo_margright_5">
                            <div class="b-combo__input b-combo__input_width_25 numeric_min_1 numeric_max_31">
                                <input type="text" id="bday" class="b-combo__input-text b-combo__input-text_fontsize_15" maxlength="2" name="bday" size="80" value="<?= stripslashes($bday);?>" onfocus="clearErrorBlock(this)">
                                <label class="b-combo__label" for="bday"></label>
                            </div>

                        </div>
                        <div class="b-combo b-combo_inline-block b-combo_margright_5 b-combo_ie7_margright_4">
                            <div class="b-combo__input b-combo__input_width_110 b-combo__input_arrow_yes b-combo__input_multi_dropdown b-combo__input_init_month drop_down_default_<?= $bmonth ? $bmonth : 1?> multi_drop_down_default_column_0 noblur_onenter">
                                <input type="text" id="bmonth" class="b-combo__input-text b-combo__input-text_fontsize_15" name="bmonth" size="80" value="<?= stripslashes($bmonth_value)?>" onchange="tplStepInfoPhp_onChangeMonth()">
                                <label class="b-combo__label" for="bmonth"></label>
                                <span class="b-combo__arrow" id="bmonth_arrow"></span>
                            </div>
                        </div>
                        <div class="b-combo b-combo_inline-block" >
                            <div class="b-combo__input b-combo__input_width_45 numeric_min_1945 numeric_max_2010">
                                <input type="text" id="byear" class="b-combo__input-text b-combo__input-text_fontsize_15" maxlength="4" name="byear" size="80" value="<?= stripslashes($byear)?>" onfocus="clearErrorBlock(this)" onkeyup="tplStepInfoPhp_onChangeMonth(event, 1)">
                                <label class="b-combo__label" for="byear"></label>
                            </div>

                        </div>
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center i-button">
                        <!-- <a href="javascript:void(0)" class="b-button b-button_admin_del"></a> -->
                    </td>
                    <td class="b-layout__right b-layout__right_padbot_20">
                        <div class="b-eye b-eye_inline-block">
                            <a href="javascript:void(0)" class="b-eye__link <?= $info_for_reg['birthday'] == 1 ? "b-eye__link_bordbot_dot_808080"  : "b-eye__link_bordbot_dot_0f71c8"?> b-eye-enable">
                                <span class="b-eye__icon <?= $info_for_reg['birthday'] == 1 ? "b-eye__icon_close"  : "b-eye__icon_open"?> b-eye__icon_margright_5"></span><span class="b-eye__txt"><?= $info_for_reg['birthday'] == 1 ? "Видят только зарегистрированные" : "Видят все"?></span>
                            </a>
                            <input type="hidden" name="info_for_reg[birthday]" value="<?= $info_for_reg['birthday'] == 1 ? $info_for_reg['birthday'] : 0?>">
                        </div>
                    </td>
                </tr>
                <? if ( $error['birthday'] ) { ?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55"></td>
                    <td colspan="3" class="">
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                            <span class="b-form__error"></span> <?= $error['birthday']?>
                        </div>
                    </td>
                </tr>
                <? } ?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt">Пол</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270">
                        <div class="b-radio b-radio_layout_horizontal">
                            <div class="b-radio__item b-radio__item_width_100">
                                <input type="radio" value="1" <?= $sex === 1?'checked="checked"':""?> name="sex"  class="b-radio__input" id="b-radio__input4" onfocus="clearErrorBlock(this)">
                                <label for="b-radio__input4" class="b-radio__label b-radio__label_fontsize_13">Мужской</label>
                            </div>
                            <div class="b-radio__item b-radio__item_width_100">
                                <input type="radio" value="0" <?= $sex === 0?'checked="checked"':""?> name="sex" class="b-radio__input" id="b-radio__input5" onfocus="clearErrorBlock(this)">
                                <label for="b-radio__input5" class="b-radio__label b-radio__label_fontsize_13">Женский</label>
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center">&#160;</td>
                    <td class="b-layout__right b-layout__right_padbot_20">
                        <div class="b-eye b-eye_inline-block">
                            <a href="javascript:void(0)" class="b-eye__link <?= $info_for_reg['sex']== 1 ? "b-eye__link_bordbot_dot_808080"  : "b-eye__link_bordbot_dot_0f71c8"?> b-eye-enable">
                                <span class="b-eye__icon <?= $info_for_reg['sex']== 1 ? "b-eye__icon_close"  : "b-eye__icon_open"?> b-eye__icon_margright_5"></span><span class="b-eye__txt"><?= $info_for_reg['sex'] == 1 ? "Видят только зарегистрированные" : "Видят все"?></span>
                            </a>
                            <input type="hidden" name="info_for_reg[sex]" value="<?= $info_for_reg['sex'] == 1 ? $info_for_reg['sex'] : 0?>">
                        </div>
                    </td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_4">Страна</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270">
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_245 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_on_load_request_id_getrelevantcountries  b-combo__input_max-width_450 all_value_id_0_0_Все+страны exclude_value_1_0 drop_down_default_<?= $country ? $country : 0?> multi_drop_down_default_column_0<?= $error['country'] ? " b-combo__input_error" : "" ?>">
                                <input type="text" id="country" class="b-combo__input-text b-combo__input-text_fontsize_15" name="country" size="80" value="<?= stripslashes($country_value)?>" onchange="loadCities()">
                                <label class="b-combo__label" for="country"></label>
                                <span class="b-combo__arrow"></span>
                            </div>
                        </div>						
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center i-button">
                        <!-- <a href="javascript:void(0)" class="b-button b-button_admin_del"></a> -->
                    </td>
                    <td class="b-layout__right b-layout__right_padbot_20">
                        <div class="b-eye b-eye_inline-block">
                            <a href="javascript:void(0)" class="b-eye__link <?= $info_for_reg['country']== 1 ? "b-eye__link_bordbot_dot_808080"  : "b-eye__link_bordbot_dot_0f71c8"?> b-eye-enable">
                                <span class="b-eye__icon <?= $info_for_reg['country']== 1 ? "b-eye__icon_close"  : "b-eye__icon_open"?> b-eye__icon_margright_5"></span><span class="b-eye__txt"><?= $info_for_reg['country'] == 1 ? "Видят только зарегистрированные" : "Видят все"?></span>
                            </a>
                            <input type="hidden" name="info_for_reg[country]" value="<?= $info_for_reg['country'] == 1 ? $info_for_reg['country'] : 0?>">
                        </div>
                    </td>
                </tr>

                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_4">Город</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270" id="city_content">
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_245 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_max-width_450 override_value_id_0_0_Все+города drop_down_default_<?= $city ? $city : 0?> multi_drop_down_default_column_0<?= $error['city'] ? " b-combo__input_error" : "" ?>">
                                <input type="text" id="city" class="b-combo__input-text b-combo__input-text_fontsize_15" name="city" size="80" value="<?= stripslashes($city_value)?>">
                                <label class="b-combo__label" for="city"></label>
                                <span class="b-combo__arrow" id="city_arrow"></span>
                            </div>
                        </div>						
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center i-button">
                        <!-- <a href="javascript:void(0)" class="b-button b-button_admin_del"></a> -->
                    </td>
                    <td class="b-layout__right b-layout__right_padbot_20">
                        <div class="b-eye b-eye_inline-block">
                            <a href="javascript:void(0)" class="b-eye__link <?= $info_for_reg['city'] == 1 ? "b-eye__link_bordbot_dot_808080"  : "b-eye__link_bordbot_dot_0f71c8"?> b-eye-enable">
                                <span class="b-eye__icon <?= $info_for_reg['city'] == 1 ? "b-eye__icon_close"  : "b-eye__icon_open"?> b-eye__icon_margright_5"></span><span class="b-eye__txt"><?= $info_for_reg['city'] == 1 ? "Видят только зарегистрированные" : "Видят все"?></span>
                            </a>
                            <input type="hidden" name="info_for_reg[city]" value="<?= $info_for_reg['city'] == 1 ? $info_for_reg['city'] : 0?>">
                        </div>
                    </td>
                </tr>
                <?php if($type_role == step_wizard_registration::TYPE_WIZARD_EMP) { ?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_4">Компания</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270">
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_260">
                                    <input type="text" id="company" class="b-combo__input-text b-combo__input-text_fontsize_15" name="company" size="80" value="<?= stripslashes($company)?>" onfocus="clearErrorBlock(this)">
                                    <label class="b-combo__label" for="company"></label>
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30">&#160;</td>
                    <td class="b-layout__right b-layout__right_padbot_20">
                        <div class="b-eye b-eye_inline-block">
                            <a href="javascript:void(0)" class="b-eye__link <?= $info_for_reg['company'] == 1 ? "b-eye__link_bordbot_dot_808080"  : "b-eye__link_bordbot_dot_0f71c8"?> b-eye-enable">
                                <span class="b-eye__icon <?= $info_for_reg['company'] == 1 ? "b-eye__icon_close"  : "b-eye__icon_open"?> b-eye__icon_margright_5"></span><span class="b-eye__txt"><?= $info_for_reg['company']== 1 ? "Видят только зарегистрированные" : "Видят все"?></span>
                            </a>
                            <input type="hidden" name="info_for_reg[company]" value="<?= $info_for_reg['company'] == 1 ? $info_for_reg['company'] : 0?>">
                        </div>
                    </td>
                </tr>
                <?php } //if?>
            </table>
            
            <?php if($type_role == step_wizard_registration::TYPE_WIZARD_EMP) { ?>
            <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_10">Логотип <span class="b-icon b-icon__pro b-icon__pro_e"></span></div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_20">
                        <table class="b-layout__table" border="0" cellpadding="0" cellspacing="0">
                            <tr class="b-layout__tr">
                                <td class="b-layout__left">
                                    <input type="hidden" name="logo_company" id="logo_company" value="<?= $logo_id;?>">
                                    <input type="hidden" name="logo_name" id="logo_name" value="<?= $logo_name;?>">
                                    <div class="b-file__wrap  b-file__wrap_margleft_-3 logo-add-element" <?= $logo_name != '' ? 'style="display:none"' : ''; ?>>
                                        <input type="file" class="b-file__input" name="logo_attach" onchange="uploadLogoFile('logo_company');">
                                        <a class="b-button b-button_rectangle_color_transparent" href="#">
                                            <span class="b-button__b1">
                                                <span class="b-button__b2">
                                                    <span class="b-button__txt">Прикрепить файлы</span>
                                                </span>
                                            </span>
                                        </a>
                                    </div>
                                    <span id="logo_block">
                                        <?php if($logo_name != '') { ?>
                                        <div class="b-layout__txt b-layout__txt_relative b-layout__txt_inline-block">
                                            <a href="javascript:void(0)" onclick="deleteLogo(<?= $logo_id; ?>); return false;" class="b-button b-button_bgcolor_fff b-button_bord_solid_3_fff b-button_admin_del b-button_right_-4 b-button_top_-6"></a>
                                            <a href="<?= $logo_path; ?>" class="b-layout__link">
                                                <img alt="" id="img_logo" src="<?= $logo_path; ?>" class="b-layout__pic b-layout__pic_bord_ece9e9">
                                            </a>
                                        </div>
                                        <?php }//if?>
                                    </span>
                                    <iframe style="width:1px;height:1px;visibility: hidden;" scrolling="no" id="fupload" name="fupload" src="about:blank" frameborder="0"></iframe>
                                </td>
                                <td class="b-layout__right">
                                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_10">Если вы станете обладателем профессионального аккаунта, логотип будет отображаться на вашей личной странице и в каталоге работодателей.</div>
                                </td>
                            </tr>
                        </table>					
                    </td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_1">О компании</div></td>
                    <td class="b-layout__middle b-layout__middle_padbot_20">
                        <div class="b-textarea">
                            <textarea class="b-textarea__textarea b-textarea__textarea_height_70 tawl" rel="350" name="about_company" cols="" rows="" onfocus="clearErrorBlock(this)"><?= stripslashes($about_company);?></textarea>
                        </div>
                    </td>
                </tr>
                <? if ( $error['company'] ) { ?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_55"></td>
                    <td colspan="2" class="">
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                            <span class="b-form__error"></span> <?= $error['company']?>
                        </div>
                    </td>
                </tr>
                <? } ?>
            </table>
            <?php }//if?>
            <?/*
            <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0" id="option_table">
                <tr class="b-layout__tr" id="option_site">
                    <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_110">&#160;</td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_width_270">
                        <div class="b-layout__txt i-button">
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'site')" class="b-button b-button_poll_plus"></a>&nbsp;
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'site')" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle b-layout__link_lineheight_15">Сайт</a>
                        </div>						
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_width_30 b-layout__one_center">&#160;</td>
                    <td class="b-layout__right b-layout__right_padbot_10">&#160;</td>
                </tr>
                <tr class="b-layout__tr" id="option_email">
                    <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_110">&#160;</td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_width_270">
                        <div class="b-layout__txt i-button">
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'email')" class="b-button b-button_poll_plus"></a>&nbsp;
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'email')" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle b-layout__link_lineheight_15">E-mail</a>
                        </div>						
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_width_30 b-layout__one_center">&#160;</td>
                    <td class="b-layout__right b-layout__right_padbot_10">&#160;</td>
                </tr>
                <tr class="b-layout__tr" id="option_phone">
                    <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_110">&#160;</td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_width_270">
                        <div class="b-layout__txt i-button">
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'phone')" class="b-button b-button_poll_plus"></a>&nbsp;
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'phone')" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle b-layout__link_lineheight_15">Телефон</a>
                        </div>						
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_width_30 b-layout__one_center">&#160;</td>
                    <td class="b-layout__right b-layout__right_padbot_10">&#160;</td>
                </tr>
                <tr class="b-layout__tr" id="option_icq">
                    <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_110">&#160;</td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_width_270">
                        <div class="b-layout__txt i-button">
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'icq')" class="b-button b-button_poll_plus"></a>&nbsp;
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'icq')" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle b-layout__link_lineheight_15">ICQ</a>
                        </div>						
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_width_30 b-layout__one_center">&#160;</td>
                    <td class="b-layout__right b-layout__right_padbot_10">&#160;</td>
                </tr>
                <tr class="b-layout__tr" id="option_skype">
                    <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_110">&#160;</td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_width_270">
                        <div class="b-layout__txt i-button">
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'skype')" class="b-button b-button_poll_plus"></a>&nbsp;
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'skype')" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle b-layout__link_lineheight_15">Skype</a>
                        </div>						
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_width_30 b-layout__one_center">&#160;</td>
                    <td class="b-layout__right b-layout__right_padbot_10">&#160;</td>
                </tr>
                <tr class="b-layout__tr" id="option_jabber">
                    <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_110">&#160;</td>
                    <td class="b-layout__middle b-layout__middle_padbot_10 b-layout__middle_width_270">
                        <div class="b-layout__txt i-button">
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'jabber')" class="b-button b-button_poll_plus"></a>&nbsp;
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'jabber')" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle b-layout__link_lineheight_15">Jabber</a>
                        </div>						
                    </td>
                    <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_width_30 b-layout__one_center">&#160;</td>
                    <td class="b-layout__right b-layout__right_padbot_10">&#160;</td>
                </tr>
                <tr class="b-layout__tr" id="option_lj">
                    <td class="b-layout__left b-layout__left_width_110">&#160;</td>
                    <td class="b-layout__middle b-layout__middle_width_270">
                        <div class="b-layout__txt i-button">
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'lj')" class="b-button b-button_poll_plus"></a>&nbsp;
                            <a href="javascript:void(0)" onclick="add_option_field(this, 'lj')" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle b-layout__link_lineheight_15">LiveJournal</a>
                        </div>						
                    </td>
                    <td class="b-layout__one b-layout__one_width_30 b-layout__one_center">&#160;</td>
                    <td class="b-layout__right">&#160;</td>
                </tr>
            </table>
             */ ?>
        </div>
        <?php if($type_role == step_wizard_registration::TYPE_WIZARD_EMP) { ?>
            <?php include ($_SERVER['DOCUMENT_ROOT']."/wizard/registration/steps/tpl.service.emp.php"); ?>
        <?php } else {//?>
            <?php include ($_SERVER['DOCUMENT_ROOT']."/wizard/registration/steps/tpl.service.frl.php"); ?>
        <?php } //if?>
        

        <div class="b-buttons">
            <a href="javascript:void(0)" onclick="$('frm').submit();" class="b-button b-button_rectangle_color_green">
                <span class="b-button__b1">
                    <span class="b-button__b2 b-button__b2_padlr_15">
                        <span class="b-button__txt">Продолжить</span>
                    </span>
                </span>
            </a>&#160;&#160;
            <?/*<a href="/wizard/registration/?action=next&complited=1" class="b-buttons__link">пропустить этот шаг</a>*/?>
            <span class="b-buttons__txt">&#160;или&#160;</span>
            <a href="/wizard/registration/?action=exit" class="b-buttons__link b-buttons__link_color_c10601">выйти из мастера</a>
        </div>

    </form>
</div>