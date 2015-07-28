<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
$xajax->printJavascript('/xajax/');
?>

    <h1 class="b-page__title"><?= $top['title'] ? $top['title'] : 'Новый пост' ?></h1>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left">
                    <? if (($user_mod & commune::MOD_COMM_AUTHOR && !$comm['is_blocked']) || $user_mod & (commune::MOD_ADMIN | commune::MOD_MODER | commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_ADMIN)) {?>
                        <script>var __commLastOpenedForm = null;var CreatePostHTML='';</script>
<? //*********************************************
// форма редактирования поста ?>
<div id='editmsg'>

<?
$message_id = $top_id;
if (!$action) {
    $action = 'Create.post';
}
$title = '';
$msgtext = '';
$attach = '';
$youtube_link = '';
$user_login = ''; // Чтобы в него загрузить картинку.
$pos=NULL;
$close_comments=NULL;
$is_private=NULL;
$category = 0;

$mess = commune::GetMessage(intval($message_id));

if($request) { // do...
    $parent_id = $request['parent_id'];
    $title = htmlspecialchars(stripslashes($request['title']));
    $category_id = $request['category_id'];
    $msgtext = stripslashes($request['msgtext']);
    $attach = $request['attach'];
    $youtube_link = $request['youtube_link'];
    $user_login = $request['user_login'];
    $pos = $request['pos'];
    $close_comments=$request['close_comments'] ? true : false;
    $is_private=$request['is_private'] ? true : false;
    $question = stripslashes($request['question']);
    $multiple = $request['multiple'];
} elseif ($action=='Edit.post') { // Edit.post
    if ($mess) {
        $parent_id = $mess['parent_id'];
        $title = $mess['title'];
        $category_id = $mess['category_id'];
        $msgtext = $mess['msgtext'];
        $attach = $mess['attach'];
        $youtube_link = $mess['youtube_link'];
        $user_login = $mess['user_login'];
        $pos = $mess['pos'];
        $close_comments = $mess['close_comments'] == 't' ? true : false;
        $is_private = $mess['is_private'] == 't' ? true : false;
        $question = $mess['question'];
        $multiple = $mess['multiple']=='t' ? 1 : 0;
    }
}
  
$answers = array();
$exists  = (isset($request['answers_exists']) && is_array($request['answers_exists']))? $request['answers_exists']: array();
if ($mess['question'] != '') {
    for ($i=0; $i<count($mess['answers']); $i++) {
        $ok = !isset($request['question']);
        for ($j=0; $j<count($exists); $j++) {
            if (!empty($exists[ $mess['answers'][$i]['id'] ])) {
                $ok = TRUE;
                break;
            }
        }
        if ($ok) {
            $answers[] = array('id'=>$mess['answers'][$i]['id'], 'answer'=>($exists[ $mess['answers'][$i]['id'] ]? __htmlchars($exists[ $mess['answers'][$i]['id'] ]): $mess['answers'][$i]['answer']));
        }
    }
}
if (isset($request['answers']) && is_array($request['answers'])) {
    foreach ($request['answers'] as $answer) {
        $answers[] = array('id' => 0, 'answer' => __htmlchars($answer));
    }
}
if (!$answers) {
    $answers[] = array('id' => 0, 'answer' => '');
}

$h = $site=='Topic' ? 'H1' : 'H2';
$header = !$message_id ? ($site=='Topic' ? 'Комментировать' : 'Создать новое сообщение') : 'Редактировать';
$button = !$message_id ? ($site=='Topic' ? 'Комментировать' : 'Создать') : 'Сохранить';
$tah = $site=='Topic' ? '150' : '200';
$action = str_replace('do.', '', $action);

$anchor = '';
if($site!='Topic') {
    if($alert) {
        $anchor = $action=='Edit.post' ? 'o'.($message_id ? $message_id : $parent_id) : 'o';
    } else {
      $anchor = 'bottom';
    }  
} elseif($alert) {
    $anchor = 'op';
}
$pt = $site=='Topic' ? '25' : '0';
$pb = $site=='Topic' ? '25' : '0';
$iid = mt_rand(1,50000);
$sub_cat = commune::getCategories($id, true);
// ******************************************
// ШАБЛОН ФОРМЫ *****************************
// ******************************************
?>
<?global $user_mod;?>
<? /*
<a name="o"></a>
<a name="<?= !empty($alert) ? 'error' : '';?>"></a>
*/ ?>
<?php
$member = commune::GetCommuneByMember($_SESSION['uid']);
$draft_id = !$draft_id ? intval($_GET['draft_id']) : $draft_id;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
$draft_data = drafts::getDraft($draft_id, get_uid(false), 4);
if($draft_data) {
    $mess['category_id'] = $draft_data['category'];
    $title = htmlspecialchars($draft_data['title'],ENT_COMPAT,'cp1251');
    if(strpos($draft_data['msg'], '<br />') === false && $draft_data['commune_id'] == commune::COMMUNE_BLOGS_ID) {
        $msgtext = nl2br($draft_data['msg']);
    } else {
        $msgtext = $draft_data['msg'];
    }
    $youtube_link = $draft_data['yt_link'];
    $question = htmlspecialchars($draft_data['poll_question'],ENT_COMPAT,'cp1251');
    $multiple = $draft_data['poll_type'];
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
if (!isset($user_mod)) {
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
<form <?= ($alert ? ' id="idAlertedCommentForm"' : 'frm id="msg_form"') ?> action="#error<?= ($site == 'Topic' && $action == 'Create.post' && $top_id == $parent_id ? '-last' : ($site == 'Topic' ? 'p' : ($message_id ? '' : ($parent_id ? $parent_id : "")))) ?>" method="post" enctype="multipart/form-data">
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
    <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-new-post">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_90">
                <label class="b-form__name b-form__name_padtop_5 b-form__name_fontsize_13 b-form__name_width_90">Заголовок</label>
            </td>
            <td class="b-layout__right">
                <div class="b-combo">
                    <div class="b-combo__input b-combo__input_width_612 b-combo__input_max-width_612 <?= !empty($alert['title']) ? ' b-combo__input_error' : '' ?>">
                        <input id="f_title" autofocus="autofocus" size="80" class="b-combo__input-text" onclick="if($('title_error')) $('title_error').style.display = 'none';" type="text" maxlength="<?= commune::MSG_TITLE_MAX_LENGTH ?>" name="title" value="<?= $title ?>" onfocus="isFocus = true;" onblur="isFocus = false;"/>
                        <label class="b-combo__label" for="c1"></label>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
<? // текстовое поле ?>
<div class="b-form b-form_padleft_90">
    <div id="wysiwyg-error" class="<?= !empty($alert['msgtext']) ? 'b-combo__input_error' : '' ?>">
        <textarea id="msg_source" name="msgtext_source" style="display:none;height:200px;"><?= htmlspecialchars($msgtext,ENT_COMPAT,'cp1251'); ?></textarea>
        <textarea class="<?= commune::IS_NEW_WYSIWYG ? "ckeditor" : "wysiwyg"?>" conf="insertcode" style="height:200px; width:100%;" onfocus="if($(this) && $('msgtext_error')) $('msgtext_error').style.display = 'none';" id="msg" name="msgtext" rows="5" cols="10"><?= commune::IS_NEW_WYSIWYG ? htmlspecialchars(html2wysiwyg($msgtext),ENT_COMPAT,'cp1251') : htmlspecialchars($msgtext,ENT_COMPAT,'cp1251'); ?></textarea>
    </div>
</div>
<? // загрузка файлов ?>
<div class="b-form b-form_padleft_90 b-file">
    <div class="b-fon">
        <div id="attachedfiles" class="b-fon__body_pad_10 b-icon-layout i-button">
            <table id="attachedfiles_table" class="b-icon-layout__table" cellpadding="0" cellspacing="0" border="0">
                <tr id="attachedfiles_template" style="display:none" class="b-icon-layout__tr">
                    <td class="b-icon-layout__icon"><i class="b-icon"></i></td>
                    <td class="b-icon-layout__files"><a href="javascript:void(0)" class="b-icon-layout__link">&nbsp;</a>&nbsp;</td>
                    <td class="b-icon-layout__operate b-icon-layout__operate_padleft_10"><a href="javascript:void(0)" class="b-button b-button_m_delete"></a></td>
                </tr>
            </table>

			

            <div id='attachedfiles_error' style='display: none;'>
                <table class='b-icon-layout wdh100'>
                    <tr>
                        <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/ico_error.gif' alt='' width='22' height='18'></td>
                        <td class='b-icon-layout__files' id='attachedfiles_errortxt' colspan='2'></td>
                        <td class='b-icon-layout__operate'><a id="attachedfiles_hide_error" class='b-icon-layout__link b-icon-layout__link_dot_666' href='javascript:void(0)'>Скрыть</a></td>
                    </tr>
                </table>
            </div>
            <div id='attachedfiles_uploadingfile' style='display:none'>
                <table class='b-icon-layout wdh100'>
                    <tr>
                        <td class='b-icon-layout__icon' style="width:45px;">
                        	<i class="b-spinner__white"></i>
                        </td>
                        <td class='b-icon-layout__files' style='vertical-align:middle;'>Идет загрузка файла…</td>
                        <td class='b-icon-layout__size'>&nbsp;</td>
                        <td class='b-icon-layout__operate'>&nbsp;</td>
                    </tr>
                </table>
            </div>
            <div id='attachedfiles_deletingfile' style='display: none;'>
                <table class='b-icon-layout wdh100'>
                    <tr>
                        <td class='b-icon-layout__icon' style="width:45px;">
                        	<i class="b-spinner__white"></i>
						</td>
                        <td class='b-icon-layout__files' style='vertical-align:middle;'>Идет удаление файла…</td>
                        <td class='b-icon-layout__size'>&nbsp;</td>
                        <td class='b-icon-layout__operate'>&nbsp;</td>
                    </tr>
                </table>
            </div>
            <div class='b-fon__item' id='attachedfiles_error' style='display: none;'>
                <table class='b-icon-layout wdh100'>
                    <tr>
                        <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/ico_error.gif' alt='' width='22' height='18'></td>
                        <td class='b-icon-layout__files' id='attachedfiles_errortxt' colspan='2'></td>
                        <td class='b-icon-layout__operate'><a class='b-icon-layout__link b-icon-layout__link_dot_666' href='#' onClick='attachedFiles.hideError(); return false;'>Скрыть</a></td>
                    </tr>
                </table>
            </div>
            <table class="b-file_layout" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="b-file__button">            
                        <div class="b-file__wrap" id="attachedfiles_file_div">
                            <input id="attachedfiles_file" name='attachedfiles_file' class="b-file__input" type="file" />
                            <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_grey">Загрузить файлы</a>
                        </div>
                    </td>
                    <td class="b-file__text">
                        <div class="b-filter">
                            <div class="b-filter__body b-filter__body_padtop_5">
                                <a href="javascript:void(0)" class="b-filter__link b-filter__link_fontsize_11 b-filter__link_dot_41" onclick="$('attachedfiles_info').removeClass('b-shadow_hide');">Требования к файлам</a>
                            </div>
                            <div id="attachedfiles_info" class="b-shadow b-shadow_hide b-filter__toggle b-shadow__margleft_-110  b-shadow_top_30">
                                                <div class="b-shadow__body b-shadow__body_pad_15 b-shadow_width_270 b-shadow__body_bg_fff">
                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">Разрешается добавлять не более <span class="b-shadow__txt b-shadow__txt_bold">10 файлов</span> общим объемом не более 5 МБ.</div>
                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">jpg, gif и png размером до <span class="b-shadow__txt b-shadow__txt_bold">470х1000 пикс.</span> и весом не более 300 КБ будут вставлены в текст поста, остальные файлы будут приложены к нему.</div>
                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11">Запрещенные форматы: ade, adp, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msk, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>
                                                </div>
                                <div class="b-shadow__icon_nosik"></div>
                                <div id="attachedfiles_close_info" class="b-shadow__icon_close"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function () {
        var attachedfiles_list = new Array();
        <?php
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        $attachedfiles_session = $_POST['attachedfiles_session'];
        $attachedfiles = new attachedfiles($attachedfiles_session);
        
        if($draft_id) {
            if(!$attachedfiles_session) {
                $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($draft_id, 2);
                if($attachedfiles_tmpdraft_files) {
                    $attachedfiles_prj_files = array();
                    foreach($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
                        $attachedfiles_draft_files[] = $attachedfiles_draft_file;
                    }
                    $attachedfiles->setFiles($attachedfiles_draft_files, 1);
                }
            }
        } else {
            if($action=='Edit.post' && !$alert) {
                $attachedfiles_tmpblog_files = commune::getAttachedFiles($top['id']);
                if($attachedfiles_tmpblog_files) {
                    $attachedfiles_blog_files = array();
                    foreach($attachedfiles_tmpblog_files as $attachedfiles_blog_file) {
                        $attachedfiles_blog_files[] = $attachedfiles_blog_file;
                    }
                    $attachedfiles->setFiles($attachedfiles_blog_files);
                }    
            }
        }
        
        $attachedfiles_files = $attachedfiles->getFiles();
        if($attachedfiles_files) {
            $n = 0;
            foreach($attachedfiles_files as $attachedfiles_file) {
                echo "attachedfiles_list[{$n}] = new Object;\n";
                echo "attachedfiles_list[{$n}].id = '".md5($attachedfiles_file['id'])."';\n";
                echo "attachedfiles_list[{$n}].name = '{$attachedfiles_file['orig_name']}';\n";
                echo "attachedfiles_list[{$n}].path = '".WDCPREFIX."/{$attachedfiles_file['path']}{$attachedfiles_file['name']}';\n";
                echo "attachedfiles_list[{$n}].size = '".ConvertBtoMB($attachedfiles_file['size'])."';\n";
                echo "attachedfiles_list[{$n}].type = '{$attachedfiles_file['type']}';\n";
                $n++;
            }
        }
        ?>
                
        commune_attached = {
            sess: '<?=$attachedfiles->getSession()?>',
            max_files: '<?=commune::MAX_FILES?>',
            max_file_size: '<?=commune::MAX_FILE_SIZE?>',
            disallowed: '<?=implode(', ', $GLOBALS['disallowed_array'])?>',
            uid: '<?=get_uid(false)?>',
            list: attachedfiles_list
        };

        document.getElementById('f_title').focus();
    })();
</script>
<input type='hidden' id='attachedfiles_uid' name='attachedfiles_uid' value='<?=get_uid(false)?>'>
<input type='hidden' id='attachedfiles_action' name='attachedfiles_action' value=''>
<input type='hidden' id='attachedfiles_delete' name='attachedfiles_delete' value=''>
<input type='hidden' id='attachedfiles_type' name='attachedfiles_type' value='commune'>
<input type='hidden' id='attachedfiles_session' name='attachedfiles_session' value='<?=$attachedfiles->getSession()?>'>
<iframe id='attachedfiles_hiddenframe' name='attachedfiles_hiddenframe' style='display: none;'></iframe>

<? // добавить видео ?>
<div class="b-form b-form_padleft_90" id="add_yt_box" style="display:<?= $youtube_link ? 'none' : 'block' ?>">
    <a href="javascript:void(0)" class="b-button b-button_m_add" id="add_yt_box1"></a>
    <div class="b-form__txt b-form__txt_padleft_5">
        <a id="add_yt_box2" class="b-form__link b-form__link_dot_0f71c8" href="javascript:void(0)">Добавить видео</a>
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
                                <input class="b-combo__input-text b-combo__input-text_color_a7" name="youtube_link" id="youtube_link" type="text" size="80" value="<?= $youtube_link ? $youtube_link : '' ?>">
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__right b-layout__right_right b-layout__right_width_30">
                        <a href="javascript:void(0)" id="hide_yt_box" class="b-button b-button_m_delete"></a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<? // добавить опрос ?>
<div class="b-form b-form_padleft_90 b-form_padbot_20 <?= $question || count($answers)-1 || !empty($answers[0][answer]) ? 'b-form_hide' : '' ?>" id="add_poll">
    <a id="add_poll1" class="b-button b-button_m_add" href="javascript:void(0)"></a>
    <div class="b-form__txt b-form__txt_padleft_5">
        <a id="add_poll2" class="b-form__link b-form__link_dot_0f71c8" href="javascript:void(0)">Добавить опрос</a>
    </div>
</div>
<div class="b-form b-form_padleft_90  b-form_padbot_20 <?= $question || count($answers)-1 || !empty($answers[0][answer]) ? '' : 'b-form_hide' ?>" id="pool_box">
    <div class="b-fon ">
	<div class="b-fon__body b-fon__body_pad_10 b-fon__body_bg_f0ffdf b-layout i-button">
            <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left">
                        <div class="b-textarea<?= $alert['polls'] ? " b-textarea_error" : "" ?>">
                            <textarea class="b-textarea__textarea" name="question" id="question" cols="80" rows="5"><?= $question ?></textarea>
                        </div>
                    </td>
                    <td class="b-layout__right b-layout__right_right b-layout__right_width_30">
                        <a href="javascript:void(0)" id="hide_poll" class="b-button b-button_m_delete"></a>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="multiple" id="multiple" value="<?= (empty($multiple) || $multiple == 0) ? 0 : 1 ?>">
            <div>
                <div id="poll-radio"<? if (!(empty($multiple) || $multiple == 0)) { ?> style="display:none"<? } ?> class="b-menu b-menu_rubric b-menu_padtop_10">
                    <ul class="b-menu__list b-menu__list_margleft_0">
                        <li class="b-menu__item"><div class="b-menu__txt b-menu__txt_padtop_3">Можно выбрать</div></li>
                        <li class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">только один вариант ответа</span></span></li>
                        <li class="b-menu__item"><a href="javascript:void(0)" class="b-menu__link b-menu__link_bordbot_dot_0f71c8">несколько вариантов ответа</a></li>
                    </ul>
                </div>
                <div id="poll-check"<? if (!(!empty($multiple) || $multiple == 1)) { ?> style="display:none"<? } ?> class="b-menu b-menu_rubric b-menu_padtop_10">
                    <ul class="b-menu__list b-menu__list_margleft_0">
                        <li class="b-menu__item"><div class="b-menu__txt b-menu__txt_padtop_3">Можно выбрать</div></li>
                        <li class="b-menu__item"><a href="javascript:void(0)" class="b-menu__link b-menu__link_bordbot_dot_0f71c8">только один вариант ответа</a></li>
                        <li class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">несколько вариантов ответа</span></span></li>
                    </ul>
                </div>

                <?
                $i = 0;
                $c = count($answers);
                foreach ($answers as $answer) {
                ?>                            
                <table id="poll-<?= $i ?>" class="b-layout__table b-layout__table_width_full b-layout__table_margtop_10 b-poll-edit-answer" cellpadding="0" cellspacing="0" border="0">
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
                                <div class="b-combo__input<?= $alert['polls_question'] && !$answer['answer'] ? " b-combo__input_error" : "" ?>">
                                    <input id="answer_input_<?= $i ?>" maxlength="<?= commune::POLL_ANSWER_CHARS_MAX ?>" value="<?= $answer['answer'] ?>" name="<?= ($answer['id'] ? "answers_exists[{$answer['id']}]" : "answers[]") ?>" tabindex="20<?= $i ?>" class="b-combo__input-text" type="text" size="80">
                                </div>
                            </div>
                        </td>
                        <td class="b-layout__right b-layout__right_right b-layout__right_width_30 b-layout__right_valign_middle">
                            <a href="javascript:void(0)" id="del_answer_btn_<?= $i ?>" class="b-button b-button_m_delete " style="display: none"></a>
                        </td>
                    </tr>
                    <? if (false && $answer['id'] && !(hasPermissions('communes') || $edit_msg['fromuser_id'] == $uid)) { ?>
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
<? if (!$parent_id && ($user_mod & (commune::MOD_COMM_AUTHOR | commune::MOD_COMM_ADMIN)) || hasGroupPermissions('administrator')) { // только для админов и модеров сайта/сообщества ?>
    <div class="b-form b-form_padleft_90">
        <div class="b-check">
            <input class="b-check__input" type="checkbox" id="idTPos<?= $iid ?>" name="pos" value="1" <?= ($pos ? ' checked' : '') ?> />
            <label for="idTPos<?= $iid ?>" class="b-check__label b-check__label_fontsize_13">Закрепить тему наверху</label>
        </div>
    </div>
<? } ?>

<? // сообщение об ошибке ?>
<? if (!empty($alert)) { ?>
<div id="msgtext_error" class="b-form b-form_padbot_null b-form_padleft_90">
    <? foreach($alert as $context=>$al) {?>
        <div id="msgtext_error_<?= $context ?>" class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_color_c10600"><span class="b-icon b-icon_sbr_rattent"></span><?= $al ?></div>
    <? } ?>
</div>
<? } ?>
<? // кнопка сохранить
$saveCaption = $action == "Create.post" ? "Опубликовать" : "Сохранить";
?>
<div class="b-buttons b-buttons_padleft_90 b-buttons_padtop_30 b-buttons_padbot_20">
    <a href="javascript:void(0)" id="topic_form_submit" class="b-button b-button_flat b-button_flat_green b-button_margright_10"><?= $saveCaption ?></a>
    <input type="hidden" id="draft_id" name="draft_id" value="<?=intval($draft_id)?>" />
    <input type="hidden" id="draft_post_id" name="draft_post_id" value="<?=$message_id?>" />
    <span id="draft_time_save" class="b-buttons__txt b-buttons__txt_hide"></span>&nbsp;&nbsp;
    <div class="b-buttons__txt">
    <a id="save_as_draft" class="b-buttons__link b-buttons__link_color_0f71c8" href="javascript:void(0)"></a>
    <span class="b-buttons__txt">или</span>
    <a class="b-buttons__link b-buttons__link_color_c10601" href="?">просто выйти</a>
    </div>
</div>

<input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>">
</form>
<?
// ******************************************
// КОНЕЦ ШАБЛОНА ФОРМЫ **********************
// ******************************************
?>
</div>
<? //*************************************************
// конец формы редактирования поста ?>
                    <? } ?>
                </td>
                <td class="b-layout__right b-layout__right_width_210 b-layout__right_padleft_30">
                    <div class="b-layout__txt"><a class="b-layout__link" href="<?=WDCPREFIX?>/about/documents/appendix_2_regulations.pdf">Полный перечень правил</a></div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">Пожалуйста, руководствуйтесь пунктом 7 при публикации тем в сообществах</div>
                </td>
            </tr>
        </table>
