<?php
/**
 * Шаблон попап формы быстрого редактирования Платные места
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ибо нефиг
    exit();
}

$sHref = '';
?>

<input type="hidden" name="user_id" id="adm_edit_user_id" value="<?=$place['uid']?>">
<input type="hidden" name="login" id="adm_edit_login" value="<?=$place['login']?>">
<input type="hidden" name="uname" id="adm_edit_uname" value="<?=$place['uname']?>">
<input type="hidden" name="usurname" id="adm_edit_usurname" value="<?=$place['usurname']?>">

<?=_parseHiddenParams($aParams)?>

<div class="b-menu b-menu_rubric b-menu_padbot_10">
    <ul class="b-menu__list">
        <li id="adm_edit_tab_i1" class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">Основное</span></span></li>
        <li id="adm_edit_tab_i2" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(2); return false;">Причина редактирования</a></li>
    </ul>
</div>

<div id="adm_edit_tab_div1">
    <?php // Заголовок ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_header">Заголовок</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_header" name="header" value="<?=($place['ad_header'] ? $place['ad_header'] : $place['title'])?>" class="b-input__text" size="80" onfocus="adm_edit_content.hideError('header')">
            <label class="b-check__label" style="padding-top: 3px;">Максимум <?=pay_place::MAX_HEADER_SIZE?> <?=ending(pay_place::MAX_HEADER_SIZE, 'символ', 'символа', 'символов')?>.</label>
        </div>
        
        <div id="div_adm_edit_err_header" class="b-fon b-fon_bg_ff6d2d b-fon_margtop_10 b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_header"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php // Текст ?>
    <div class="b-form b-form_padtop_10">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_txt">Текст</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_txt_source" style="display:none" cols="50" rows="20"><?=($place['ad_text'] ? $place['ad_text'] : $place['descr'])?></textarea>
            <textarea id="adm_edit_txt" name="txt" onfocus="adm_edit_content.hideError('txt')" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"></textarea>
            <label class="b-check__label">Максимум <?=pay_place::MAX_TEXT_SIZE?> <?=ending(pay_place::MAX_TEXT_SIZE, 'символ', 'символа', 'символов')?>.</label>
        </div>
        
        <div id="div_adm_edit_err_txt" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_txt">Поле заполнено некорректно</div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php // Файл ?>
    <div class="b-form b-form_padtop_10">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Файл</label>
        <iframe style="width:550px;height:45px;" scrolling="no" id="fupload" name="fupload" src="/upload.php?type=carusellogo&uid=<?=$place['uid']?>" frameborder="0"></iframe>
        <input type="hidden" id="new_val" name="new_val" value="">
        <input type="hidden" id="old_val" name="old_val" value="<?=$place['ad_img_file_name']?>">
        
        <?php
        if ( $place['ad_img_file_name'] ) {
            $sHref = WDCPREFIX . '/users/' . $place['login'] . '/foto/' . $place['ad_img_file_name'];
        }
        ?>
        <br/><span id="span_new_val"><?php if ( $sHref ) { ?><a href="<?=$sHref?>" class="blue" target="_blank">Посмотреть загруженный файл</a>&nbsp;&nbsp;<input type="checkbox" class="b-check__input" id="adm_edit_del_prev" name="del_prev" value="1"><label class="b-check__label" for="adm_edit_del_prev">Удалить файл</label><?php } ?></span>
        <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">Разрешенные форматы: jpg, png, размером не более 1000х1000 пикс объемом не более 1 Мб.</div>
    </div>
</div>