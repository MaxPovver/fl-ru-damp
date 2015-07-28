<?php
/**
 * Шаблон попап формы быстрого редактирования работы в портфолио
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ибо нефиг
    exit();
}
?>

<input type="hidden" name="prof" value="<?=$portf['prof_id']?>" />
<input type="hidden" name="user_id" value="<?=$portf['user_id']?>" />
<input type="hidden" name="is_video" id="is_video" value="<?=( $portf['is_video'] == 't' ? '1' : '0' )?>" />

<?=_parseHiddenParams($aParams)?>

<div class="b-menu b-menu_rubric b-menu_padbot_10">
    <ul class="b-menu__list">
        <li id="adm_edit_tab_i1" class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">Основное</span></span></li>
        <li id="adm_edit_tab_i2" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(2); return false;">Файлы</a></li>
        <li id="adm_edit_tab_i3" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(3); return false;">Причина редактирования</a></li>
    </ul>
</div>

<div id="adm_edit_tab_div1">
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Раздел</label>
        <div class="b-input_inline-block b-input_width_545">
            <select id="adm_edit_new_prof" name="new_prof" onchange="adm_edit_content.hideError('prof');" class="b-select__select b-select__select_width_full" tabindex="300">
            <?php foreach ( $profs as $aOne ) {
                if ( $user->is_pro != 't' && ($aOne['prof_id'] == professions::BEST_PROF_ID || $aOne['prof_id'] == professions::CLIENTS_PROF_ID) ) {
                    continue;
                }
                
                $sSelected = $portf['prof_id'] == $aOne['prof_id'] ? ' selected="selected"' : '';
                echo '<option value="'. $aOne['prof_id'] .'" '. $sSelected .'>'. $aOne['name'] .'</option>';
            }
            ?>
            </select>
        </div>
        
        <div id="div_adm_edit_err_prof" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_prof">Поле заполнено некорректно</div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Размещение</label>
        <div class="b-input_inline-block b-input_width_545">
            <div class="b-radio  b-radio_layout_horizontal">
                <div class="b-radio__item">
                    <input id="adm_edit_first" class="b-radio__input" type="radio" name="make_position" value="first" />
                    <label class="b-radio__label" for="adm_edit_first">поставить первой</label>
                </div>
                <div class="b-radio__item">
                    <input id="adm_edit_last" class="b-radio__input" type="radio" name="make_position" value="last" />
                    <label class="b-radio__label" for="adm_edit_last">поставить последней</label>
                </div>
                <div class="b-radio__item">
                    <input id="adm_edit_num" class="b-radio__input" type="radio" name="make_position" value="num" />
                    <label class="b-radio__label" for="adm_edit_num">сделать</label>
                </div>
                <div class="b-input b-input_inline-block b-input_width_60">
                    <input type="text" id="adm_edit_position_num" name="make_position_num" value="" class="b-input__text" maxlength="8" />
                </div>
                <div class="b-radio__item">
                    <label class="b-radio__label">в разделе</label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_pname">Название</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_pname" name="pname" value="<?=input_ref($portf['name'])?>" onfocus="adm_edit_content.hideError('pname');" class="b-input__text" size="80" />
            <label class="b-check__label">Максимум 80 символов</label>
        </div>
        
        <div id="div_adm_edit_err_pname" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_pname">Поле заполнено некорректно</div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <div class="b-form b-form_padtop_10">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_pname">Укажите</label>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3" for="adm_edit_pcost">стоимость</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_pcost" name="pcost" value="<?=$portf['prj_cost']?>" onfocus="adm_edit_content.hideError('pcost');" class="b-input__text" maxlength="10" />
        </div>
        <div class="b-input_inline-block b-input_width_60">
            <select name="pcosttype" id="adm_edit_pcosttype" class="b-select__select b-select__select_width_full">
                <option value="0" <?=($portf['prj_cost_type'] == 0 ? 'selected="selected"' : '')?>>USD</option>
                <option value="1" <?=($portf['prj_cost_type'] == 1 ? 'selected="selected"' : '')?>>Euro</option>
                <option value="2" <?=($portf['prj_cost_type'] == 2 ? 'selected="selected"' : '')?>>Руб</option>
            </select>
        </div>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3" for="adm_edit_ptime">и временные затраты</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_ptime" name="ptime" value="<?=$portf['prj_time_value']?>" onfocus="adm_edit_content.hideError('ptime');" class="b-input__text" maxlength="10" />
        </div>
        <div class="b-input_inline-block b-input_width_100">
            <select name="ptimeei" id="adm_edit_ptimeei" class="b-select__select b-select__select_width_full">
                <option value='0'<? if ($portf['prj_time_type']==0) { ?> selected="selected"<? } ?>>в часах</option>
                <option value='1'<? if ($portf['prj_time_type']==1) { ?> selected="selected"<? } ?>>в днях</option>
                <option value='2'<? if ($portf['prj_time_type']==2) { ?> selected="selected"<? } ?>>в месяцах</option>
                <option value='3'<? if ($portf['prj_time_type']==3) { ?> selected="selected"<? } ?>>в минутах</option>
            </select>
        </div>
        
        <div id="div_adm_edit_err_pcost" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_pcost">Поле заполнено некорректно</div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
        
        <div id="div_adm_edit_err_ptime" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_ptime">Поле заполнено некорректно</div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php if ( $portf['is_video'] == 't' ) { ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Ссылка</label>
        <label class="b-check__label">Вставьте в поле ниже ссылку, которую вы получили на видео хостинге YouTube, RuTube или Vimeo:</label>
    </div>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_video_link">http://</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_video_link" name="v_video_link" value="<?=input_ref($portf['video_link'])?>" onfocus="adm_edit_content.hideError('video_link');" class="b-input__text" size="80" />
        </div>
        <label class="b-check__label b-fon_padleft_80">Внимание! Не используйте html код в поле ввода ссылки.</label>
        
        <div id="div_adm_edit_err_video_link" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_video_link">Поле заполнено некорректно</div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    <?php } else { ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_link">Ссылка</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_link" name="link" value="<?=input_ref($portf['link'])?>" onfocus="adm_edit_content.hideError('link');" class="b-input__text" size="80" />
        </div>
        
        <div id="div_adm_edit_err_link" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_link">Поле заполнено некорректно</div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    <?php } ?>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_msg">Описание</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_msg_source" style="display:none" cols="50" rows="20"><?=input_ref($portf['descr'])?></textarea>
            <textarea id="adm_edit_msg" name="descr" onfocus="adm_edit_content.hideError('descr');" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"></textarea>
            <label class="b-check__label">Максимум 1500 символов. Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;</label>
        </div>
        
        <div id="div_adm_edit_err_descr" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_descr">Поле заполнено некорректно</div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
</div>

<div id="adm_edit_tab_div2" style="display: none;">
    <?php if ( $portf['is_video'] != 't' ) { ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input_inline-block b-input_width_550">
            <div class="b-check b-check_padtop_3">
                <input type="checkbox" class="b-check__input" id="adm_edit_upd_prev" name="upd_prev" value="1" />
                <label class="b-check__label" for="adm_edit_upd_prev">Обновить превью?</label>
            </div>
        </div>
    </div>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Работа</label>
        <iframe style="width:550px;height:45px;" scrolling="no" id="fupload" name="fupload" src="/upload.php?type=work_pict&uid=<?=$portf['user_id']?>" frameborder="0"></iframe>
        <input type="hidden" id="pict" name="pict" value="" />
        <span id="span_pict" style="visibility:hidden;"><a href="<?=WDCPREFIX?>/users/<?=$user->login?>/upload/<?=$portf['pict']?>" class="blue" target="_blank">Посмотреть загруженный файл</a><?php/*&nbsp;&nbsp;<a href="javascript:delpict('pict')" title="Удалить">[x]</a>*/?></span>
        <div class="b-layout__txt b-layout__txt_fontsize_11">Можно загрузить:  файл размером до 10 Мб, флеш-файлы и картинки более 1 Мб открываются в новом окне.  <br />Запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?></div>
    </div>
    <?php } ?>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Превью</label>
        <iframe style="width:550px;height:45px;" scrolling="no" id="fupload" name="fupload" src="/upload.php?type=work_prev&uid=<?=$portf['user_id']?>" frameborder="0"></iframe>
        <input type="hidden" id="prev_pict" name="prev_pict" value="" />
        <span id="span_prev_pict" style="visibility:<?=( $portf['prev_pict'] ? 'visible' : 'hidden' )?>"><a href="<?=WDCPREFIX?>/users/<?=$user->login?>/upload/<?=$portf['prev_pict']?>" class="blue" target="_blank">Посмотреть загруженный файл</a>&nbsp;&nbsp;<input type="checkbox" class="b-check__input" id="adm_edit_del_prev" name="del_prev" value="1" /><label class="b-check__label" for="adm_edit_del_prev">Удалить файл</label></span>
        <div class="b-layout__txt b-layout__txt_fontsize_11">Можно загрузить превью для закачиваемого файла.  <? if($user->is_pro != 't') { ?><br /><strong>Превью отображается только у пользователей с аккаунтом <a href='/payed/' class='ac-pro'><img src='/images/icons/f-pro.png' width='26' height='11' alt='PRO' style='vertical-align:bottom;' /></a></strong><br /><? } ?> Формат: <?=implode(', ', array_diff($GLOBALS['graf_array'], array('swf')) )?>. Максимальный размер файла: 100 Кб.</div>
    </div>
    
    <?php if ( $portf['is_video'] != 't' ) { ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input_inline-block b-input_width_545">
            <div class="b-radio  b-radio_layout_horizontal">
                <div class="b-radio__item">
                    <input id="adm_edit_prev_type1" class="b-radio__input" type="radio" name="prev_type" value="0" <?=( !$portf['prj_prev_type'] ? ' checked="checked"' : '' )?> />
                    <label class="b-radio__label" for="adm_edit_prev_type1">Графическое превью</label>
                </div>
                <div class="b-radio__item">
                    <input id="adm_edit_prev_type2" class="b-radio__input" type="radio" name="prev_type" value="1"  <?=( $portf['prj_prev_type'] ? ' checked="checked"' : '' )?> />
                    <label class="b-radio__label" for="adm_edit_prev_type2">Текстовое превью</label>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

