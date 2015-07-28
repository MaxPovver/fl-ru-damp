                <?
                if ($_SESSION['uid']) {

                  if ($ban_where) {
                    $ban=$user->GetBan($_SESSION['uid'],$ban_where);
                        ?><div><a name="bottom"></a>
                        <h1>Команда Free-lance.ru заблокировала вам возможность оставлять записи в сервисе «Блоги» <?=($ban["to"] ? "до ".date("d.m.Y  H:i",strtotimeEx($ban["to"])).' ' : '')?>по причине: <?=reformat( $ban["comment"], 24, 0, 0, 1, 24 )?></h1>

<br />
<br />

Если у вас возникли вопросы, напишите нам на <a href="mailto:info@free-lance.ru">info@free-lance.ru</a><br />
<br />

                        </div>
                        <?
                  }
                elseif ($_SESSION['uid'] && (!$read_only || $read_only && !$mod) ) {?>

                <script type="text/javascript">draft_type = 3;</script>

                <div id="editmsg"><a name="bottom"></a>

    <?php
    $count_drafts = drafts::CheckBlogs($uid);
    if($count_drafts) {
    ?>
        <div class="form fs-p drafts-v" id="draft_div_info">
          <b class="b1"></b>
          <b class="b2"></b>
            <div class="form-in" id="draft_div_info_text"><?='Не забывайте, у вас в черновиках <a href="/drafts/?p=blogs">'.ending($count_drafts, 'сохранен', 'сохранено', 'сохранено').' '.$count_drafts.' '.getSymbolicName($count_drafts, 'blogs').'</a>'?></div>
        <b class="b2"></b>
        <b class="b1"></b>
      </div>
    <?php
    }
    ?>


<?php
$request_uri =  ( $gr ? "?gr={$gr}&" : "?")
                ."ord={$ord}".
                ($edit_msg["id"] ? "&tr={$edit_msg["id"]}" : ( $thread? "&tr={$thread}" : "" ));
?>

            <?php
            // Заполнение данных из черновика
            $draft_id = intval($_GET['draft_id']);
            $uid = get_uid(false);
            $draft_data = drafts::getDraft($draft_id, $uid, 3);
            if($draft_data) {
               $edit_msg['title'] = str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), $draft_data['title']);
               $edit_msg['msgtext'] = str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), $draft_data['msgtext']);
               $edit_msg['yt_link'] = $draft_data['yt_link'];
               $is_yt_link = ($draft_data['yt_link']?true:false);
               $edit_msg['close_comments'] = $draft_data['is_close_comments'];
               $edit_msg['is_private'] = $draft_data['is_private'];
               $edit_msg['poll_question'] = str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), $draft_data['poll_question']);
               $edit_msg['poll_multiple'] = ($draft_data['poll_type']?'t':'f');
               $draft_answers = $draft_data['poll_answers'];
               if ( empty($draft_answers) ) {
                    $draft_answers = array( '' );
               }
               $edit_msg['poll'] = array();
               if($draft_answers) {
                   foreach($draft_answers as $draft_answer) {
                       array_push($edit_msg['poll'], array('answer'=>str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), $draft_answer)));
                   }
               }
               $answers = $edit_msg['poll'];
               $edit_msg['fromuser_id'] = $uid;
            }
            ?>

                  <form action="/blogs/viewgroup.php<?=$request_uri?>" method="post" enctype="multipart/form-data" name="frm" id="frm" onKeyPress="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {submitLock(this);}" onSubmit="if (checkexts()) {  } else { return false; }">
                    <div>
                          <? if ($action == "edit") {?>
                          <input type="hidden" name="page" value="<?=$page>1?$page:''?>" />
                           <? } ?>
                          <input type="hidden" name="thread" value="<?=($edit_msg["id"])?$edit_msg["id"]:$thread?>" /> 
                          <input type="hidden" name="thread_id" value="<?=($edit_msg["thread_id"])?$edit_msg["thread_id"]:$thread_id?>" />
                          <input type="hidden" name="sub_ord" value="<?=$sub_ord?>" />
                    <? if ($action == "edit") {?><h2>Редактировать:</h2><? } else { ?>
                    <h2>Создать новое сообщение:</h2> <? } ?><a name="edit" id="edit"></a>
                    <table class="blog-form" border="0" cellspacing="0" cellpadding="0">
                      <col style="width:96px"/>
                      <col style="width:610px"/>
            <tr valign="top">
                        <td>Заголовок</td>
                        <td><input type="text" id="name" name="name" maxlength="96" value="<?=(empty($_POST['name'])? (($edit_msg['title']!=='')? $edit_msg['title']: $msg_name): str_replace(array("'", '"', '<', '>'), array('&#039;', '&quot;', '&lt;', '&gt;'), stripslashes($_POST['name'])))?>" onfocus="isFocus = true;" onblur="isFocus = false;" style="width:555px" /></td>
                      </tr>
                      <tr valign="top">
                        <td style="padding-top:5px">Текст</td>
                        <td style="padding-top:5px">
              <textarea id="msg_source" style="display:none" cols="50" rows="20"><? if($_GET['l']!='') { echo htmlspecialchars($_GET['l']); } else { ?><?=(empty($_POST['msg'])? (($edit_msg['msgtext']!=='')? input_ref($edit_msg['msgtext']): $msg): str_replace(array("'", '"', '<', '>'), array('&#039;', '&quot;', '&lt;', '&gt;'), stripslashes($_POST['msg'])))?><? } ?></textarea>
              <textarea id="msg" name="msg" onfocus="isFocus = true;" onblur="isFocus = false;" cols="50" rows="20"></textarea>
                        <? if ($alert[2])  print(view_error($alert[2])) ?>
                          <br />Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;&lt;cut&gt;&lt;h&gt;&lt;s&gt;
                        </td>
                      </tr>


                        <tr valign="top">
                            <td>&nbsp;</td>
                            <td>
                               <br/>
                                <div id="attachedfiles" class="b-fon b-fon_width_full" style="width:560px"></div>

                                <script type="text/javascript">
                                    var attachedfiles_list = new Array();
                                    <?php
                                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
                                    $attachedfiles_session = $_POST['attachedfiles_session'];
                                    if(!$attachedfiles_session) {
                                        $attachedfiles = new attachedfiles('', true);
                                        $asid = $attachedfiles->createSessionID();
                                        $attachedfiles->addNewSession($asid);
                                        $attachedfiles_session = $asid;
                                    } else {
                                        $attachedfiles = new attachedfiles($attachedfiles_session);
                                        $asid = $attachedfiles_session;
                                    }


                                    if($draft_id) {
                                        if(!$attachedfiles_session) {
                                            $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($draft_id, 1);
                                            if($attachedfiles_tmpdraft_files) {
                                                $attachedfiles_prj_files = array();
                                                foreach($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
                                                    $attachedfiles_draft_files[] = $attachedfiles_draft_file;
                                                }
                                                $attachedfiles->setFiles($attachedfiles_draft_files, 1);
                                            }
                                        }
                                    } else {
                                        if($action=='edit' && !$alert) {
                                            $attachedfiles_tmpblog_files = blogs::getAttachedFiles($edit_tr);
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
                                    attachedFiles.init('attachedfiles', 
                                                       '<?=$attachedfiles_session?>',
                                                       attachedfiles_list, 
                                                       '<?=blogs::MAX_FILES?>',
                                                       '<?=blogs::MAX_FILE_SIZE?>',
                                                       '<?=implode(', ', $GLOBALS['disallowed_array'])?>',
                                                       'blog',
                                                       '<?=get_uid(false)?>'
                                                       );
                                </script>

                                <input type="hidden" name="olduser" value="<?=$edit_msg["fromuser_id"]?>" />
                            </td>
                        </tr>

                      <tr valign="top">
                        <td>&nbsp;</td>
                        <td><br /><a href="javascript:void(null);" onClick="toggle_yt_link();" style="border-bottom:1px dashed; height:15px; text-decoration:none;">Добавить ссылку на YouTube/RuTube/Vimeo видео</a></td>
                      </tr>
                      <tr valign="top">
                        <td>&nbsp;</td>
                        <td><div id="yt_link" style="display:<? if ($is_yt_link) { ?>block<? } else { ?>none<? } ?>;padding-top:4px; width:560px;">
                            <input type="text" id="fyt_link" name="yt_link" value="<?=htmlspecialchars(isset($_POST['yt_link'])? stripslashes($_POST['yt_link']): ($edit_msg['yt_link']? $edit_msg['yt_link']: ''), ENT_QUOTES)?>" onfocus="isFocus = true;" onblur="isFocus = false;" style="width:99%" />
                            <? if (isset($alert) && (is_array($alert)) && ($alert[4])) { print(view_error($alert[4])); }?>
                          </div>
                        </td>
                      </tr>
                      
                      
                      <? if ($action != "edit" || !$edit_msg['reply_to'] ) {?>
                      <tr valign="top">
                        <td>&nbsp;</td>
                        <td><br /><a href="javascript:void(null);" onClick="toggle_settings();" style="border-bottom:1px dashed; height:15px; text-decoration:none;">Дополнительные настройки</a></td>
                      </tr>
                      <tr valign="top">
                        <td>&nbsp;</td>
                        <?php
                        $bClose   = !empty($close_comments) ? $close_comments == 't' : $edit_msg['close_comments'] == 't';
                        $bPrivate = !empty($is_private)     ? $is_private == 't'     : $edit_msg['is_private'] == 't';
                        ?>
                        <td><div id="settings" style="<?=( $bClose || $bPrivate ? '' : 'display:none;' )?>padding-top:4px">
                        <div class="b-check b-check_padtop_3">
                            <input onclick="toggle_close()" id="ch_close_comments" class="b-check__input" type="checkbox" name="close_comments" value="1" <?=( $bClose ? 'checked="checked"' : '' )?> />
                            <label class="b-check__label" for="ch_close_comments" id="label_close_comments">Запретить комментирование</label>
                        </div>
                        <div class="b-check b-check_padtop_3">
                            <input id="ch_is_private" class="b-check__input" type="checkbox" onclick="toggle_private()" name="is_private" value="1" <?=( $bPrivate ? 'checked="checked"' : '' )?> />
                            <label class="b-check__label" for="ch_is_private" id="label_is_private">Показывать только мне<?=(($edit_msg['is_private']=="t")?' (скрытые от пользователей темы видны модераторам)':'')?></label>
                        </div>
                          </div>
                        </td>
                      </tr>
                      <tr valign="top">
                        <td>&nbsp;</td>
                        <td><br /><a href="javascript:void(null);" onClick="toggle_pool();" style="border-bottom:1px dashed; height:15px; text-decoration:none;"><?=($edit_msg['poll_question']? 'Редактировать опрос': 'Добавить опрос')?></a></td>
                      </tr>
            <tr valign="top" id="trpollquestion" class="poll-st"<?=((!empty($alert[5]) || $edit_msg['poll_question'])? '': ' style="display: none"')?>>
                        <td>Вопрос</td>
                        <td>
              <? // этот span, используется в качестве хака, для того, чтобы нормально отображались html сущности и ничего не разъезжалось. в конце страницы, есть javascript, который содержимое в textarea ?>
              <textarea cols="50" rows="20" onfocus="isFocus = true;" onblur="isFocus = false;" id="poll-question-source" style="display: none"><?=(isset($_POST['question'])? str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), stripslashes($_POST['question'])): ($edit_msg['poll_question']? $edit_msg['poll_question']: ''))?></textarea>
              <? if ($edit_msg['poll_question'] && !(hasPermissions('blogs') || $edit_msg['fromuser_id'] == $uid)) { ?>
              <input type="hidden" name="question" value="<?=(isset($_POST['question'])? str_replace(array('"', "'", "\\"), array('&quot;', '&#039;', '&#92;'), stripslashes($_POST['question'])): ($edit_msg['poll_question']? $edit_msg['poll_question']: ''))?>">
              <textarea id="poll-question" style=" width:554px !important" name="no_question" disabled onfocus="isFocus = true;" onblur="isFocus = false;" cols="50" rows="20"></textarea>
              <? } else { ?>
              <textarea id="poll-question" style=" width:554px !important" name="question" onfocus="isFocus = true;" onblur="isFocus = false;" cols="50" rows="20"></textarea>
              <? } ?>
              <div id="poll-warn">&nbsp;</div>
              <? if (isset($alert) && (is_array($alert)) && ($alert[5])) { print(view_error($alert[5])); }?>
            </td>
                      </tr>
            <tr id="trpolltype" class="poll-type"<?=((!empty($alert[5]) || $edit_msg['poll_question'])? '': ' style="display: none"')?>>
              <td>Тип опроса:</td>
              <td style="padding-top:5px;">
              	  <div class="b-radio  b-radio_layout_horizontal">
                  	<div class="b-radio__item">
                      <input id="fmultiple0" class="b-radio__input" type="radio" name="multiple" value="0" <?=((($edit_msg['poll_multiple'] != 't') && empty($_POST['multiple']))? "checked='checked'": "")?> />
                      <label class="b-radio__label" for="fmultiple0">Один вариант ответа&nbsp;&nbsp;&nbsp;</label>
                    </div>
                  	<div class="b-radio__item">
                      <input id="fmultiple1" class="b-radio__input" type="radio" name="multiple" value="1" <?=((($edit_msg['poll_multiple'] == 't') || !empty($_POST['multiple']))? "checked='checked'": "")?> />
                      <label class="b-radio__label">Несколько вариантов ответа</label>
                    </div>
                  </div>
              </td>
            </tr><?
            $i = 0;
            $c = count($answers);
            foreach ($answers as $answer) {
            ?><tr valign="top" class="poll-line" id="poll-<?=$i?>"<?=((!empty($alert[5])  || $edit_msg['poll_question'])? '': ' style="display: none"')?>>
                        <td>Ответ #<span class="poll-num"><?=($i+1)?></span></td>
                        <td>
            <? if ($answer['id'] && !(hasPermissions('blogs') || $edit_msg['fromuser_id'] == $uid)) { ?><input class="poll-answer-exists" type="hidden" name="answers_exists[<?=$answer['id']?>]" value="1"><? } ?>
            <table cellpadding="0" cellspacing="0" border="0" style="width:560px;">
            <tr>
              <td><input onfocus="isFocus = true;" onblur="isFocus = false;" maxlength="<?=blogs::MAX_POLL_ANSWER_CHARS?>" class="poll-answer" type="text" value="<?=$answer['answer']?>" <?=($answer['id']? ((hasPermissions('blogs') || $edit_msg['fromuser_id'] == $uid)? "name='answers_exists[{$answer['id']}]'": 'name="it_no"  disabled'): "name='answers[]'")?> tabindex="20<?=$i?>" /></td>
              <td class="poll-btn"><a class="poll-del" href="javascript: return false" onclick="poll.del('Blogs', <?=$i++?>); return false;"><img src="/images/delpoll.png" width="15" height="15"  alt="Удалить ответ" title="Удалить ответ" /></a></td>
              <td class="poll-btn"><span class="poll-add">&nbsp;</span></td>
            </tr>
            </table>
            </td>
                      </tr><?
            }
                      ?><tr valign="top">
                        <td><br />Раздел</td>
                        <td><br />
                              <select id="fcategory" name="category" onfocus="isFocus = true;" onblur="isFocus = false;" tabindex="300">
                              <?
                               
                              if ($groups)
                              foreach ($groups as $id => $group)
                               if ((!$group['read_only'] || ($group['read_only'] && !$mod)) && ($group['id']!=55 || $allow_love)) { 
                                   $sSelected = '';
                                   if ((!$edit_msg['id'] && $gr == $group['id'] && $group['t'] == intval($base)) || ($edit_msg['id'] && $edit_msg['id_gr'] == $group['id'] && $group['t'] == $edit_msg['base'])) {
                                       $gr_value="{$group['id']}|{$group['t']}";
                                       $sSelected = ' selected';
                                   }
                                   ?>
                                      <option value="<?=$group['id']?>|<?=$group['t']?>" <?=$sSelected?>><?=$group['t_name']?></option>
                              <? } ?>
                              </select>
                        </td>
                      </tr>
                      <? if(hasPermissions('blogs')) { ?>
                      <tr valign="top">
                        <td>&nbsp;</td>
                        <td><br /><div class="b-check"><input class="b-check__input" type="checkbox" id="ontopid" name="ontop" value="t" <?=($edit_msg['ontop']=='t')? 'checked="checked"': ''?> />&nbsp;<label class="b-check__label b-check__label_fontsize_11" for="ontopid">Закрепить тему наверху</label></div></td>
                      </tr>
                      <? } ?>
                      
                      <?php } ?>
                      
                      <tr valign="top">
                        <td>&nbsp;</td>
                        <td><br />
                    <input type="hidden" name="action" value="<?= ($action == "edit")? "change":"new_tr"?>" />
<!--
                    <input type="submit" name="btn" style="width:100px; height:20px;" value="<?= ($action == "edit")? "Сохранить":"Создать"?>" tabindex="301">
-->

        <input type="hidden" name="draft_post_id" id="draft_post_id" value="<?=($edit_msg["id"])?$edit_msg["id"]:$thread?>" />
        <input type="hidden" name="draft_id" id="draft_id" value="<?=$draft_id?>" />
        <div class="form-el">
      <span class="todrafts">
        <span class="time-save" id="draft_time_save" style="display:none;"></span> <a href="javascript:DraftSave();" onclick="this.blur();" class="btnr-mb"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">В черновики</span></span></span></a>
      </span>
            <span style="float: left;">
                <a id="btn" class="b-button b-button_rectangle_color_green"  href="javascript: void(0)" onmousedown="return false" onmouseup="if($('btn').get('disabled')==true) { return false; } if(checkexts()) {$('btn_text').set('html','Подождите'); $('btn').set('disabled',true); $('btn' ).addClass('b-button_rectangle_color_disable'); $('btn' ).removeClass('b-button_rectangle_color_green'); submitLock($('frm')); return false; } else return false;">
                <span class="b-button__b1">
                    <span class="b-button__b2">
                        <span class="b-button__txt" id="btn_text"><?= ($action == "edit")? "Сохранить":"Создать"?></span>
                    </span>
                </span>
                </a>
            </span>
    </div>

                        </td>
                      </tr>
                    </table>
                    </div>
                  </form>
                </div>
                <script type="text/javascript">  
                window.addEvent('domready', function(){  
                    DraftInit(3);  
                });  
                </script>
                <? }
                }
                 ?>