<?php
/**
 * Шаблон попап формы быстрого редактирования блогов
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
        <?php if ( $rec_type == 1 ) { ?>
        <li id="adm_edit_tab_i3" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(3); return false;">Опрос</a></li>
        <?php } ?>
        <li id="adm_edit_tab_i<?=( $rec_type == 1 ? 4 : 3 )?>" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(<?=( $rec_type == 1 ? 4 : 3 )?>); return false;">Причина редактирования</a></li>
    </ul>
</div>

<input type="hidden" name="thread" value="<?=$edit_msg['id']?>" /> 
<input type="hidden" name="thread_id" value="<?=$edit_msg['thread_id']?>" />
<input type="hidden" name="user_id" value="<?=$edit_msg["fromuser_id"]?>" />
<input type="hidden" name="user_name" value="<?=$edit_msg["uname"]?>" />
<input type="hidden" name="user_surname" value="<?=$edit_msg["usurname"]?>" />
<input type="hidden" name="olduserlogin" value="<?=$edit_msg["login"]?>" />
<input type="hidden" name="oldusertitle" value="<?=$edit_msg["title"]?>" />
<input type="hidden" name="post_time" value="<?=$edit_msg["post_time"]?>" />

<?=_parseHiddenParams($aParams)?>

<div id="adm_edit_tab_div1">
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_name">Заголовок</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_name" name="name" value="<?=$edit_msg['title']?>" class="b-input__text" size="80">
        </div>
    </div>

    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_msg">Текст</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_msg_source" style="display:none" cols="50" rows="20"><?=input_ref($edit_msg['msgtext'])?></textarea>
            <textarea id="adm_edit_msg" name="msg" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"></textarea>
        </div>

        <div id="div_adm_edit_err_msg" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_msg"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <div class="b-form b-form_padtop_10">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_yt_link">YouTube</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_yt_link" name="yt_link" value="<?=$edit_msg['yt_link']?>" class="b-input__text" size="80">
        </div>

        <div id="div_adm_edit_err_yt_link" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_yt_link"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>

    <?php if ( $rec_type == 1 ) { ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Настройки</label>
        <div class="b-input_inline-block b-input_width_545">
            <div class="b-check b-check_padtop_3">
                <input id="adm_edit_ch_close_comments" class="b-check__input" type="checkbox" name="close_comments" value="1" <?=(($edit_msg['close_comments']=="t")?"checked='checked'":"")?> />
                <label class="b-check__label" for="adm_edit_ch_close_comments" id="label_close_comments">Запретить комментирование (тема будет перенесена в раздел "Личные блоги")</label>
            </div>
            <div class="b-check b-check_padtop_3">
                <input id="adm_edit_ch_is_private" class="b-check__input" type="checkbox" name="is_private" value="1" <?=(($edit_msg['is_private']=="t")?"checked='checked'":"")?> />
                <label class="b-check__label" for="adm_edit_ch_is_private" id="label_is_private">Показывать только мне (скрытые от пользователей темы видны модераторам)</label>
            </div>
            <div class="b-check b-check_padtop_3">
                <input class="b-check__input" type="checkbox" id="adm_edit_ontopid" name="ontop" value="1" <?=(($edit_msg['ontop']=='t')? 'checked="checked"': '')?> />
                <label class="b-check__label b-check__label_fontsize_11" for="adm_edit_ontopid">Закрепить тему наверху</label>
            </div>
        </div>
    </div>
    
    <div class="b-form b-form_padtop_10">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Раздел</label>
        <div class="b-input_inline-block b-input_width_545">
            <select id="adm_edit_category" name="category" class="b-select__select b-select__select_width_full" tabindex="300">
            <?php if ( $groups ) {
                foreach ( $groups as $id => $group ) {
                    if ( $group['id'] != 55 || $GLOBALS['allow_love'] ) { 
                        $sSelected = '';
                        if ( $edit_msg['id'] && $edit_msg['id_gr'] == $group['id'] && $group['t'] == $edit_msg['base'] ) {
                            $sSelected = ' selected';
                        }
                        ?><option value="<?=($group['id'] .'|'. $group['t'])?>" <?=$sSelected?> ><?=$group['t_name']?></option><?php
                    }
                }
            } ?>
            </select>
        </div>
    </div>
    <?php } ?>
</div>

<div id="adm_edit_tab_div2" style="display: none;">
    <div class="b-form">
        <div id="adm_edit_attachedfiles" class="b-fon" style="width:635px"></div>
    </div>
</div>

<?php if ( $rec_type == 1 ) { ?>
<div id="adm_edit_tab_div3" style="display: none;">
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_question">Вопрос</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea cols="50" rows="20" id="adm_edit_question_source" style="display: none"><?=$edit_msg['poll_question']?></textarea>
            <textarea id="adm_edit_question" name="question" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"></textarea>
            <div id="adm_edit_question_warn">&nbsp;</div>
        </div>

        <div id="div_adm_edit_err_question" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_question"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Тип опроса</label>
        <div class="b-input_inline-block b-input_width_545">
            <div class="b-radio  b-radio_layout_horizontal">
                <div class="b-radio__item">
                    <input id="fmultiple0" class="b-radio__input" type="radio" name="multiple" value="0" <?=($edit_msg['poll_multiple'] != 't' ? "checked='checked'": "")?> />
                    <label class="b-radio__label" for="fmultiple0">Один вариант ответа&nbsp;&nbsp;&nbsp;</label>
                </div>
                <div class="b-radio__item">
                    <input id="fmultiple1" class="b-radio__input" type="radio" name="multiple" value="1" <?=($edit_msg['poll_multiple'] == 't' ? "checked='checked'": "")?> />
                    <label class="b-radio__label" for="fmultiple1">Несколько вариантов ответа</label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="b-form">
        <table border="0" cellspacing="0" cellpadding="0" width="635px;">
    <?php
    $i = 0;
    $c = count($answers);
    
    if ( $c ) {
        foreach ($answers as $answer) { ?>
            <tr valign="top" class="poll-line" id="poll-<?=$i?>">
                <td class="b-form__name_width_80">
                    <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">
                        Ответ #<span class="poll-num"><?=($i + 1)?></span>
                    </label>
                </td>
                <td>
                    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td><input maxlength="<?=blogs::MAX_POLL_ANSWER_CHARS?>" class="poll-answer" type="text" value="<?=$answer['answer']?>" <?=($answer['id'] ? "name='answers_exists[{$answer['id']}]'" : "name='answers[]'")?> tabindex="20<?=$i?>" style="width: 99%;" /></td>
                        <td class="poll-btn" style="width: 20px; text-align: right; padding-top: 4px;"><a class="poll-del" href="javascript: return false" onclick="poll.del('Blogs', <?=($i++)?>); return false;"><img src="/images/delpoll.png" width="15" height="15"  alt="Удалить ответ" title="Удалить ответ" /></a></td>
                        <td class="poll-btn" style="width: 20px; text-align: right; padding-top: 4px;"><span class="poll-add">&nbsp;</span></td>
                    </tr>
                    </table>
                </td>
            </tr><?php
        }
    }
    ?>
            <tr>
                <td></td>
            </tr>
        </table>
    </div>
</div>
<?php } ?>