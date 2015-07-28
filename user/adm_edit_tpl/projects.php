<?php
/**
 * Шаблон попап формы быстрого редактирования проектов и конкурсов (пока только файлы)
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ибо нефиг
    exit();
}
?>
<style type="text/css">
#adm_edit_professions {display: inline-block;margin-bottom: -20px;width: 400px;}
#adm_edit_professions select {display: inline;float: left;margin-right: 2px !important;vertical-align: top;width: 180px;}
#adm_edit_professions #category_line a img {margin: -5px 0;border:0;}
#category_line {height: 20px;padding-bottom: 10px;}
</style>

<div class="b-menu b-menu_rubric b-menu_padbot_10">
    <ul class="b-menu__list">
        <li id="adm_edit_tab_i1" class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">Основное</span></span></li>
        <li id="adm_edit_tab_i2" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(2); return false;">Файлы</a></li>
        <li id="adm_edit_tab_i3" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(3); return false;">Платные услуги</a></li>
        <li id="adm_edit_tab_i4" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(4); return false;">Причина редактирования</a></li>
    </ul>
</div>

<input type="hidden" name="temp_key" value="<?=$sTmpKey?>">
<input type="hidden" name="user_id" value="<?=$prj['user_id']?>">
<input type="hidden" name="user_login" value="<?=$prj['login']?>">
<input type="hidden" name="user_uname" value="<?=$prj['uname']?>">
<input type="hidden" name="user_usurname" value="<?=$prj['usurname']?>">

<?=_parseHiddenParams($aParams)?>

<?php // Основное ?>
<div id="adm_edit_tab_div1">
    <?php // Заголовок ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_name">Заголовок</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_name" name="name" value="<?=$prj['name']?>" class="b-input__text" size="80" onfocus="adm_edit_content.hideError('name')">
        </div>
        
        <div id="div_adm_edit_err_name" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_name"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php // Текст ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_descr">Текст</label>
        <div class="b-textarea b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_descr_source" style="display:none" cols="50" rows="20"><?=input_ref($prj['descr'])?></textarea>
            <textarea id="adm_edit_descr" name="descr" class="b-textarea__textarea b-textarea__textarea_width_full b-textarea__textarea__height_70" cols="77" rows="5" onfocus="adm_edit_content.hideError('descr')"></textarea>
        </div>

        <div id="div_adm_edit_err_descr" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_descr"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php // Папка ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Папка</label>
        <div class="b-input_inline-block b-input_width_545">
            <select id="adm_edit_folder_id" name="folder_id" class="b-select__select b-select__select_width_full">
                <option value="0">Корневая</option>
                <?php
                if ( is_array($aFolders) && count($aFolders) ) {
                    foreach ( $aFolders as $aOne ) {
                        $sSelected = ( $aOne['id'] == $prj['folder_id'] ) ? ' selected' : '';
                        echo '<option value="'.$aOne['id'].'" '.$sSelected.'>'.$aOne['name'].'</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>
    
    <?php // Раздел ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Раздел</label>
        <div class="b-input_inline-block b-input_width_545" id="adm_edit_professions">
        <?php 
        foreach ( $project_categories as $project_category ) { ?>
            <div id="category_line">
                <select name="categories[]" class="b-select__select b-select__select_width_180" onchange="adm_edit_content.prjSubCategory(this);adm_edit_content.hideError('categories');">
                    <option value="0">Выберите раздел</option>
            <?php
            foreach ( $categories as $cat ) {
                if ( $cat['id'] <=0 ) {
                    continue;
                }
                ?>
                <option value="<?=$cat['id']?>" <?=($project_category['category_id']==$cat['id'] ? ' selected' : '')?>><?=$cat['name']?></option><?php
            }
            ?>
                </select>
                <select name="subcategories[]" class="b-select__select b-select__select_width_180" onchange="adm_edit_content.hideError('categories')">
                    <option value='0' <?=($project_category['subcategory_id']==0 ? ' selected' : '')?>>Все специализации</option>
            <?php                    
            $categories_specs = $professions[$project_category['category_id']];
            
            for ( $i=0; $i<sizeof($categories_specs); $i++ ) {
                ?><option value="<?=$categories_specs[$i]['id']?>" <?=($categories_specs[$i]['id'] == $project_category['subcategory_id'] ? ' selected' : '')?>><?=$categories_specs[$i]['profname']?></option>
            <?php
            }
            ?>
                </select>
            </div>
        <?php } ?>
        </div>
        
        <div id="div_adm_edit_err_categories" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_categories"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php // Закладка "Проекты/В офис" только для проектов 
    if ( $prj['kind'] != 7 ) {
    ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Закладка</label>
        <div class="b-input_inline-block b-input_width_545">
            <div class="b-check b-check_padtop_3">
                <input id="adm_edit_kind1" class="b-check__input" type="radio" name="kind" value="1" <?=($prj['kind'] == 1 ? 'checked="checked"' : '')?> onchange="adm_edit_content.prjChangeKind()" />
                <label class="b-check__label" for="adm_edit_kind1" id="label_close_comments"><span class="b-radio__bold">Проекты</span> — Разовые проекты с фиксированной оплатой</label>
            </div>
            <div class="b-check b-check_padtop_3">
                <input id="adm_edit_kind4" class="b-check__input" type="radio" name="kind" value="4" <?=($prj['kind'] == 4 ? 'checked="checked"' : '')?> onchange="adm_edit_content.prjChangeKind()" />
                <label class="b-check__label" for="adm_edit_kind4" id="label_is_private"><span class="b-radio__bold">Вакансии</span> — Вакансии на постоянную или попроектную работу в офисе</label>
            </div>
            
            <div id="adm_edit_location" class="b-form_padtop_10" <?=($prj['kind'] == 1 ? 'style="display: none;"' : '')?>>
                <div>
                    <select name="country" onchange="adm_edit_content.prjCityUpd(this.value);adm_edit_content.hideError('country')" class="flt-p-sel">
                        <option value="0">Страна</option>
                        <?php foreach( $countries as $country_id => $country ) { ?>
                        <option value="<?=$country_id?>" <?=($country_id == $prj['country'] ? 'selected' : '')?>><?=$country?></option>
                        <?php } ?>
                    </select>
                </div>
                <div id="frm_city" class="b-form_padtop_10">
                    <select name="pf_city" class="flt-p-sel" onchange="adm_edit_content.hideError('country')">
                        <option value='0'>Город</option>
                        <?php if ( $cities ) {
                            foreach ( $cities as $city_id=> $city ) { ?>
                                <option value="<?=$city_id?>" <?=($city_id == $prj['city'] ? 'selected' : '')?>><?=$city?></option>
                            <?php }
                        } ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div id="div_adm_edit_err_country" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_country"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    <? } ?>
    
    <?php // Даты проведения только для конкурсов 
    if ( $prj['kind'] == 7 ) {
    ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Окончание конкурса</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_end_date" name="end_date" value="<?=date('d-m-Y',strtotime($prj['end_date']))?>" class="b-input__text"  size="10" maxlength="10" onfocus="adm_edit_content.hideError('end_date')">
            <div id="end_date_btn" class="b-input__cal"></div>
        </div>
        
        <div id="div_adm_edit_err_end_date" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_end_date"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Объявление победителей</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_win_date" name="win_date" value="<?=date('d-m-Y',strtotime($prj['win_date']))?>" class="b-input__text"  size="10" maxlength="10" onfocus="adm_edit_content.hideError('win_date')">
            <div id="win_date_btn" class="b-input__cal"></div>
        </div>
        
        <div id="div_adm_edit_err_win_date" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_win_date"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    <? } ?>
    
    <?php // Бюджет 
    $bAgreement = (isset($prj['cost']) && intval($prj['cost']) == 0);
    ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Бюджет</label>
        <div class="b-input_inline-block b-input_width_545">
            <div class="b-check b-check_padtop_3">
                <input id="adm_edit_agreement" class="b-check__input" type="checkbox" name="agreement" value="1" <?=($bAgreement ? 'checked="checked"' : '')?> onchange="adm_edit_content.prjAgreement()" onchange="adm_edit_content.hideError('cost')" />
                <label class="b-check__label" for="adm_edit_agreement" id="label_close_comments">По договоренности</label>
            </div>
        </div>
    </div>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_cost" name="cost" value="<?=$prj['cost']?>" class="b-input__text" size="80" <?=($bAgreement ? ' disabled' : '' )?> maxlength="6" onfocus="adm_edit_content.hideError('cost')">
        </div>
        <div class="b-input_inline-block b-input_width_150">
            <select name="currency" id="adm_edit_currency" class="b-select__select b-select__select_width_full" <?=($bAgreement ? ' disabled' : '' )?> onchange="adm_edit_content.hideError('cost')">
                <option value="-1">Выберите валюту</option>
                <option value="2"<?= ($prj['currency'] == 2 && !(isset($prj['cost']) && intval($prj['cost']) == 0) ? ' selected="selected"' : '') ?>>Руб</option>
                <option value="0"<?= ($prj['currency'] === '0' && !(isset($prj['cost']) && intval($prj['cost']) == 0) ? ' selected="selected"' : '') ?>>USD</option>
                <option value="1"<?= ($prj['currency'] == 1 && !(isset($prj['cost']) && intval($prj['cost']) == 0) ? ' selected="selected"' : '') ?>>Euro</option>
            </select>
        </div>
        <div class="b-input_inline-block b-input_width_180">
            <select name="priceby" id="adm_edit_priceby" class="b-select__select b-select__select_width_full" <?=($bAgreement ? ' disabled' : '' )?> onchange="adm_edit_content.hideError('cost')">
                <option value="-1">Выберите из списка</option>
                <option value="1"<?= ($prj['priceby'] == 1 ? ' selected="selected"' : '') ?>>цена за час</option>
                <option value="2"<?= ($prj['priceby'] == 2 ? ' selected="selected"' : '') ?>>цена за день</option>
                <option value="3"<?= ($prj['priceby'] == 3 ? ' selected="selected"' : '') ?>>цена за месяц</option>
                <option value="4"<?= ($prj['priceby'] == 4 ? ' selected="selected"' : '') ?>>цена за проект</option>
            </select>
        </div>
        
        <div id="div_adm_edit_err_cost" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_cost"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php // Отвечать на проект могут только пользователи с аккаунтом ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input_inline-block b-input_width_545">
            <div class="b-check b-check_padtop_3">
                <input id="adm_edit_pro_only" class="b-check__input" type="checkbox" name="pro_only" value="1" <?=($prj['pro_only'] == 't' ? 'checked="checked"' : '')?> />
                <label class="b-check__label" for="adm_edit_pro_only" id="label_close_comments">Отвечать на проект могут только пользователи с аккаунтом</label>
            </div>
        </div>
    </div>
</div>

<?php // Файлы ?>
<div id="adm_edit_tab_div2" style="display: none;">
    <div class="b-form">
        <div id="adm_edit_attachedfiles" class="b-fon" style="width:635px"></div>
    </div>
</div>

<?php // Платные услуги ?>
<div id="adm_edit_tab_div3" style="display: none;">
    <table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
    <tr class="b-layout__tr">
        <td class="b-layout__left b-layout__left_width_300 b-layout__left_padleft_15">
            <?php // Закрепление наверху ?>
            <div class="b-check b-check_padbot_10">
                <input class="b-check__input" id="adm_edit_top_ok" name="<?=(!$remTPeriod ? 'top_ok' : 'top_fake')?>" type="checkbox" value="1" <?=($remTPeriod ? ' checked disabled ' : '')?> />
                <label for="adm_edit_top_ok" class="b-check__label b-check__label_margleft_5">Закрепить наверху страницы<span id="popup_qedit_prj_top_ok_icon" class="b-check__pin" <?=(!$remTPeriod ? 'style="display:none"' : '')?>></span></label>
            </div>
            <?=($remTPeriod ? '<div class="b-input_inline-block b-check_margleft_25 b-check__over">'.$remtverb.' '.$remTPeriod.'</div>' : '')?>
            <div class="b-check b-check_padbot_10 b-check_margleft_25">
                <?=($remTPeriod ? '<input class="b-check__input"  id="adm_edit_top_ok2" name="top_ok" type="checkbox" value="1" />' : '')?><label <?=($remTPeriod ? ' for="adm_edit_top_ok2" ' : '')?> class="b-check__label"><?=(!$remTPeriod ? 'На' : 'Продлить на')?></label>
                <div class="b-form b-form_inline-block b-check__form">
                    <div class="b-input b-input_inline-block"><input class="b-input__text b-input__text_width_25 b-input__text_fontsize_11" name="top_days" type="text" value="1" /></div> 
                    <label class="b-form__name b-form__name_padtop_2">&nbsp;дней</label>
                </div>
            </div>
            
            <?php // Выделить цветом ?>
            <div class="b-check b-check_padbot_10">
                <input id="adm_edit_is_color" class="b-check__input" type="checkbox" name="is_color" value="1" <?=($prj['is_color'] == 't' ? 'checked="checked"' : '')?> />
                <label class="b-check__label" for="adm_edit_is_color" id="label_close_comments">Выделить цветом</label>
            </div>
            
            <?php // Выделить жирным ?>
            <div class="b-check b-check_padbot_10">
                <input id="adm_edit_is_bold" class="b-check__input" type="checkbox" name="is_bold" value="1" <?=($prj['is_bold'] == 't' ? 'checked="checked"' : '')?> />
                <label class="b-check__label" for="adm_edit_is_bold" id="label_close_comments">Выделить жирным</label>
            </div>
        </td>
        <td class="b-layout__right b-layout__left_width_300 b-layout__right_padleft_15">
            <?php // Загрузить логотип со ссылкой ?>
            <div class="b-check b-check_padbot_10">
                <input id="adm_edit_logo_ok" class="b-check__input" type="checkbox" name="logo_ok" value="1" <?=($prj['logo_id'] ? 'checked="checked" disabled' : '')?> />
                <label class="b-check__label" for="adm_edit_logo_ok" id="label_close_comments">Загрузить логотип со ссылкой</label>
            </div>
            
            <div class="b-check b-check_padbot_10">
                <iframe style="width:300px;height:45px;" scrolling="no" id="fupload" name="fupload" src="/upload.php?type=prj_logo&uid=<?=$portf['user_id']?>&pkey=<?=$sTmpKey?>" frameborder="0"></iframe>
                <input type="hidden" id="adm_edit_logo_id" name="logo_id" value="<?=$prj['logo_id']?>">
                <?php 
                $sHref = '';
                
                if ( $prj['logo_id'] ) {
                    $logo  = $tmpPrj->getLogo();
                    $sHref = WDCPREFIX . '/' . $logo['path'] . $logo['name'];
                }
                ?>
                <div class="b-layout__txt b-layout__txt_fontsize_11" id="adm_edit_span_logo" style="display: <?=( $prj['logo_id'] ? 'block' : 'none' )?>"><a href="<?=$sHref?>" class="blue" target="_blank">Посмотреть загруженный файл</a>&nbsp;&nbsp;<input type="checkbox" class="b-check__input" id="adm_edit_del_logo" name="del_logo" value="1" /><label class="b-check__label" for="adm_edit_del_logo">Удалить файл</label></div>
                <div class="b-layout__txt b-layout__txt_fontsize_11">Не более 50 Кб.<br/>150 пикселей в ширину, до 150 в высоту.<br/>(gif, jpeg, png).</div>
            </div>
            
            <div class="b-check b-check_padbot_10">
                <label for="adm_edit_top_ok2" class="b-check__label">Ссылка</label>
                <div class="b-form b-form_inline-block b-check__form">
                    <div class="b-input b-input_inline-block">
                        <input class="b-input__text b-input__text_width_180 b-input__text_fontsize_11" name="link" type="text" value="<?=$prj['link']?>" />
                    </div> 
                </div>
            </div>
        </td>
    </tr>
    </table>
    
    <div id="div_adm_edit_err_paid" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
        <b class="b-fon__b1"></b>
        <b class="b-fon__b2"></b>
        <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
            <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_paid"></div>
        </div>
        <b class="b-fon__b2"></b>
        <b class="b-fon__b1"></b>
    </div>
</div>