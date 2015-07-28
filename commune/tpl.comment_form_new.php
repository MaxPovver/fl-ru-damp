<?global $user_mod;?>
<a name="o"></a>
<a name="<?= !empty($alert) ? 'error' : '';?>"></a>

<?php
$member = commune::GetCommuneByMember($_SESSION['uid']);
$draft_id = !$draft_id ? intval($_GET['draft_id']) : $draft_id;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
$draft_data = drafts::getDraft($draft_id, get_uid(false), 4);
if($draft_data) {
    $mess['category_id'] = $draft_data['category'];
    $title = $draft_data['title'];
    $msgtext = $draft_data['msg'];
    $youtube_link = $draft_data['yt_link'];
    $question = $draft_data['poll_question'];
    $multiple = ($draft_data['poll_type']==0 ? 'f' : 't');
    $draft_answers = $draft_data['poll_answers'];
    if ( empty($draft_answers) ) {
            $draft_answers = array( '' );
    }
    $edit_msg['poll'] = array();
    if($draft_answers) {
        foreach($draft_answers as $draft_answer) {
            array_push($edit_msg['poll'], array('answer'=>htmlspecialchars($draft_answer)));
        }
    }
    $answers = $edit_msg['poll'];
}
if(!isset($user_mod)) {
    if ( $uStatus = commune::GetUserCommuneRel($id, get_uid()) ) {
        $user_mod |= commune::MOD_COMM_MODERATOR * $uStatus['is_moderator'];
        $user_mod |= commune::MOD_COMM_MANAGER * $uStatus['is_manager'];
        $user_mod |= commune::MOD_COMM_ADMIN * ($uStatus['is_admin'] || $uStatus['is_moderator'] || $uStatus['is_manager']);
        $user_mod |= commune::MOD_COMM_AUTHOR * $uStatus['is_author'];
        $user_mod |= commune::MOD_COMM_ASKED * $uStatus['is_asked'];
        $user_mod |= commune::MOD_COMM_ACCEPTED * ($uStatus['is_accepted'] || ($user_mod & commune::MOD_COMM_ADMIN));
        $user_mod |= commune::MOD_COMM_BANNED * $uStatus['is_banned'];
    }
    $mod = $user_mod;
}
$is_comm_admin = $user_mod & (commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR);
$is_author = $user_mod & (commune::MOD_COMM_AUTHOR);
?>

<form <?= ($alert ? ' id="idAlertedCommentForm"' : 'frm id="msg_form"') ?> action="#error<?= ($site == 'Topic' && $action == 'Create.post' && $top_id == $parent_id ? '-last' : ($site == 'Topic' ? 'p' : ($message_id ? '' : ($parent_id ? $parent_id : "")))) ?>" onsubmit="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {this.submit();}" onkeydown="if(event.ctrlKey && event.keyCode==13 && submitFlag==1){submitFlag=0; this.submit()}" method="post" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?= $id ?>"/>
<input type="hidden" name="top_id" value="<?= $top_id ?>"/>
<input type="hidden" name="message_id" value="<?= $message_id ?>"/>
<input type="hidden" name="parent_id" value="<?= $parent_id ?>"/>
<input type="hidden" name="user_login" value="<?= $user_login ?>"/>
<input type="hidden" name="om" value="<?= $om ?>"/>
<input type="hidden" name="page" value="<?= $page ?>"/>
<input type="hidden" name="action" value="do.<?= $action ?>"/>
<input type="hidden" name="cat" value="<?= $cat ?>"/>

<? // заголовок ?>
<div class="b-form b-form_padbot_20 b-layout">
    <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_90">
                <label class="b-form__name b-form__name_padtop_5 b-form__name_fontsize_13 b-form__name_width_90">Заголовок</label>
            </td>
            <td class="b-layout__right">
                <div class="b-combo">
                    <div class="b-combo__input<?= !empty($alert['title']) ? ' b-combo__input_error' : '' ?>">
                        <input id="f_title" autofocus="autofocus" size="80" class="b-combo__input-text" onclick="if($('title_error')) $('title_error').style.display = 'none';" type="text" maxlength="<?= commune::MSG_TITLE_MAX_LENGTH ?>" name="title" value="<?= $title ?>" onfocus="isFocus = true;" onblur="isFocus = false;"/>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
<? // текстовое поле ?>
<div class="b-form b-form_padleft_90<?= !empty($alert['msgtext']) ? ' b-combo__input_error' : '' ?>">
    <textarea id="msg_source" name="msgtext_source" style="display:none;height:200px"><?= $msgtext ?></textarea>
    <textarea class="wysiwyg" style="height:200px" onfocus="if($('msgtext_error')) $('msgtext_error').style.display = 'none';" id="msg" name="msgtext" rows="5" cols="10"><?= $msgtext ?></textarea>
</div>
<? // загрузка файлов ?>
<div class="b-form b-form_padleft_90 b-file">
    <table class="b-file_layout" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td class="b-file__button">            
                <div class="b-file__wrap">
                    <input class="b-file__input" type="file" />
                    <a href="#" class="b-button b-button_rectangle_color_transparent">
                        <span class="b-button__b1">
                            <span class="b-button__b2">
                                <span class="b-button__txt">Загрузить файлы</span>
                            </span>
                        </span>
                    </a>
                </div>
            </td>
            <td class="b-file__text">
                <div class="b-filter">
                    <div class="b-filter__body b-filter__body_padtop_10">
                        <a href="#" class="b-filter__link b-filter__link_fontsize_11 b-filter__link_dot_41">Требования к файлам</a>
                    </div>
                    <div class="b-shadow b-filter__toggle b-filter__toggle_hide b-shadow__margleft_-110  b-shadow_top_30">
                                        <div class="b-shadow__body b-shadow__body_pad_15 b-shadow_width_270 b-shadow__body_bg_fff">
                                            <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">Разрешается добавлять не более <span class="b-shadow__txt b-shadow__txt_bold">10 файлов</span> общим объемом не более 5 МБ.</div>
                                            <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">jpg и gif размером <span class="b-shadow__txt b-shadow__txt_bold">600х1000 пикс.</span> и весом не более 300 КБ будут вставлены в текст поста, остальные файлы будут приложены к нему.</div>
                                            <div class="b-shadow__txt b-shadow__txt_fontsize_11">Запрещенные форматы: ade, adp, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msk, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>
                                        </div>
                        <div class="b-shadow__icon_nosik"></div>
                        <div class="b-shadow__icon_close"></div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
<? // добавить видео ?>
<div class="b-form b-form_padleft_90" id="add_yt_box">
    <a href="#" class="b-button b-button_m_add" id="add_yt_box1"></a>
    <div class="b-form__txt b-form__txt_padleft_5">
        <a id="add_yt_box2" class="b-form__link b-form__link_dot_0f71c8" href="#">Добавить видео</a>
    </div>
</div>
<div class="b-form b-form_padleft_90 b-form_padbot_20" id="yt_box" style="display:<?= $youtube_link ? 'block' : 'none' ?>">
    <div class="b-fon ">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_bg_f0ffdf b-layout i-button">
            <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left">
                        <div class="b-combo">
                            <div class="b-combo__input<?= !empty($alert['youtube']) ? ' b-combo__input_error' : '' ?>">
                                <input class="b-combo__input-text b-combo__input-text_color_a7" name="youtube_link" id="youtube_link" type="text" size="80" value="<?= $youtube_link ? $youtube_link : 'Ссылка на видео-ролик Youtube, Rutube или Vimeo' ?>" onfocus="checkYouTube();">
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__right b-layout__right_right b-layout__right_width_30">
                        <a href="#" id="hide_yt_box" class="b-button b-button_m_delete"></a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<? // добавить опрос ?>
<div class="b-form b-form_padleft_90 b-form_padbot_20" id="add_poll">
    <a id="add_poll1" class="b-button b-button_m_add" href="#"></a>
    <div class="b-form__txt b-form__txt_padleft_5">
        <a id="add_poll2" class="b-form__link b-form__link_dot_0f71c8" href="#">Добавить опрос</a>
    </div>
</div>
<div class="b-form b-form_padleft_90 b-form_padbot_20" style="display:<?= $question || $alert['polls'] ? 'block' : 'none' ?>" id="pool_box">
    <div class="b-fon ">
	<div class="b-fon__body b-fon__body_pad_10 b-fon__body_bg_f0ffdf b-layout i-button">
            <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left">
                        <div class="b-textarea<?= $alert['polls'] ? " b-textarea_error" : "" ?>">
                            <textarea class="b-textarea__textarea " name="question" id="question" cols="80" rows="5" onfocus="$('polls_error').setStyle('display', 'none');"><?= $question ?></textarea>
                        </div>
                    </td>
                    <td class="b-layout__right b-layout__right_right b-layout__right_width_30">
                        <a href="#" id="hide_poll" class="b-button b-button_m_delete"></a>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="multiple" id="multiple" value="<?= (empty($multiple) || $multiple == 0) ? 0 : 1 ?>">
            <div>
                <div id="poll-radio"<? if (!(empty($multiple) || $multiple == 0)) { ?> style="display:none"<? } ?> class="b-menu b-menu_rubric b-menu_padtop_10">
                    <ul class="b-menu__list b-menu__list_margleft_0">
                        <li class="b-menu__item"><div class="b-menu__txt b-menu__txt_padtop_3">Можно выбрать</div></li>
                        <li class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">только один вариант ответа</span></span></li>
                        <li class="b-menu__item"><a href="#" class="b-menu__link b-menu__link_bordbot_dot_0f71c8">несколько вариантов ответа</a></li>
                    </ul>
                </div>
                <div id="poll-check"<? if (!(!empty($multiple) || $multiple == 1)) { ?> style="display:none"<? } ?> class="b-menu b-menu_rubric b-menu_padtop_10">
                    <ul class="b-menu__list b-menu__list_margleft_0">
                        <li class="b-menu__item"><div class="b-menu__txt b-menu__txt_padtop_3">Можно выбрать</div></li>
                        <li class="b-menu__item"><a href="#" class="b-menu__link b-menu__link_bordbot_dot_0f71c8">только один вариант ответа</a></li>
                        <li class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">несколько вариантов ответа</span></span></li>
                    </ul>
                </div>

                <?
                $i = 0;
                $c = count($answers);
                foreach ($answers as $answer) {
                ?>                            
                <table id="poll-<?= $i ?>" class="b-layout__table b-layout__table_width_full b-layout_margbot_10 b-poll-edit-answer" cellpadding="0" cellspacing="0" border="0">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_20 b-layout__left_valign_middle">
                            <div class="b-radio">
                                <div class="b-radio__item">
                                    <input class="b-radio__input" name="" type="radio" disabled="disabled" value=""<?= !(empty($multiple) || $multiple == 0) ? ' style="display:none"' : '' ?>/>
                                    <input class="b-check__input" name="" type="checkbox" disabled="disabled" value=""<?= (empty($multiple) || $multiple == 0) ? ' style="display:none"' : '' ?> />
                                </div>
                            </div>
                        </td>
                        <td class="b-layout__middle">
                            <div class="b-combo">
                                <div class="b-combo__input<?= $alert['polls_question'] ? " b-combo__input_error" : "" ?>">
                                    <input id="answer_input_<?= $i ?>" maxlength="<?= commune::POLL_ANSWER_CHARS_MAX ?>" value="<?= $answer['answer'] ?>" name="<?= ($answer['id'] ? "answers_exists[{$answer['id']}]" : "answers[]") ?>" tabindex="20<?= $i ?>" class="b-combo__input-text" type="text" size="80">
                                </div>
                            </div>
                        </td>
                        <td class="b-layout__right b-layout__right_right b-layout__right_width_30 b-layout__right_valign_middle">
                            <a href="javascript:void(0)" id="del_answer_btn_<?= $i ?>" class="b-button b-button_m_delete" style="display: none"></a>
                        </td>
                        <!--
                        <? if ($i < count($answers) - 1) { ?>
                        <td class="b-layout__right b-layout__right_right b-layout__right_width_30">
                            <a href="javascript:void(0)" id="answer_dtn<?//= $i++ ?>" onclick="//poll_new.del('Commune', <?//= $i++ ?>); return false;" class="b-button b-button_m_delete"></a>
                        </td>
                        <? } else if($i<9) { ?>
                        <td class="b-layout__right b-layout__right_right b-layout__right_width_30">
                            <a href="javascript:void(0)" onclick="poll_new.add('Commune'); return false;" class="b-button b-button_m_delete"></a>
                        </td>
                        <? } else { ?>
                        <td class="b-layout__right b-layout__right_right b-layout__right_width_30">
                            <a href="javascript:void(0)" onclick="poll_new.del('Commune', <?//= $i++ ?>); return false;" class="b-button b-button_m_delete"></a>
                        </td>
                        <? } ?>
                        -->
                    </tr>
                    <? if ($answer['id'] && !(hasPermissions('communes') || $edit_msg['fromuser_id'] == $uid)) { ?>
                    <input id="ans_hidden_<?= $i ?>" class="poll-answer-exists" type="hidden" name="answers_exists[<?= $answer['id'] ?>]" value="1">
                    <? } ?>
                </table>
                <? $i++ ?>
                <? } ?>
            </div>
        </div>
    </div>
</div>
        
<? // разделы ?>
<div class="b-form  b-form_padbot_20">
    <label class="b-form__name b-form__name_padtop_2 b-form__name_fontsize_13 b-form__name_width_90">Раздел</label>
    <div class="b-select b-select_inline-block ">
        <select id="b-select__select" name="category_id" class="b-select__select b-select__select_width_180">
            <option>Все разделы</option>
            <?php foreach($sub_cat as $sc){ if($sc['is_only_for_admin'] == 't' && !($is_author || $is_comm_admin)) continue;?>
            <option <?= $mess['category_id'] == $sc['id'] ? 'selected="selected"' : '';?> value="<?= $sc['id'];?>"><?= LenghtFormatEx($sc['name'],commune::MAX_CATEGORY_NAME_SIZE);?></option>
            <? } ?>
        </select>
    </div>
</div>

<? // запретить комментирование ?>
<div class="b-form b-form_padleft_90">
    <div class="b-check">
        <input id="b-check1" class="b-check__input" type="checkbox" value="1" name="close_comments" <?= ($close_comments ? 'checked="checked"' : '') ?> />
        <label for="b-check1" class="b-check__label b-check__label_fontsize_13">Запретить комментирование</label>
    </div>
</div>
        
<? // показывать только мне ?>
<div class="b-form b-form_padleft_90">
    <div class="b-check">
        <input id="b-check2" class="b-check__input" type="checkbox" value="1" name="is_private" <?= ($is_private ? 'checked="checked"' : '') ?> />
        <label for="b-check2" class="b-check__label b-check__label_fontsize_13">Показывать только мне</label>
    </div>
</div>
        
<? // закрепить на верху ?>
<? if (!$parent_id && ($mod & (commune::MOD_COMM_AUTHOR | commune::MOD_COMM_ADMIN)) || hasGroupPermissions('administrator')) { // только для админов и модеров сайта/сообщества ?>
    <div class="b-form b-form_padleft_90">
        <div class="b-check">
            <input class="b-check__input" type="checkbox" id="idTPos<?= $iid ?>" name="pos" value="1" <?= ($pos ? ' checked' : '') ?> />
            <label for="b-check2" class="b-check__label b-check__label_fontsize_13">Закрепить тему наверху</label>
        </div>
    </div>
<? } ?>

<? // сообщение об ошибке ?>
<? if (!empty($alert)) { 
    foreach($alert as $al) {?>
        <div class="b-form b-form_padbot_null b-form_padtop_20 b-form_padleft_90">
            <div class="b-form__txt b-form__txt_error"><span class="b-form__error"></span><?= $al ?></div>
        </div>
    <? } ?>
<? } ?>
        
<div class="b-buttons b-buttons_padleft_90 b-buttons_padtop_30 b-buttons_padbot_20">
    <a href="#" onclick="checkYouTube();<?= ($alert ? 'if(submitFlag) { $(\'idAlertedCommentForm\').submit(); $(this).addClass(\'btnr-disabled\');} submitFlag = 0; return false' : 'if(submitFlag) { $(\'msg_form\').submit(); $(this).addClass(\'btnr-disabled\');} submitFlag = 0; return false') ?>" class="b-button b-button_rectangle_color_green b-button_margright_10">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <span class="b-button__txt">Опубликовать</span>
            </span>
        </span>
    </a>
    <input type="hidden" id="draft_id" name="draft_id" value="<?=intval($draft_id)?>" />
    <input type="hidden" id="draft_post_id" name="draft_post_id" value="<?=$message_id?>" />
    <a class="b-buttons__link b-buttons__link_color_0f71c8" href="javascript:DraftSave();" onclick="this.blur();"></a>
    <span class="b-buttons__txt">или</span>
    <a class="b-buttons__link b-buttons__link_color_c10601" href="#">просто выйти</a>
</div>

<input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>">
</form>
<script type="text/javascript">
window.addEvent('domready', function(){
    document.getElementById('f_title').focus();
    DraftInit(4);
});
</script>

