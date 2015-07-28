<?php
/**
 * Редатирование пользовательского контента модератором
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ибо нефиг
    exit();
}
?>
<script type="text/javascript">
    var attachedfiles_list = new Array();
</script>
<div class="i-shadow">
<div id="ov-notice33" class="b-shadow b-shadow_center b-shadow_zindex_11 b-shadow_hide b-shadow__quick">
    <div class="b-shadow__body b-shadow__body_pad_15">
                        <h4 id="h4_adm_edit" class="b-shadow__h4 b-shadow__h4_padbot_15">Редактировать</h4>
                        
                        <form id="adm_edit_frm" name="adm_edit_frm" action="" enctype="multipart/form-data" method="post">
                        <div id="div_adm_edit"></div>
                        
                        <div id="div_adm_reason">
                            <div class="b-form b-form_padtop_10">
                                <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Причина</label>
                                <div id="div_adm_edit_sel" class="b-input_inline-block b-input_width_545">
                                    <select id="adm_edit_sel" name="adm_edit_sel" class="b-select__select b-select__select_width_full" disabled="disabled">
                                        <option>Подождите...</option>
                                    </select>
                                </div>
                            </div>

                            <div class="b-form">
                                <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="popup_qedit_prj_fld_descr">Текст</label>
                                <div class="b-textarea b-textarea_inline-block b-textarea_width_550">
                                    <textarea id="adm_edit_text" name="adm_edit_text" class="b-textarea__textarea b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div id="div_ban_btn" class="b-buttons">
                            <a id="adm_edit_btn" href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="adm_edit_content.save();">Сохранить</a>
                            <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
                            <a href="javascript:void(0);" class="b-buttons__link b-buttons__link_dot_c10601" onclick="adm_edit_content.cancel();return false;">закрыть, не сохраняя</a>
                        </div>
                        </form>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close" onclick="adm_edit_content.cancel();return false;"></span>
</div>
</div>

