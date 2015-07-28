<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/drafts.common.php");

/**
* Получить и заполнить форму данными из черновика
*
* @param    int   $draft_id    ID черновика
* @param    int   $type        Тип черновика
*/
function FillDraftForm($draft_id, $type) {
    $objResponse = new xajaxResponse();
    session_start();
    $uid = get_uid(false);
    $draft_id = intval($draft_id);
    if($uid) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
        switch($type) {
            case 1:
                // Проекты
                /*$draft = drafts::getDraft($draft_id, $uid, 1);
                if($draft) {
                    $objResponse->assign("draft_id", "value", $draft['id']);
                    $objResponse->assign("f1", "value", $draft['name']);
                    $objResponse->assign("f2", "innerHTML", $draft['descr']);
                    $objResponse->assign("f3", "value", $draft['cost']);
                    if($draft['pro_only']=='t') {
                        $objResponse->script('$("f22").set("checked", true);');
                    } else {
                        $objResponse->script('$("f22").set("checked", false);');
                    }
                    if($draft['prefer_sbr']=='t') {
                        $objResponse->script('$("prefer_sbr").set("checked", true);');
                    } else {
                        $objResponse->script('$("prefer_sbr").set("checked", false);');
                    }
                    $objResponse->script('$("f3").set("checked", false);');
                    $objResponse->script('$("fcurrency").set("value", '.$draft['currency'].');');
                    $objResponse->script('$("fpriceby").set("value", '.$draft['priceby'].');');
                    if($draft['kind']==7) {
                        $objResponse->assign("end_date", "value", (string) $draft['p_end_date']);
                        $objResponse->assign("win_date", "value", (string) $draft['p_win_date']);
                    } else {
                        if($draft['kind']==4) {
                            $objResponse->script('$("f8").set("checked", true);');
                            $objResponse->script('ShowCities();');
                            $objResponse->script('$("fcountry").set("value", '.intval($draft['country']).');');
                            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
                            $cities = city::GetCities(intval($draft['country']));
                            $out_text = "<select id=\"fcity\" name=\"city\" class=\"apf-select\"><option value=\"0\">Не выбрано</option>";
                        	if($cities) foreach ($cities as $cityid => $city)
                                $out_text .= "<option value=".$cityid.">".$city."</option>";
                            $out_text .= "</select>";
                            $objResponse->assign("frm_city","innerHTML",$out_text);
                            $objResponse->script('$("fcity").set("value", '.intval($draft['city']).');');
                        }
                    }
                    $categories = preg_split("/,/",$draft['categories']);
                    if($categories) {
                        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
                        $cats = professions::GetAllGroupsLite();
                        $out = '<div class="apf-or" style="margin: 0 0 -7px 0;" id="cat_con">';
                        foreach($categories as $category) {
                            list($m_cat, $s_cat) = preg_split("/\|/",$category);
                            $out .= '<div class="apf-or-one" id="cat_line">';
                            $out .= '<select class="" style="width: 170px" name="categories[]"  onchange="RefreshSubCategory(this)">';
                            foreach($cats as $cat) {
                                if($cat['id']<=0) continue;
                                $out .= '<option value="'.$cat['id'].'" '.($m_cat==$cat['id'] ? ' selected' : '').'>'.$cat['name'].'</option>';
                            }
                            $out .= '</select>';
                            $out .= '&nbsp;&nbsp;';
            				$out .= '<select name="subcategories[]" style="width: 200px" class="subcat">';
                            $categories_specs = professions::GetAllProfessions($m_cat);
                            for ($i=0; $i<sizeof($categories_specs); $i++) {
                                $out .= '<option value="'.$categories_specs[$i]['id'].'"'.($categories_specs[$i]['id']==$s_cat ? 'selected' : '').'>'.$categories_specs[$i]['profname'].'</option>';
                            }
                            $out .= '<option value="" '.(!$s_cat ?' selected':'').'>Другое</option>';
                            $out .= '</select>&nbsp;&nbsp;';
                            $out .= '</div>';
                        }
                        $out .= '</div>';
                        $objResponse->assign("fcategory", "innerHTML", $out);
                        $objResponse->script("var mx = new MultiInput('cat_con','cat_line'); mx.init();");
                        $objResponse->script('if(is_auto_draft==1) { NextStep(); }');
                    }
                    $objResponse->script('changeBudgetSlider();');
                }*/
                break;
            case 2:
                // Личка
                $draft = drafts::getDraft($draft_id, $uid, 2);
                if($draft) {
                    $objResponse->assign("draft_id", "value", $draft['id']);
                    $objResponse->assign("msg", "innerHTML", $draft['msg']);
                }
                break;
            case 3:
                // Блоги
                $draft = drafts::getDraft($draft_id, $uid, 3);
                if($draft) {
                    $objResponse->assign("draft_id", "value", $draft['id']);
                    $objResponse->assign("name", "value", $draft['title']);
                    $objResponse->assign("msg", "value", $draft['msgtext']);

                    if($draft['yt_link']) {
                        $objResponse->assign("fyt_link", "value", $draft['yt_link']);
                        $objResponse->script('$("yt_link").setStyle("display","block");');
                    } else {
                        $objResponse->assign("fyt_link", "value", '');
                        $objResponse->script('$("yt_link").setStyle("display","none");');
                    }
                    if($draft['is_close_comments']=='t' || $draft['is_private']=='t') {
                        $objResponse->script('$("settings").setStyle("display","block");');
                    } else {
                        $objResponse->script('$("settings").setStyle("display","none");');
                    }
                    
                    if($draft['is_close_comments']=='t') { 
                        $objResponse->script('$("ch_close_comments").set("checked",true);');
                    } else {
                        $objResponse->script('$("ch_close_comments").set("checked",false);');
                    }
                    
                    if($draft['is_private']=='t') { 
                        $objResponse->script('$("ch_is_private").set("checked",true);');
                    } else {
                        $objResponse->script('$("ch_is_private").set("checked",false);');
                    }

                    $objResponse->script('$("fcategory").set("value","'.$draft['category'].'|0");');

                    $answers = preg_split("/\|-\|-\|/", htmlspecialchars($draft['poll_answers']), -1, PREG_SPLIT_NO_EMPTY);
                    
                    $show = TRUE;
                    if ( empty($answers) ) {
                        $answers = array( '' );
                        $show = FALSE;
                    }
                    
                    if(!empty($draft['poll_question']) || (count($answers) && $show)) { 
                        $objResponse->script('$("trpollquestion").setStyle("display", "table-row");');
                        $objResponse->script('$("trpolltype").setStyle("display", "table-row");');  
                    } else {
                        $objResponse->script('$("trpollquestion").setStyle("display", "none");');
                        $objResponse->script('$("trpolltype").setStyle("display", "none");');  
                    }
                    
                    $objResponse->assign("poll-question", "value", $draft['poll_question']);
                    $objResponse->assign("poll-question-source", "value", $draft['poll_question']);
                    if($draft['poll_type']==0) {
                        $objResponse->script('$("fmultiple0").set("checked", true);');
                        $objResponse->script('$("fmultiple1").set("checked", false);');
                    } else {
                        $objResponse->script('$("fmultiple0").set("checked", false);');
                        $objResponse->script('$("fmultiple1").set("checked", true);');
                    }

                    if(count($answers)) {
                        $objResponse->script('$$(".poll-line").destroy();');
                        $i = 0;
                        $c = count($answers);
                        $out = '';
                        $insert_id = "trpolltype";
                        foreach($answers as $answer) {
                            $objResponse->insertAfter($insert_id, "tr", "poll-{$i}");
                            $objResponse->script('$("poll-'.$i.'").set("class", "poll-line");');
                            $objResponse->script('$("poll-'.$i.'").set("valign", "top");');
                            $out = '';
                            $out .= '<td>Ответ #<span class="poll-num">'.($i+1).'</span></td>';
                            $out .= '<td>';
						    $out .= '<table cellpadding="0" cellspacing="0" border="0">';
						    $out .= '<tr>';
							$out .= '<td><input maxlength="'.blogs::MAX_POLL_ANSWER_CHARS.'" class="poll-answer" type="text" value="'.addslashes($answer).'" name="answers[]" tabindex="20'.$i.'"></td>';
							$out .= '<td class="poll-btn"><a class="poll-del" href="javascript: return false" onclick="poll.del(\'Blogs\', '.$i++.'); return false;"><img src="/images/delpoll.png" width="15" height="15" border="0" alt="Удалить ответ" title="Удалить ответ"></a></td>';
							$out .= '<td class="poll-btn"><span class="poll-add">&nbsp;</span></td>';
    						$out .= '</tr>';
						    $out .= '</table>';
						    $out .= '</td>';
                            $objResponse->assign("poll-".($i-1), "innerHTML", $out);
                            $insert_id = "poll-".($i-1);
                        }
                        $objResponse->script("poll.init('Blogs', document.getElementById('frm'), ".blogs::MAX_POLL_ANSWERS.", '');");
                    } 
                    
                    if (!empty($draft['poll_question']) || (count($answers) && $show) ) {
                        $objResponse->script('$("poll-0").setStyle("display", "table-row");');
                    } else {
                        $objResponse->script('$("poll-0").setStyle("display", "none");');
                    }

                }
                break;
            case 4:
                // Сообщества
                $draft = drafts::getDraft($draft_id, $uid, 4);
                if($draft) {
                    $objResponse->assign("draft_id", "value", $draft['id']);
                    $objResponse->script('$("f_category_id").set("value", "'.$draft['category'].'");');
                    $objResponse->assign("f_title", "value", $draft['title']);
                    $objResponse->assign("msg", "value", $draft['msg']);
                    $objResponse->script('$each($$("textarea.wysiwyg"), function(el) { if($(el).retrieve("MooEditable")) { if(el.get("id")=="msg") { $(el).retrieve("MooEditable").setContent("'.preg_replace('/"/','\"',preg_replace("/[\r\n]/",'\n',$draft["msg"])).'"); } } });');
                    if($draft['yt_link']) {
                        $objResponse->script('$("yt_box").setStyle("display","block");');
                        $objResponse->assign("youtube_link", "value", $draft['yt_link']);
                    }
                    if($draft['close_comments']=='t' || $draft['is_private']=='t') {
                        $objResponse->script('$("additional_box").setStyle("display","block");');
                        if($draft['close_comments']=='t') { $objResponse->script('$("ch_close_comments").set("checked",true);'); }
                        if($draft['is_private']=='t') { $objResponse->script('$("ch_is_private").set("checked",true);'); }
                    }
                    if($draft['poll_question'] || $draft['poll_answers']) { $objResponse->script('$("pool_box").setStyle("display", "block");'); } 
                    $objResponse->assign("question", "value", $draft['poll_question']);
                    if($draft['poll_type']==0) {
                        $objResponse->script('$("f_multiple0").set("checked", true);');
                        $objResponse->script('$("f_multiple1").set("checked", false);');
                    } else {
                        $objResponse->script('$("f_multiple0").set("checked", false);');
                        $objResponse->script('$("f_multiple1").set("checked", true);');
                    }
                    $answers = preg_split("/\|-\|-\|/", $draft['poll_answers'], -1, PREG_SPLIT_NO_EMPTY);

                    if(count($answers)) {

                        $i = 0;
                        $c = count($answers);
                        $out = '';
                        foreach($answers as $answer) {
                            $out .= '<li class="poll-line" id="poll-'.$i.'">'."\n";
                            $out .= '<span class="btns" >';
                            if ($i<count($answers)-1) {
                                $out .= '<a class="poll-del" href="javascript: return false" onclick="poll.del(\'Commune\', '.$i++.'); return false;">';
                                $out .= '<img src="/images/btns/btn-remove-s.png" alt=""/>';
                                $out .= '</a>';
                            } else {
                                $out .= '<a class="poll-add" href="javascript: return false">';
                                $out .= '<img src="/images/btns/btn-add-s.png" alt=""/>';
                                $out .= '</a>'."\n";
                            }
                            $out .= '</span>'."\n";
                            $out .= '<input class="poll-answer" maxlength="'.commune::POLL_ANSWER_CHARS_MAX.'" type="text" value="'.preg_replace("/\"/",'\"',$answer).'" name="answers[]" tabindex="20'.$i.'"/>'."\n";
                            $out .= '</li>'."\n";
                        }
                        $out = $out;

                        $objResponse->assign("poll_ans_home", "innerHTML", $out);
                        if($draft['post_id']) {
                            $objResponse->script("poll.init('Commune', document.getElementById('idEditCommentForm_{$draft['post_id']}'), 10, '');");
                        } else {
                            $objResponse->script("poll.init('Commune', document.getElementById('editmsg'), 10, '');");
                        }
                    }
                    
                    if (count($answers) || !empty($draft['poll_question'])) {
                        $objResponse->script('$("poll-0").setStyle("display", "table-row");');
                    }
                    
                }
                break;
        }
    }
    return $objResponse;
}

/**
* Сохранить черновик сообщества
*
* @param    array   $frm    Информация о посте в сообществе
*/
function SaveDraftCommune($frm) {
    session_start();
    $uid = get_uid(false);
    $aRes = array();
    if($uid) {
        $frm['uid'] = $uid;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        $frm['msgtext'] = $frm['msgtext_source'];
        $draft = drafts::SaveCommune($frm);
        $aRes['html'] = iconv('CP1251', 'UTF-8', "Сообщение сохранено в ".preg_replace("/^.* /","",preg_replace("/:\d{2}$/","",$draft['date'])));
        $aRes['id'] = $draft['id'];
        $aRes['success'] = true;
    }
    else {
        $aRes['success'] = false;
    }
    echo json_encode( $aRes );
}

/**
* Проверяет наличие ранее сохраненных черновиков для сообществ
*
*/
function CheckDraftsCommune() {
   $objResponse = new xajaxResponse();
    session_start();
    $uid = get_uid(false);
    if($uid) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        $count = drafts::CheckCommune($uid);
        if($count) {
            $objResponse->script('$("draft_div_info").setStyle("display","block")');
            $objResponse->assign('draft_div_info_text', 'innerHTML', 'Не забывайте, у вас в черновиках <a href="/drafts/?p=communes">сохранено '.$count.' '.getSymbolicName($count, 'messages').' в сообществах</a>');
        }
    }
    return $objResponse;
}

/**
* Сохранить черновик блога
*
* @param    array   $frm    Информация о после в блоге
*/
function SaveDraftBlog($frm) {
    session_start();
    $uid = get_uid(false);
    $aRes = array();
    if($uid) {
        $frm['uid'] = $uid;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        $draft = drafts::SaveBlog($frm);
        $aRes['html'] = iconv('CP1251', 'UTF-8', "Текст блога сохранен в  ".preg_replace("/^.* /","",preg_replace("/:\d{2}$/","",$draft['date'])));
        $aRes['id'] = $draft['id'];
        $aRes['success'] = true;
        $drafts = drafts::getCounts($uid);
        $aRes['count']   = $drafts["blogs"];  
    }
    else {
        $aRes['success'] = false;
    }
    echo json_encode( $aRes );
}

/**
* Проверяет наличие ранее сохраненных черновиков для блогов
*
*/
function CheckDraftsBlog() {
   $objResponse = new xajaxResponse();
    session_start();
    $uid = get_uid(false);
    if($uid) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        $count = drafts::CheckBlogs($uid);
        if($count) {
            $objResponse->script('$("draft_div_info").setStyle("display","block")');
            $objResponse->assign('draft_div_info_text', 'innerHTML', 'Не забывайте, у вас в черновиках <a href="/drafts/?p=blogs">'.ending($count, 'сохранен', 'сохранено', 'сохранено').' '.$count.' '.getSymbolicName($count, 'blogs').'</a>');
        }
    }
    return $objResponse;
}

/**
* Сохнить черновик проекта
*
* @param    array   $prj    Информация о проекте
*/
function SaveDraftProject($prj, $newTemplate = false) {
    session_start();
    $uid = get_uid(false);
    if($uid) {
        $prj['uid'] = $uid;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        $draft = $newTemplate ? drafts::SaveProjectNew($prj) : drafts::SaveProject($prj);
        $aRes['html'] = iconv('CP1251', 'UTF-8', "Текст проекта сохранен в  ".preg_replace("/^.* /","",preg_replace("/:\d{2}$/","",$draft['date'])));
        $aRes['id'] = $draft['id'];
        $aRes['success'] = true;
    }
    else {
        $aRes['success'] = false;
    }
    echo json_encode( $aRes );
}

/**
* Сохнить черновик личного сообщения
*
* @param    array   $msg    Информация о сообщении
*/
function SaveDraftContacts($msg) {
    session_start();
    $uid = get_uid(false);
    $aRes = array();
    if($uid) {
        $msg['uid'] = $uid;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        $draft = drafts::SaveContacts($msg);
        $aRes['html'] = iconv('CP1251', 'UTF-8', "Текст сообщения сохранен в  ".preg_replace("/^.* /","",preg_replace("/:\d{2}$/","",$draft['date'])));
        $aRes['id'] = $draft['id'];
        $aRes['success'] = true;
    }
    else {
        $aRes['success'] = false;
    }
    echo json_encode( $aRes );
}

/**
* Проверяет наличие ранее сохраненных черновиков для личных сообщений
*
* @param    string  $to_login   Получатель сообщений
*/
function CheckDraftsContacts($to_login) {
   $objResponse = new xajaxResponse();
    session_start();
    $uid = get_uid(false);
    if($uid) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        $count = drafts::CheckContacts($to_login, $uid);
        if($count) {
            $objResponse->script('$("draft_div_info").setStyle("display","block")');
            $objResponse->assign('draft_div_info_text', 'innerHTML', 'Не забывайте, у вас в черновиках <a href="/drafts/?p=contacts" class="blue"><strong>сохранено '.$count.' '.getSymbolicName($count, 'messages').'</strong></a> для ['.$to_login.']');
        }
    }
    return $objResponse;
}

/**
* Проверяет наличие ранее сохраненных черновиков для проектов
*
*/
function CheckDraftsProject($new = false) {
   $objResponse = new xajaxResponse();
    session_start();
    $uid = get_uid(false);
    if($uid) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        $count = drafts::CheckProjects($uid);
        if($count) {
            if ($new) {
                $showDraftsCount = 3;
                $moreDraftsCount = $count - $showDraftsCount;
                $drafts = drafts::getUserDrafts($uid, 1, $showDraftsCount);
                ob_start();
                include($_SERVER['DOCUMENT_ROOT'] . "/public/new/tpl.drafts_block.php");
                $html = ob_get_clean();
                //$objResponse->script('$("draft_div_info").setStyle("display","block")');
                $objResponse->assign('draft_div_info_text', 'innerHTML', $html);
            } else {
                // после введения нового шаблона добавления проектов, это можно удалить
                $objResponse->script('$("draft_div_info").setStyle("display","block")');
                $objResponse->assign('draft_div_info_text', 'innerHTML', 'Не забывайте, у вас в черновиках <a href="/drafts/?p=projects">'.ending($count, 'сохранен', 'сохранено', 'сохранено').' '.$count.' '.getSymbolicName($count, 'projects').'</a>');
            }
        }
    }
    return $objResponse;
}


/**
* Публикация черновика
*
* @param    int     $draft_id   ID черновика
* @param    int     $type       Тип черновика
* @param    bool    $is_edit    false - публикация нового поста/прокта, true - публикация существующего поста/проекта
*/
function PostDraft($draft_id, $type, $is_edit=false) {
    $objResponse = new xajaxResponse();
    session_start();
    $draft_id = intval($draft_id);
    $uid = get_uid(false);
    if($uid) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        $draft = drafts::getDraft($draft_id, $uid, $type);
        if($draft) {
            switch($type) {
                case 2:
                    // Личка
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
                    $attachedfiles = new attachedfiles($attachedfiles_session);

                    $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($draft_id, 3);
                    if($attachedfiles_tmpdraft_files) {
                        $attachedfiles_draft_files = array();
                        foreach($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
                            $attachedfiles_draft_files[] = $attachedfiles_draft_file;
                        }
                        $attachedfiles->setFiles($attachedfiles_draft_files);
                    }



                    $objResponse->assign("f_attachedfiles_session", "value", $attachedfiles->getSession());
                    $objResponse->assign("f_msg", "innerHTML", $draft['msg']);
                    $objResponse->assign("f_msg_to", "value", $draft['to_login']);
                    $objResponse->assign("f_draft_id", "value", $draft['id']);
                    $objResponse->assign("f_to_login", "value", $draft['to_login']);
                    $objResponse->script("var attrAction = document.createAttribute('action'); attrAction.value='/contacts/?from=".$draft['to_login']."'; $('f_frm').setAttributeNode(attrAction);");
                    $objResponse->script('$("f_frm").submit();');
                    break;
                case 3:
                    // Блоги
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
                    $objResponse->assign("f_draft_id", "value", $draft['id']);
                    $objResponse->assign("f_msg", "value", $draft['msgtext']);
                    $objResponse->assign("f_yt_link", "value", $draft['yt_link']);
                    if($draft['is_close_comments']=='t') { $objResponse->script('$("f_is_close_comments").set("checked",true);'); }
                    if($draft['is_private']=='t') { $objResponse->script('$("f_is_private").set("checked",true);'); }
                    $objResponse->assign("f_category", "value", $draft['category'].'|0');
                    if($is_edit) {
                        $blogmsg = blogs::GetMsgInfo($draft['post_id'], $error, $perm);
                        $objResponse->assign("f_msg_name", "value", $draft['title']);
                        $objResponse->assign("f_tr", "value", $blogmsg['thread_id']);
                        $objResponse->assign("f_olduser", "value", $blogmsg['fromuser_id']);
                        $objResponse->assign("f_reply", "value", $draft['post_id']);
                        $objResponse->assign("f_action", "value", 'change');
                        $objResponse->assign("f_msg_name", "value", $draft['title']);
                        $objResponse->assign("f_draft_post_id", "value", $draft['post_id']);
                        $objResponse->script("var attrAction = document.createAttribute('action'); attrAction.value='/blogs/view.php?id=".$draft['post_id']."'; $('f_frm').setAttributeNode(attrAction);");
                    } else {
                        $objResponse->assign("f_name", "value", $draft['title']);
                        $objResponse->assign("f_sub_ord", "value", 'new');
                        $objResponse->assign("f_action", "value", 'new_tr');
                        $objResponse->script("var attrAction = document.createAttribute('action'); attrAction.value='/blogs/viewgroup.php?gr=".$draft['category']."&ord=new&tr='; $('f_frm').setAttributeNode(attrAction);");
                    }

                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
                    $attachedfiles = new attachedfiles($attachedfiles_session);

                    $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($draft_id, 1);
                    if($attachedfiles_tmpdraft_files) {
                        $attachedfiles_draft_files = array();
                        foreach($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
                            $attachedfiles_draft_files[] = $attachedfiles_draft_file;
                        }
                        $attachedfiles->setFiles($attachedfiles_draft_files, 1);
                    }
                    $objResponse->assign("f_attachedfiles_session", "value", $attachedfiles->getSession());

                    $objResponse->assign("f_poll_question", "value", $draft['poll_question']);
                    $objResponse->assign("f_poll_type", "value", $draft['poll_type']);

                    $answers = $draft['poll_answers'];
                    if(count($answers)) {
                        $out = '';
                        foreach($answers as $answer) {
                            $out .= '<input type="hidden" value="'.htmlspecialchars($answer, ENT_QUOTES).'" name="answers[]" />';
                        }
                        $objResponse->assign("f_poll_answers", "innerHTML", $out);
                    }

                    $objResponse->script('$("f_frm").submit();');
                    break;
                case 4:
                    // Сообщества
                    $objResponse->assign("f_id", "value", $draft['commune_id']);
                    $objResponse->assign("f_draft_id", "value", $draft['id']);
                    $objResponse->assign("f_category_id", "value", intval($draft['category']));
                    $objResponse->assign("f_title", "value", $draft['title']);
                    $objResponse->assign("f_msgtext", "value", $draft['msg']);
                    $objResponse->assign("f_youtube_link", "value", $draft['yt_link']);
                    if($draft['close_comments']=='t') { $objResponse->script('$("f_close_comments").set("checked",true);'); }
                    if($draft['is_private']=='t') { $objResponse->script('$("f_is_private").set("checked",true);'); }
                    if($is_edit) {
                        $objResponse->assign("f_draft_post_id", "value", $draft['post_id']);
                        $objResponse->assign("f_top_id", "value", $draft['post_id']);
                        $objResponse->assign("f_message_id", "value", $draft['post_id']);
                        $objResponse->assign("f_page", "value", 0);
                        $objResponse->script("var attrAction = document.createAttribute('action'); attrAction.value='".getFriendlyURL('commune', $draft['post_id'])."'; $('f_frm').setAttributeNode(attrAction);");
                        $objResponse->assign("f_action", "value", "do.Edit.post");
                    } else {
                        $objResponse->script("var attrAction = document.createAttribute('action'); attrAction.value='".getFriendlyURL('commune_commune', $draft['commune_id'])."#o'; $('f_frm').setAttributeNode(attrAction);");
                        $objResponse->assign("f_action", "value", "do.Create.post");
                    }

                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
                    $attachedfiles = new attachedfiles($attachedfiles_session);
                    if(!$is_edit) {
                        $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($draft_id, 2);
                        if($attachedfiles_tmpdraft_files) {
                            $attachedfiles_draft_files = array();
                            foreach($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
                                $attachedfiles_draft_files[] = $attachedfiles_draft_file;
                            }
                            $attachedfiles->setFiles($attachedfiles_draft_files, 1);
                        }
                    }
                    $objResponse->assign("f_attachedfiles_session", "value", $attachedfiles->getSession());

                    $objResponse->assign("f_poll_question", "value", $draft['poll_question']);
                    $objResponse->assign("f_poll_type", "value", $draft['poll_type']);

                    $answers = $draft['poll_answers'];
                    if(count($answers)) {
                        $out = '';
                        foreach($answers as $answer) {
                            $out .= '<input type="hidden" value="'.htmlspecialchars($answer, ENT_QUOTES).'" name="answers[]" />';
                        }
                        $objResponse->assign("f_poll_answers", "innerHTML", $out);
                    }

                    $objResponse->script('$("f_frm").submit();');
                    break;
            }
        }
    }
    return $objResponse;
}


$xajax->processRequest();

?>
