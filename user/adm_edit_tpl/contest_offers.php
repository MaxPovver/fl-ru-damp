<?php
/**
 * Шаблон попап формы быстрого редактирования конкурсной работы
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ибо нефиг
    exit();
}
?>
<div class="b-menu b-menu_rubric b-menu_padbot_10">
    <ul class="b-menu__list">
        <li id="adm_edit_tab_i1" class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">Основное</span></span></li>
        <li id="adm_edit_tab_i2" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(2); return false;">Файлы</a></li>
        <li id="adm_edit_tab_i3" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(3); return false;">Причина редактирования</a></li>
    </ul>
</div>

<input type="hidden" id="files" name="files" value="">
<input type="hidden" name="id" value="<?=$contest->offer['id']?>">
<input type="hidden" name="user_id" value="<?=$contest->offer['user_id']?>">
<input type="hidden" name="user_login" value="<?=$contest->offer['login']?>">
<input type="hidden" name="user_uname" value="<?=$contest->offer['name']?>">
<input type="hidden" name="user_usurname" value="<?=$contest->offer['uname']?>">

<?=_parseHiddenParams($aParams)?>

<div id="adm_edit_tab_div1">
    <?php // Текст ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_descr">Текст</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_descr_source" style="display:none" cols="50" rows="20"><?=input_ref($contest->offer['descr'])?></textarea>
            <textarea id="adm_edit_descr" name="descr" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5" onfocus="adm_edit_content.hideError('descr')"></textarea>
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
    
    <?php // Запретить другим пользователям комментировать моё предложение ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input_inline-block b-input_width_545">
            <div class="b-check b-check_padtop_3">
                <input id="adm_edit_comm_blocked" class="b-check__input" type="checkbox" name="comm_blocked" value="1" <?=($contest->offer['comm_blocked'] == 't' ? 'checked="checked"' : '')?> />
                <label class="b-check__label" for="adm_edit_comm_blocked" id="label_close_comments">Запретить другим пользователям комментировать моё предложение (только для PRO)</label>
            </div>
        </div>
    </div>
</div>
    
<div id="adm_edit_tab_div2" style="display: none;">
    <div id="ca-iboxes" style="width: 655px; height: 220px; overflow: auto;"></div>
    <div class="ca-managment" style="width: 655px;">
        <table cellpadding="0" border="0">
        <tr>
            <td class="ca-add-text b-post__txt_fontsize_15"><a href="#" onclick="boxes.add(); return false;">Добавить еще 3 поля</a></td>
            <td class="ca-add-info b-post__txt_fontsize_11">Для файлов до 2Мбайт. Файлы следующих форматов запрещены к загрузке:<br/><?php $i=0; $aTmp = array();
				foreach ( $GLOBALS['disallowed_array'] as $sOne ) {
					if ( $i > 14 ) {
						$i = 0;
						echo implode(', ', $aTmp).'<br/>';
						$aTmp = array();
					}
					
					$aTmp[] = $sOne;
					$i++;
				}
				
				if ( $aTmp ) {
					echo implode(', ', $aTmp).'<br/>';
				}
				?>
            </td>
        </tr>
        </table>
    </div>
</div>