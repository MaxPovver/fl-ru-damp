<script type="text/javascript">
window.addEvent('domready', 
    function() {
        <? if($registration->is_post) { ?>
        $('form_<?= $ukey?>').submit();        
        <? }//if?>
        $$('.b-eye-enable').addEvent("click", function(){
            info_for_reg(this);
        });
    }
);
function error_clear(obj) {
    $(obj).getParent().removeClass('b-combo__input_error');
    var error = $(obj).getParent('.b-layout__middle').getElement('.b-layout-error');
    if(error != undefined) error.dispose();
}
function tplInfoPhp_clearDate() {
    $('bday').value = '';
    $('byear').value = '';
    ComboboxManager.setDefaultValue("bmonth", "января", 1);
    return false;
}
function tplInfoPhp_clearCountry() {
	ComboboxManager.setDefaultValue("country", "Все страны", 0);	
	ComboboxManager.getInput("city").clear(0, 0, 1);
	ComboboxManager.setDefaultValue("city", "Все города", 0);
}
function tplInfoPhp_clearCity() {
	ComboboxManager.setDefaultValue("city", "Все города", 0);
}
function tplInfoPhp_leapYear(year) {
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
function tplInfoPhp_onChangeMonth(evt, callFromYear) {
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
        if (tplInfoPhp_leapYear(y)) {
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

<?php 
if($registration->is_post) {
    echo $registration->generateFormPost($ukey);
    $registration->clearPostForm($ukey);
    exit;
} 
?>

<div class="b-layout">
    <div class="b-layout__right b-layout__right_width_73ps b-layout__right_float_right">
        <h1 class="b-page__title b-page__title_padbot_30">Личная информация</h1>
    </div>
    <div class="b-layout__right b-layout__right_width_73ps b-layout__right_float_right">
        <?php if($_SESSION['confirm_info']) {?>
        <div class="b-fon b-fon_padbot_20 b-fon_inline-block">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
                <span class="b-fon__attent_pink"></span><?= $_SESSION['confirm_info'];?>
            </div>
        </div>
        <?php } else {//if?>
        <div class="b-fon b-fon_inline-block b-fon_padbot_20">
            <div class="b-fon__body b-fon__body_pad_15  b-fon__body_padleft_30 b-fon__body_lineheight_18 b-fon__body_padright_40 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                <? if (BLOGS_CLOSED == false) { ?>
                <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Заполните либо сейчас, либо позже личную информацию. Она потребуется как только вы захотите <?= is_emp() ? 'создать проект' : 'ответить на любой проект' ?>, опубликовать пост в блогах или сообществах, написать комментарий. 
                <? } else { ?>
                <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Заполните либо сейчас, либо позже личную информацию. Она потребуется как только вы захотите <?= is_emp() ? 'создать проект' : 'ответить на любой проект' ?>, опубликовать пост в сообществе, написать комментарий. 
                <? } ?>
            </div>
        </div>	
        <?php }//else?>
        <form method="POST" id="form_info" name="form_info">
            <input type="hidden" name="action" value="<?= registration::ACTION_SAVE_INFO?>">
            <div class="b-layout b-layout_margleft_-110">
                <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_4">Ваше имя</div></td>
                        <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270">
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_width_260 <?= $registration->error['uname'] ? "b-combo__input_error" : ""?>">
                                    <input type="text" class="b-combo__input-text" id="uname" name="uname" size="80" value="<?= stripslashes($registration->uname)?>" onclick="error_clear(this);">
                                </div>
                            </div>
                            <?php if($registration->error['uname']) {?>
                                <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10 b-layout-error"><span class="b-form__error"></span><?= stripslashes($registration->error['uname'])?></div>
                            <?php }//if?>
                        </td>                        
                        <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center i-button">
                            <a class="b-button b-button_admin_del" href="javascript:void(0)" onclick="$('uname').value='';"></a>
						</td>
                        <td class="b-layout__right b-layout__right_padbot_20">&nbsp;
                            
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_4">Фамилия</div></td>
                        <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270">
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_width_260 <?= $registration->error['usurname'] ? "b-combo__input_error" : ""?>">
                                    <input type="text" class="b-combo__input-text" id="usurname" name="usurname" size="80" value="<?= stripslashes($registration->usurname)?>" onclick="error_clear(this);">
                                </div>
                            </div>
                            <?php if($registration->error['usurname']) {?>
                                <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10 b-layout-error"><span class="b-form__error"></span><?= stripslashes($registration->error['usurname'])?></div>
                            <?php }//if?>
                        </td>
                        <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center i-button">
                        	<a class="b-button b-button_admin_del" href="javascript:void(0)" onclick="$('usurname').value='';"></a>
                        </td>
                        <td class="b-layout__right b-layout__right_padbot_20">&nbsp;
                            
                        </td>
                    </tr>
                    <?
                    $birthday_day = (($registration->birthday == '1910-01-01')||(!$registration->birthday))  ? '' : date('d', strtotime($registration->birthday));
                    $birthday_mon = (($registration->birthday == '1910-01-01')||(!$registration->birthday))  ? 1 : date('m', strtotime($registration->birthday));
                    $birthday_mon = $birthday_mon ? $birthday_mon : 1;
                    $birthday_year = (($registration->birthday == '1910-01-01')||(!$registration->birthday)) ? '' : date('Y', strtotime($registration->birthday));
                    ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_4">День рождения</div></td>
                        <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270">
                            <div class="b-combo b-combo_inline-block b-combo_margright_5">
                                <div class="b-combo__input b-combo__input_width_45  numeric_max_31 numeric_min_1">
                                    <input type="text" id="bday" class="b-combo__input-text b-combo__input-text_fontsize_15" maxlength="2" name="bday" size="80" value="<?= $birthday_day ?>" onfocus="error_clear(this);">
                                    <label class="b-combo__label" for="bday"></label>
                                </div>

                            </div>
                            <div class="b-combo b-combo_inline-block b-combo_margright_5 b-combo_ie7_margright_4">
                                <div class="b-combo__input b-combo__input_width_125 b-combo__input_arrow_yes b-combo__input_multi_dropdown b-combo__input_init_month drop_down_default_<?= $birthday_mon ?> multi_drop_down_default_column_0 noblur_onenter">
                                    <input type="text" id="bmonth" class="b-combo__input-text b-combo__input-text_fontsize_15" name="bmonth" size="80" value="<?= monthtostr($birthday_mon, true); ?>" onchange="tplInfoPhp_onChangeMonth()">
                                    <label class="b-combo__label" for="bmonth"></label>
                                    <span class="b-combo__arrow" id="bmonth_arrow"></span>
                                </div>
                            </div>
                            <div class="b-combo b-combo_inline-block" >
                                <div class="b-combo__input b-combo__input_width_60 numeric_max_2010 numeric_min_1945">
                                    <input type="text" id="byear" class="b-combo__input-text b-combo__input-text_fontsize_15" maxlength="4" name="byear" size="80" value="<?= $birthday_year ?>" onfocus="error_clear(this);" onkeyup="tplInfoPhp_onChangeMonth(event, 1)">
                                    <label class="b-combo__label" for="byear"></label>
                                </div>
                            </div>
                            <?php if($registration->error['birthday']) {?>
                                <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10 b-layout-error"><span class="b-form__error"></span><?= $registration->error['birthday']?></div>
                            <?php }//if?>
                        </td>
                        <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center i-button">
                            <a class="b-button b-button_admin_del" href="javascript:void(0)" onclick="tplInfoPhp_clearDate()"></a>
                        </td>
                        <td class="b-layout__right b-layout__right_padbot_20">
                            <div class="b-eye b-eye_inline-block">
                                <a href="javascript:void(0)" class="b-eye__link <?= $registration->info_for_reg['birthday'] == 1 ? "b-eye__link_bordbot_dot_808080"  : "b-eye__link_bordbot_dot_0f71c8"?> b-eye-enable">
                                    <?/* #0024237 <span class="b-eye__icon <?= $registration->info_for_reg['birthday'] == 1 ? "b-eye__icon_close"  : "b-eye__icon_open"?> b-eye__icon_margright_5"></span><span class="b-eye__txt"><?= $registration->info_for_reg['birthday'] == 1 ? "Видят только зарегистрированные" : "Видят все"?></span> */?>
                                </a>
                                <input type="hidden" name="info_for_reg[birthday]" value="<?= $registration->info_for_reg['birthday'] == 1 ? $registration->info_for_reg['birthday'] : 0?>">
                            </div>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt">Пол</div></td>
                        <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270">
                            <div class="b-radio b-radio_layout_horizontal">
                                <div class="b-radio__item b-radio__item_width_100">
                                    <input type="radio" value="1" <?= $registration->sex === '1'?"checked":""?> name="sex"  class="b-radio__input" id="b-radio__input4" onfocus="error_clear(this)">
                                    <label for="b-radio__input4" class="b-radio__label b-radio__label_fontsize_13">Мужской</label>
                                </div>
                                <div class="b-radio__item b-radio__item_width_100">
                                    <input type="radio" value="0" <?= $registration->sex === '0'?"checked":""?> name="sex" class="b-radio__input" id="b-radio__input5" onfocus="error_clear(this)">
                                    <label for="b-radio__input5" class="b-radio__label b-radio__label_fontsize_13">Женский</label>
                                </div>
                            </div>
                            <?php if($registration->error['sex']) {?>
                                <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10 b-layout-error"><span class="b-form__error"></span><?= $registration->error['sex']?></div>
                            <?php }//if?>
                        </td>
                        <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center">&#160;</td>
                        <td class="b-layout__right b-layout__right_padbot_20">
                            <div class="b-eye b-eye_inline-block">
                                <a href="javascript:void(0)" class="b-eye__link <?= $registration->info_for_reg['sex']== 1 ? "b-eye__link_bordbot_dot_808080"  : "b-eye__link_bordbot_dot_0f71c8"?> b-eye-enable">
                                    <?/* #0024237 <span class="b-eye__icon <?= $registration->info_for_reg['sex']== 1 ? "b-eye__icon_close"  : "b-eye__icon_open"?> b-eye__icon_margright_5"></span><span class="b-eye__txt"><?= $registration->info_for_reg['sex'] == 1 ? "Видят только зарегистрированные" : "Видят все"?></span> */?>
                                </a>
                                <input type="hidden" name="info_for_reg[sex]" value="<?= $registration->info_for_reg['sex'] == 1 ? $registration->info_for_reg['sex'] : 0?>">
                            </div>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_4">Страна</div></td>
                        <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270">
                            <div class="b-combo b-combo_inline-block">
                                <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_245 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_on_load_request_id_getrelevantcountries  b-combo__input_max-width_450 all_value_id_0_0_Все+страны exclude_value_1_0 drop_down_default_<?= $registration->country ? $registration->country : 0?> multi_drop_down_default_column_0 <?= $registration->error['country'] ? "b-combo__input_error" : ""?>">
                                    <input type="text" id="country" class="b-combo__input-text b-combo__input-text_fontsize_15" name="country" size="80" value="<?= $registration->country == 0 ? "" : stripslashes($registration->country_name)?>" onchange="loadCities()" onfocus="error_clear(this);error_clear('city');" />
                                    <label class="b-combo__label" for="country"></label>
                                    <span class="b-combo__arrow"></span>
                                </div>
                            </div>
                            <?php if($registration->error['country']) {?>
                                <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10 b-layout-error"><span class="b-form__error"></span><?= stripslashes($registration->error['country'])?></div>
                            <?php }//if?>
                        </td>
                        <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center i-button">
                            <a href="javascript:void(0)" onclick="tplInfoPhp_clearCountry()" class="b-button b-button_admin_del"></a>
                        </td>
                        <td class="b-layout__right b-layout__right_padbot_20">
                            <div class="b-eye b-eye_inline-block">
                                <a href="javascript:void(0)" class="b-eye__link <?= $registration->info_for_reg['country']== 1 ? "b-eye__link_bordbot_dot_808080"  : "b-eye__link_bordbot_dot_0f71c8"?> b-eye-enable">
                                    <?/* #0024237 <span class="b-eye__icon <?= $registration->info_for_reg['country']== 1 ? "b-eye__icon_close"  : "b-eye__icon_open"?> b-eye__icon_margright_5"></span><span class="b-eye__txt"><?= $registration->info_for_reg['country'] == 1 ? "Видят только зарегистрированные" : "Видят все"?></span> */?>
                                </a>
                                <input type="hidden" name="info_for_reg[country]" value="<?= $registration->info_for_reg['country'] == 1 ? stripslashes($registration->info_for_reg['country']) : 0?>">
                            </div>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_110"><div class="b-layout__txt b-layout__txt_padtop_4">Город</div></td>
                        <td class="b-layout__middle b-layout__middle_padbot_20 b-layout__middle_width_270" id="city_content">
                            <div class="b-combo b-combo_inline-block">
                                <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_245 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_max-width_450 all_value_id_0_0_Все+города drop_down_default_<?= $registration->city ? $registration->city : 0?> multi_drop_down_default_column_0 <?= $registration->error['city'] ? "b-combo__input_error" : ""?> <?= $registration->country > 0 ? "b-combo__input_on_load_request_id_getcitiesbyid?id=".$registration->country : "" ?>" >
                                    <input type="text" id="city" class="b-combo__input-text b-combo__input-text_fontsize_15" name="city" size="80" value="<?= $registration->city == 0 ? "" : $registration->city_name?>" onfocus="error_clear(this);" />
                                    <label class="b-combo__label" for="city"></label>
                                    <span class="b-combo__arrow" id="city_arrow"></span>
                                </div>
                            </div>
                            <?php if($registration->error['city']) {?>
                                <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10 b-layout-error"><span class="b-form__error"></span><?= stripslashes($registration->error['city'])?></div>
                            <?php }//if?>
                        </td>
                        <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_30 b-layout__one_center i-button">
                            <a href="javascript:void(0)" onclick="tplInfoPhp_clearCity()" class="b-button b-button_admin_del"></a>
                        </td>
                        <td class="b-layout__right b-layout__right_padbot_20">
                            <div class="b-eye b-eye_inline-block">
                                <a href="javascript:void(0)" class="b-eye__link <?= $registration->info_for_reg['city'] == 1 ? "b-eye__link_bordbot_dot_808080"  : "b-eye__link_bordbot_dot_0f71c8"?> b-eye-enable">
                                    <?/* #0024237 <span class="b-eye__icon <?= $registration->info_for_reg['city'] == 1 ? "b-eye__icon_close"  : "b-eye__icon_open"?> b-eye__icon_margright_5"></span><span class="b-eye__txt"><?= $registration->info_for_reg['city'] == 1 ? "Видят только зарегистрированные" : "Видят все"?></span> */?>
                                </a>
                                <input type="hidden" name="info_for_reg[city]" value="<?= $registration->info_for_reg['city'] == 1 ? $registration->info_for_reg['city'] : 0?>">
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
        <div class="b-buttons b-buttons_padbot_100 b-buttons_padtop_20">
            <a href="javascript:void(0)" onclick="$('form_info').submit();" class="b-button b-button_rectangle_color_green">
                <span class="b-button__b1">
                    <span class="b-button__b2 b-button__b2_padlr_15">
                        <span class="b-button__txt">Сохранить</span>
                    </span>
                </span>
            </a>&#160;&#160;
            <?/*<span class="b-buttons__txt">&#160;или&#160;</span>
            <a href="<?= $_SESSION['link_back'] ? $_SESSION['link_back'] : "/"?>" class="b-buttons__link">заполнить позже</a>*/?>
        </div>
    </div>
</div>