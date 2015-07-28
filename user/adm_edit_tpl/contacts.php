<?php
/**
 * Шаблон попап формы быстрого редактирования личных сообщений
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ибо нефиг
    exit();
}
?>
<div class="b-menu b-menu_rubric b-menu_padbot_10">
    <ul class="b-menu__list">
        <li id="adm_edit_tab_i1" class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">Сообщение</span></span></li>
        <li id="adm_edit_tab_i2" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(2); return false;">Файлы</a></li>
        <li id="adm_edit_tab_i3" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(3); return false;">Причина редактирования</a></li>
    </ul>
</div>

<input type="hidden" name="to_id" value="<?=$msg['to_id']?>">

<?=_parseHiddenParams($aParams)?>

<div id="adm_edit_tab_div1">
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_msg">Текст</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_msg_source" style="display:none" cols="50" rows="20"><?=input_ref($msg['msg_text'])?></textarea>
            <textarea id="adm_edit_msg" name="msg_text" onfocus="adm_edit_content.hideError('msg')" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"></textarea>
        </div>

        <div id="div_adm_edit_err_msg" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_msg">Поле заполнено некорректно</div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
</div>

<div id="adm_edit_tab_div2" style="display: none;">
    <div class="b-form">
        <div id="adm_edit_attachedfiles" class="b-fon" style="width:635px"></div>
    </div>
</div>