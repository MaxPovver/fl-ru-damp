<?php
/**
 * Шаблон попап формы быстрого редактирования предложения по проектам (!не конкурс)
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ибо нефиг
    exit();
}

$sPostText = rtrim(input_ref(strip_tags($offer['dialogue'][0]['post_text'])));
?>
<?php // TODO: решить что то со стилями ?>
<style>
    .works
{
  width:634px;
}
.works .pic, .works .pic_blank
{
  width:200px;
  height:225px;
  vertical-align:top;
  text-align:left;
  padding: 0px;
  margin: 0px;
}
.works .pic_blank .pic_blank_cnt
{
  background: #dfdfdf;
  width:auto;
  height:200px;
  vertical-align:top;
  text-align:left;
  padding: 0px;
  margin: 0px;
}
.works .pic_sort
{
  width: 16px;
  text-align:center;
  vertical-align: top;
}
</style>

<input type="hidden" name="user_id" id="user_id" value="<?=$offer['user_id']?>">
<input type="hidden" name="pid" value="<?=$offer['project_id']?>">
<input type="hidden" name="edit" value="<?=$offer['dialogue'][0]['id']?>">
<input type="hidden" name="ps_payed_items" id="ps_payed_items" value="<?=$offer['payed_items']?>" />

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
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Бюджет</label>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3" for="adm_edit_cost_from">от&nbsp;</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_cost_from" name="ps_cost_from" value="<?=$offer['cost_from']?>" class="b-input__text" maxlength="8">
        </div>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3" for="adm_edit_cost_to">&nbsp;до&nbsp;</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_cost_to" name="ps_cost_to" value="<?=$offer['cost_to']?>" class="b-input__text" maxlength="8">
        </div>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input_inline-block b-input_width_60">
            <select id="adm_edit_cost_type" name="ps_cost_type" class="b-select__select b-select__select_width_full">
                <option value="0" <?=($offer['cost_type'] == 0 ? 'selected' : '')?>>USD</option>
                <option value="2" <?=($offer['cost_type'] == 2 ? 'selected' : '')?>>Руб</option>
                <option value="1" <?=($offer['cost_type'] == 1 ? 'selected' : '')?>>Euro</option>
                <option value="3" <?=($offer['cost_type'] == 3 ? 'selected' : '')?>>FM</option>
            </select>
        </div>
    </div>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Сроки</label>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3" for="adm_edit_time_from">от&nbsp;</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_time_from" name="ps_time_from" value="<?=$offer['time_from']?>" class="b-input__text" maxlength="8">
        </div>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3" for="adm_edit_time_to">&nbsp;до&nbsp;</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_time_to" name="ps_time_to" value="<?=$offer['time_to']?>" class="b-input__text" maxlength="8">
        </div>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input_inline-block b-input_width_90">
            <select id="adm_edit_time_type" name="ps_time_type" class="b-select__select b-select__select_width_full">
                <option value="0"<? if ($offer['time_type'] == 0) { ?> selected<? } ?>>в часах</option>
                <option value="1"<? if ($offer['time_type'] == 1) { ?> selected<? } ?>>в днях</option>
                <option value="2"<? if ($offer['time_type'] == 2) { ?> selected<? } ?>>в месяцах</option>
            </select>
        </div>
    </div>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_msg">Текст</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_descr_source" style="display:none" cols="50" rows="20"><?=$sPostText?></textarea>
            <textarea id="adm_edit_descr" name="ps_text" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"></textarea>
        </div>
    </div>
</div>

<div id="adm_edit_tab_div2" style="display: none;">
    <?php 
    if ( $user->is_pro == 'f' ) { 
        ?><div class="b-form">Функция доступна только для предложений от владельцев аккаунта PRO</div><? 
    } 
    else {
    ?>
    <input id="ps_work_1_id" name="ps_work_1_id" type="hidden" value="" />
    <input id="ps_is_color" name="ps_is_color" type="hidden" value="<?= $offer['is_color']?>" />
    <input id="ps_payed_items" name="ps_payed_items" type="hidden" value="<?= $offer['payed_items']?>" />
    <input id="ps_work_2_id" name="ps_work_2_id" type="hidden" value="" />
    <input id="ps_work_3_id" name="ps_work_3_id" type="hidden" value="" />
    <input id="ps_work_1_pict" name="ps_work_1_pict" type="hidden" value="<?=$offer['portfolio_work_1']?>" />
    <input id="ps_work_2_pict" name="ps_work_2_pict" type="hidden" value="<?=$offer['portfolio_work_2']?>" />
    <input id="ps_work_3_pict" name="ps_work_3_pict" type="hidden" value="<?=$offer['portfolio_work_3']?>" />
    <input id="ps_work_1_prev_pict" name="ps_work_1_prev_pict" type="hidden" value="<?=$$offerps['portfolio_work_1_prev_pict']?>" />
    <input id="ps_work_2_prev_pict" name="ps_work_2_prev_pict" type="hidden" value="<?=$offer['portfolio_work_2_prev_pict']?>" />
    <input id="ps_work_3_prev_pict" name="ps_work_3_prev_pict" type="hidden" value="<?=$offer['portfolio_work_3_prev_pict']?>" />
    <input id="ps_work_1_link" name="ps_work_1_link" type="hidden" value="<?=$offer['portfolio_work_1_link']?>" />
    <input id="ps_work_2_link" name="ps_work_2_link" type="hidden" value="<?=$offer['portfolio_work_2_link']?>" />
    <input id="ps_work_3_link" name="ps_work_3_link" type="hidden" value="<?=$offer['portfolio_work_3_link']?>" />
    <input id="ps_work_1_name" name="ps_work_1_name" type="hidden" value="<?=$offer['portfolio_work_1_name']?>" />
    <input id="ps_work_2_name" name="ps_work_2_name" type="hidden" value="<?=$offer['portfolio_work_2_name']?>" />
    <input id="ps_work_3_name" name="ps_work_3_name" type="hidden" value="<?=$offer['portfolio_work_3_name']?>" />
    <table class="works" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td id="td_pic_1" class="pic_blank">
            <div class="pic_blank_cnt">&nbsp;</div>
            <div style="margin-top:6px; font-size:100%;">&nbsp;</div>
        </td>
        <td id="td_pic_sort_1" class="pic_sort"><?
        if (($offer['portfolio_work_1'] == '') && ($offer['portfolio_work_2'] == '')) { ?><img id="ico_right<?=$curprof?>" src="/images/ico_right0.gif" alt="" width="9" height="9" border="0"><?} else { ?><img id="ico_right<?=$curprof?>" src="/images/ico_right.gif" alt="" width="9" height="9" border="0" /><? } ?><br /><?
        if (($offer['portfolio_work_1'] == '') && ($offer['portfolio_work_2'] == '')) { ?><img id="ico_left<?=$curprof?>" src="/images/ico_left0.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" /><? } else { ?><img id="ico_left<?=$curprof?>" src="/images/ico_left.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" /><? }
        ?></td>
        <td id="td_pic_2" class="pic_blank">
            <div class="pic_blank_cnt">&nbsp;</div>
            <div style="margin-top:6px; font-size:100%;">&nbsp;</div>
        </td>
        <td id="td_pic_sort_2" class="pic_sort"><?
        if (($offer['portfolio_work_2'] == '') && ($offer['portfolio_work_3'] == '')) { ?><img id="ico_right<?=$curprof?>" src="/images/ico_right0.gif" alt="" width="9" height="9" border="0" /><?} else { ?><img id="ico_right<?=$curprof?>" src="/images/ico_right.gif" alt="" width="9" height="9" border="0" /><? } ?><br /><?
        if (($offer['portfolio_work_2'] == '') && ($offer['portfolio_work_3'] == '')) { ?><img id="ico_left<?=$curprof?>" src="/images/ico_left0.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" /><? } else { ?><img id="ico_left<?=$curprof?>" src="/images/ico_left.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" /><? }
        ?></td>
        <td id="td_pic_3" class="pic_blank">
            <div class="pic_blank_cnt">&nbsp;</div>
            <div style="margin-top:6px; font-size:100%;">&nbsp;</div>
        </td>
    </tr>
    </table>
    
    <div class="b-form b-form_padtop_10" id="adm_edit_work_ctrl1">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_60 b-form__name_padtop_3">Работы</label>
        <div class="b-input_inline-block b-input_width_545">
            <select id="adm_edit_prof" name="professions" onchange="adm_edit_content.prjOfferLoadWorks();" class="b-select__select b-select__select_width_220" tabindex="300">
            <?php if ( $professions ) {
                foreach ( $professions as $key => $value ) { 
                    ?><option value="<?=$value['id']?>" <?=$sSelected?> ><?=$value['name']?></option><?php
                }
            } ?>
            </select>
            
            <div class="b-input_inline-block" id="adm_edit_works_div">
                <select id="adm_edit_works" name="works" class="b-select__select b-select__select_width_220" tabindex="300">
                <?php if ( $portf_works ) {
                    foreach ( $portf_works as $key => $value ) {
                        ?><option value="<?=$value['id']?>"><?=$value['name']?></option><?php
                    }
                } ?>
                </select>
            </div>
            <input type="button" onclick="adm_edit_content.prjOfferAddWork($('adm_edit_works').get('value'));" id="adm_edit_pict_add" name="adm_edit_pict_add" value="Подгрузить">
        </div>
    </div>
    
    <div class="b-form b-form_padtop_10" id="adm_edit_work_ctrl2">
        <iframe style="width:626px;height:45px;" scrolling="no" id="fupload" name="fupload" src="/projects/upload.php?pid=<?=$offer['project_id']?>&uid=<?=$offer['user_id']?>&onload=adm_edit_content" frameborder="0"></iframe><br />
        С помощью этого поля возможно загрузить файл.<br />
        Максимальный размер файла: 2 Мб.<br />
        Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
    </div>
    
    <div class="b-form" id="adm_edit_work_msg" style="display: none;">Чтобы добавить другие работы, удалите одну из выбранных</div>
    <?php
    }
    ?>
</div>