<?

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mess_folders.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

$stop_words = new stop_words( false );

$is_pro1 = payed::CheckPro($user->login);
$cf = new mess_folders();
$cf->from_id = get_uid();
$folders = $cf->GetAll();
$msgs = new messages();
$users_folders = $msgs->GetContactFolders(get_uid(), $dlg_user, $err);

$isNeedUseCaptcha = $msgs->isNeedUseCaptcha(get_uid(false));
if($isNeedUseCaptcha) {
    $SESSION['need_captcha_messages'] = 1;
}

if($draft_id) {
    $draft = drafts::getDraft($draft_id, get_uid(), 2);
    if($draft['msg']) { $msg = $draft['msg']; }
}

$userNotBeSpam = array_merge($GLOBALS['usersNotBeIgnored'], $GLOBALS['ourUserLoginsInCatalog']);
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/contacts.common.php");
$xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">
<!--
var inner = false;
function show_fpopup(img,num)
{
    document.getElementById(img).blur();
    document.getElementById(num).toggleClass('b-layout_hide');
}

function hide_fpopup(num)
{
    if (!inner)
    {
        e = document.getElementById(num);
        e.addClass('b-layout_hide');
    }
}

function mouseout(num)
{
    setTimeout("hide_fpopup('"+num+"')", 500);
}
function toggle_attach() {var a=document.getElementById('attach').style;if(a.display!='block') a.display='block';else a.display='none';}

function reload_attach() {
    var obj = document.getElementById('attach');
    var buffer = obj.innerHTML;
    obj.innerHTML = '';
    obj.innerHTML = buffer;
    document.getElementById('del_attach').style.visibility = 'hidden';
}

function make_del_button(obj) {
    if (obj.value) {
        document.getElementById('del_attach').style.visibility = 'visible';
    } else {
        document.getElementById('del_attach').style.visibility = 'hidden';
    }
}


function checkexts() {
    var val = 0;
    var aext = ['<?=implode("','", $GLOBALS['disallowed_array'])?>'];
    var grp = document.getElementById('msg_frm')['attach[]'];
    if(grp) {
        if (typeof grp.length != 'undefined') {
            for (i=0; i<grp.length; i++) {
                if (!grp[i].value) continue;
                var ext = grp[i].value.split(/\.+/).pop().toLowerCase();
                var ok = true;
                for (var c=0,m=aext.length; c<m; c++) {
                    if (ext == aext[c]) {
                        ok = false;
                        break;
                    }
                }
                if (!ok) {
                    var fname = grp[i].value.split(/[\/|\\]/);
                    fname = fname[fname.length - 1];
                    alert("Формат файла "+fname+" недопустим к загрузке.");
                    return false;
                }
            }
        } else if (grp.value) {
            var ext = grp.value.split(/\.+/).pop().toLowerCase();
            var ok = true;
            for (var c=0,m=aext.length; c<m; c++) {
                if (ext == aext[c]) {
                    ok = false;
                    break;
                }
            }
            if (!ok) {
                var fname = grp.value.split(/[\/|\\]/);
                fname = fname[fname.length - 1];
                alert("Формат файла "+fname+" недопустим к загрузке.");
                return false;
            }
        }
    }
    return true;
}

var is_sending = 0;

function sendMessage() {
    if(is_sending==0) {
        $("btn" ).addClass("b-button_disabled");
    }
    if ( checkexts() && !is_sending ) { 
        is_sending = 1;
        $('btn_text').set('html','Подождите');
        $('btn_text').set('disabled',true); 
        // for Opera
        sending_interval = setTimeout( function() { 
            clearTimeout(sending_interval);
            $('msg_frm').submit(); 
        }, 10);
        return false;
    } else {
        return false;
    }
}

// прокрутка к диалогу при загрузке страницы
window.addEvent('domready', function(){
    JSScroll($('user_info_table'));
});

<?php if(empty($_COOKIE['hack_warn_1']) || empty($_COOKIE['hack_warn_2'])){ ?>
    function cookieWarn(warn) {
        var exdate=new Date();
        exdate.setDate(exdate.getDate()+365);
        document.cookie="hack_warn_"+warn+"=1" + ";expires="+exdate.toGMTString();
    }

    function hideWarn(warn) {
        cookieWarn(warn);
        document.getElementById('hack_warn_' + warn).parentNode.removeChild(document.getElementById('hack_warn_'+warn));
        if($('hack_br') != undefined) $('hack_br').destroy();
        var child = $('hack_warn_all').getChildren();
        if (child.length == 0) {
            $('hack_warn_all').destroy();
        }
    }
<? } ?>

<?php // жалобы на спам-------------------------- ?>
var sSpamComplaintSid = '<?=$user->uid?>';
var sSpamComplaintUid = '<?=$_SESSION['uid']?>';
var aSpamComplaintMsg = new Array();
var nSpamComplaintNum = -1;
<?php $nSpamComplaintCnt = 1; ?>

function popupSpamComplaint(num) {
    nSpamComplaintNum = num;
    $('spam_complaint_close').set( 'disabled', false );
    $('spam_complaint_send').set( 'disabled', false );
    $('spam_complaint_txt').set('value','');
    $('spam_complaint_popup').toggleClass( 'b-shadow_hide' );
}

function sendSpamComplaint() {
    if ( nSpamComplaintNum > 0 ) {
        $('spam_complaint_close').set( 'disabled', true );
        $('spam_complaint_send').set( 'disabled', true );
        aSpamComplaintMsg[nSpamComplaintNum].txt = $('spam_complaint_txt').get('value');
        xajax_sendSpamComplaint( sSpamComplaintSid, sSpamComplaintUid, JSON.encode(aSpamComplaintMsg[nSpamComplaintNum]) );
        nSpamComplaintNum = -1;
    }
}
<?php //----------------------------------------- ?>
//-->
</script>


<form action="/contacts/" method="post" name="frm" id="frm">
    <div>
    <input type="hidden" name="action" value="" />
    <input type="hidden" name="selected" id="sel" value="<?=$dlg_user?>" />
    </div>
</form>

<h1 class="b-page__title">Сообщения</h1>

<table class="b-layout__table b-layout__table_width_full">
   <tr class="b-layout__tr">
      <td class="b-layout__td">
          <!-- WARNING -->
          <? include($_SERVER['DOCUMENT_ROOT']. '/contacts/tpl.warning.php'); ?>
          <!-- WARNING -->
          
          <table id="user_info_table" width="100%" cellspacing="0" cellpadding="0" border="0">
              <tr class="qpr">
              <td style=" padding:10px 10px 10px 0">
                  <table cellspacing="0" cellpadding="0" border="0">
                  <tr style="vertical-align:top" class="n_qpr">
                      <td style="text-align:center; width:70px;"><a href="/users/<?=$user->login?>/" class="<?=$cnt_role?>name11"><?=view_avatar($user->login, $user->photo) ?></a></td>
                      <td class="<?=$cnt_role?>name11" align="left" style="text-align:left">
                      <?=$session->view_online_status($user->login)?><a href="/users/<?=$user->login?>" class="<?=$cnt_role?>name11"><?=($user->uname." ".$user->usurname)?></a> [<a href="/users/<?=$user->login?>" class="<?=$cnt_role?>name11"><?=$user->login?></a>]
                      <?= view_mark_user(array(
                          "login"=>$user->login,
                          "is_pro"=>$is_pro1?"t":"f", 
                          "role"=>$user->role, 
                          "is_team"=>$user->is_team, 
                          "is_pro_test"=>$user->is_pro_test, 
                          'is_verify'=>$user->is_verify,
                          'is_profi'=>$user->is_profi
                      ));?>
                      
                      <?=($user->is_banned && !$alert[3] ? "<br /><span style='color: #FF500B'>Пользователь&nbsp;заблокирован</span>" : "")?>
                      <br />
                      <span class="cl9">Всего сообщений: <?=$num_msgs_from?></span><br />
                      <?/* if(!$post_denied) { ?>
                        <a href="/contacts/?from=<?=$user->login?>#form" class="blue">Написать новое сообщение</a><br/>
                      <? } */?>
                      <div class="vfolders"><? if ($folders)
                      foreach ($folders as $ikey => $folder) {
                          ?><div id="vfolder<?=$folder['id']?>" class="<? if ($users_folders[$dlg_user] && in_array($folder['id'], $users_folders[$dlg_user])) { ?>active<? } else { ?>passive<? } ?>"><?=$folder['fname']?></div> <?
                       } ?><div id="vfolder-1" class="<? if ( ($users_folders[$dlg_user] && in_array(-1, $users_folders[$dlg_user]))) { ?>active<? } else { ?>passive<? } ?>">Избранные</div> <div id="vfolder-2" class="<? if ($users_folders[$dlg_user] && in_array(-2, $users_folders[$dlg_user])) { ?>active<? } else { ?>passive<? } ?>">Игнорирую </div>
          
                       <?php if ($users_folders[$dlg_user] && in_array(-6, $users_folders[$dlg_user])) { ?>
                       <div class="active delivery">Платные рассылки</div>
                       <?php } ?>
                      </div>
          
                      <?php if(!$user->is_banned) { ?>
                      <a href="javascript:void(null);" onClick="show_fpopup('ipopup<?=$folder['id']?>','fpopup<?=$folder['id']?>')" onMouseOut="inner=false;mouseout('fpopup<?=$folder['id']?>')"><img id="ipopup<?=$folder['id']?>" class="i_2folder" src="/images/2folder.gif" alt="" /></a>
                      <div class="folders">
                      <div onMouseOver="inner=true;" onMouseOut="inner=false;mouseout('fpopup<?=$folder['id']?>')" class="fpopup b-layout_hide" id="fpopup<?=$folder['id']?>" name="fpopup<?=$folder['id']?>">
                      <? if ($folders) {
                       foreach ($folders as $ikey => $folder) { ?>
                       <div onMouseOver="inner=true;" onMouseOut="inner=false;" id="folder<?=$folder['id']?>" <? if ($users_folders[$dlg_user] && in_array($folder['id'], $users_folders[$dlg_user])) { ?>class="active"<? } else { ?>class="passive"<? } ?> onClick="xajax_ChFolderInner(<?=$folder['id']?>, '<?=$dlg_user_login?>');"><?=reformat($folder['fname'],25,0,1)?></div><br /> 
                       <? } ?>
                       <br />
                       <? } ?>
                      <div onMouseOver="inner=true;" onMouseOut="inner=false;" id="folder-1" <? if ($users_folders[$dlg_user] && in_array(-1, $users_folders[$dlg_user])) { ?>class="active"<? } else { ?>class="passive"<? } ?> onClick="xajax_ChFolderInner(-1, '<?=$dlg_user_login?>');">Избранные</div><br />
                      <? if(!in_array($user->login, $usersNotBeIgnored)) { ?><div onMouseOver="inner=true;" onMouseOut="inner=false;" id="folder-2" <? if ($users_folders[$dlg_user] && in_array(-2, $users_folders[$dlg_user])) { ?>class="active"<? } else { ?>class="passive"<? } ?> onClick="xajax_ChFolderInner(-2, '<?=$dlg_user_login?>');">Игнорирую</div><br /><?}?> <div onMouseOver="inner=true;" onMouseOut="inner=false;" class="blue" onClick="if (warning(3)) {document.getElementById('sel').value=<?=$dlg_user?>;frm.action.value='delete'; frm.submit();} else return(false);">Удалить</div></div>
                      </div>
                      <? } ?>
          
                      </td>
                  </tr>
                  </table>
              </td>
              <? $name=$user->login; $t_role=$user->role; include ("../user/note.php") ?>
              </tr>
              <tr><td  colspan="2" style="background-image: url(/images/shadow_t.gif); padding:0; height:6px;"></td></tr>
          </table>
          
          
          <a name="form" id="form"></a>
          <form id="msg_frm" action="/contacts/?from=<?=$user->login?>" method="post" enctype="multipart/form-data" onKeyPress="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {sendMessage();}" onSubmit="if(checkexts()) { } else return false;">
          <div class=" <?php if($is_pro):?>b-layout__left_margright_270 b-layout_marg_null_ipad<?php endif; ?>">
              <input type="hidden" name="draft_id" id="draft_id" value="<?=$draft_id?>" />
              <input type="hidden" name="to_login" id="to_login" value="<?=$user->login?>" />
                <input type="hidden" name="action" value="post_msg" />	
              <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>" />
          
              <div class="form fs-p drafts-v" style="margin: 15px 0px 0px; display: none;" id="draft_div_info">
                  <b class="b1"></b>
                  <b class="b2"></b>
                  <div class="form-in" id="draft_div_info_text"></div>
                  <b class="b2"></b>
                  <b class="b1"></b>
              </div>
          
              <? if(!$post_denied) { ?>
              
                <div class="dialog-err"><? if ($alert[3]) print(view_error($alert[3])) ?></div>
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                 <tr>
                  <td style="padding-top:10px; text-align:left">
                   Новое сообщение:
                  </td>
                 </tr>
                 <tr>
                  <td style=" text-align:left; padding-bottom:10px;">
                   <? if ($error_flag) { ?>
                   <script type="text/javascript">
                   <!--
                   window.location = "#form";
                   //-->
                   </script>
                   <? if ($prjname) { ?>
                   <input type="hidden" name="prjname" value="<?=$prjname?>" /> <? } } ?>
                  <div class="b-textarea">
                   <textarea <?php echo ( $alert[3] ) ? 'disabled="disabled"' : '';?> cols="10" rows="14" name="msg" class="b-textarea__textarea b-textarea__textarea_fontsize_11" id="msg"><? if ($prjname && !$msg) print("&gt;  Проект/Предложение &laquo;$prjname&raquo;\n"); else if ($msg) print stripslashes($msg); ?></textarea>
                  </div>
                          Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;&lt;h&gt;
                   <? if ($alert[2]) print(view_error($alert[2])) ?>
                  </td>
                 </tr>
              
              <?php if($isNeedUseCaptcha) { ?>
                  <?php
                  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
                  $captchanum = uniqid('',true);
                  $captcha = new captcha($captchanum);
                  $captcha->setNumber();
                  ?>
                  <tr>
                      <td style="text-align:left">
                          <br/>
                          <div class="b-fon b-fon_bg_ffeda9 b-fon_padbot_15">
                           <table class="b-fon__table"><tbody><tr><td class="b-fon__cell">
                            <b class="b-fon__b1"></b>
                            <b class="b-fon__b2"></b>
                            <div class="b-fon__body">
                             <div class="b-captcha b-captcha_pad_5_10">
                              <span class="b-captcha__descr">Вы отправили более 3 сообщений за 1 минуту.<br>Чтобы отправить это сообщение, введите код с картинки:</span>
                                          <img id="capcha" class="b-captcha__img b-captcha__img_bord_b2b2b2" src="/image.php?num=<?=$captchanum?>" alt="" onClick="$('capcha').set('src','/image.php?num='+$('captchanum').get('value')+'&r='+Math.random());" width="130" height="60" />
                              <div class="b-input b-input_width_120 b-input_height_60 b-input_inline-block">
                                              <input id="captchanum" name="captchanum" type="hidden" value="<?=$captchanum?>" />
                                              <input id="rndnum" class="b-input__text"  name="rndnum" type="text" value="" maxlength="5" />
                              </div>
                                          <a class="b-captcha__link b-captcha__link_color_333 b-captcha__link_margtop_20" href="#" onClick="$('capcha').set('src','/image.php?num='+$('captchanum').get('value')+'&r='+Math.random()); return false;" >Обновить картинку</a>
                             </div>
                            </div>
                            <b class="b-fon__b2"></b>
                            <b class="b-fon__b1"></b>
                           </td></tr></tbody></table>
                              <? if ($alert[4]) print(view_error($alert[4])) ?>
                          </div>
                      </td>
                  </tr>
              <?php } ?>
              
                 <tr style="vertical-align:top">
                 <td>
                 
                 <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="#" onClick="this.getParent().hide();$('attachedfiles').removeClass('b-layout_hide');return false;"><span class="b-icon b-icon__ref"></span></a><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="#" onClick="this.getParent().hide();$('attachedfiles').removeClass('b-layout_hide');return false;">Добавить файлы в сообщение</a></div>
              
                          <!-- Attaches -->
                          <div id="attachedfiles" class="b-fon b-fon_padbot_10 b-layout_hide"></div>
                          <script type="text/javascript">
                              var attachedfiles_list = new Array();
                              <?php
                              require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
                              $attachedfiles = new attachedfiles($attachedfiles_session);
                              if(!$attachedfiles_session && $draft_id) {
                                  $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($draft_id, 3);
                                  if($attachedfiles_tmpdraft_files) {
                                      $attachedfiles_prj_files = array();
                                      foreach($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
                                          $attachedfiles_draft_files[] = $attachedfiles_draft_file;
                                      }
                                      $attachedfiles->setFiles($attachedfiles_draft_files);
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
                                                 '<?=$attachedfiles->getSession()?>',
                                                 attachedfiles_list, 
                                                 '<?=messages::MAX_FILES?>',
                                                 '<?=messages::MAX_FILE_SIZE?>',
                                                 '<?=implode(', ', $GLOBALS['disallowed_array'])?>',
                                                 'contacts',
                                                 '<?=get_uid(false)?>'
                                                 );
                          </script>
                          <!-- /Attaches -->
              
                  <input type="hidden" name="msg_to" value="<?=$chat_with?>" />
              <!--
                  <input <?php echo ( $alert[3] ) ? 'onclick="return false" disabled="disabled"' : '';?> style="margin-top: 42px !important;" type="submit" name="btn" class="btn" value="Отправить">
              -->
              
                  <div>
                   <span class="todrafts">
                    <span class="time-save" id="draft_time_save" style="display:none;"></span> <a href="javascript:DraftSave();" onclick="this.blur();" class="btnr-mb"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">В черновики</span></span></span></a>
                </span>
                   <!-- DRAFTS NEW BLOCK -->
                   <button type="button" id="btn" class="b-button b-button_flat b-button_flat_green"  href="javascript: void(0)" onmousedown="return false" onmouseup="return sendMessage()"><span id="btn_text">Отправить сообщение</span></button>
                   <!-- DRAFTS NEW BLOCK -->
               </div>
              
                 </td>
                 </tr>
              
                 <tr><td colspan="3">&nbsp;</td></tr>
                </table>
              <? } ?>
                
              <?php if (isset($is_allow_messages) && !$is_allow_messages): ?> 
              <div class="b-layout__txt b-layout__txt_padtop_10">
                  <?= messages::MESSAGES_NOT_ALLOWED ?>
              </div>                
              <?php endif; ?>
                
          </div>
          </form>
          <? if ($error) print("<div class=\"dialog-err\">".view_error($error)."</div>") ?>
          <? if ($dialog) { ?>
              <table width="100%" cellspacing="0" cellpadding="0" border="0">
                  <tr >
                      <td colspan="2"><img src="/images/shadow_b.gif" alt=""  style="border:0; width:100%; height:6px;" /></td>
                  </tr>
                  <tr><td colspan="2" style="padding-top:10px">&nbsp;</td></tr>
                  <?
                  foreach($dialog as $ikey=>$frase){
                    $is_my    = ($frase['from_id'] == $_SESSION['uid']);
                    $login    = $is_my ? $_SESSION['login'] : $user->login;
                    $uname    = $is_my ? $_SESSION['name'] : $user->uname;
                    $usurname = $is_my ? $_SESSION['surname'] : $user->usurname;
                    $is_chuck = $is_my ? $_SESSION['is_chuck'] : $user->is_chuck;
                ?>
                  <tr>
                  <td style="width:20px; text-align:center; vertical-align:top;"><img class="b-page__desktop" src="/images/triangle_<?=($is_my ? 'grey' : 'red')?>.gif" alt="" width="4" height="11" style="border:0;" /></td>
                      <td<?=($is_my ? '' : ' class="d_him"')?> style="text-align:left;">
                         
                      
                      <?php // жалобы на спам
                      if ( !$frase['deleted'] && !$is_my && !$user->is_banned && $frase['to_id'] && !in_array($user->login, $userNotBeSpam) && $frase['is_spam'] != 't' && $user->is_team != 't' ) { 
                          // не удалено модератором, не свои, не от забаненного, не платные, не от админа и мы на это еще не жаловались
                      ?>
                      <div id="mess_spam_<?=$nSpamComplaintCnt?>" class="mess-spam"><a onclick="popupSpamComplaint(<?=$nSpamComplaintCnt?>);" href="javascript:void(0);">Это спам!</a></div>
                      <script type="text/javascript">
                      aSpamComplaintMsg[<?=$nSpamComplaintCnt?>]      = new Object();
                      aSpamComplaintMsg[<?=$nSpamComplaintCnt?>].id   = '<?=$frase['id']?>';
                      aSpamComplaintMsg[<?=$nSpamComplaintCnt?>].num  = '<?=$nSpamComplaintCnt?>';
                      aSpamComplaintMsg[<?=$nSpamComplaintCnt?>].msg  = '<?=clearTextForJS($frase['msg_text'])?>';
                      aSpamComplaintMsg[<?=$nSpamComplaintCnt?>].date = '<?=$frase['post_time']?>';
                      <?php $nSpamComplaintCnt++ ?>
                      </script>
                      <?php } ?>
                      
                      <div class="utxt <?=($is_my?'':' utxt_contacts')?>" style=" <?=($is_my ? '' : 'color: #A34747 !important')?>">
                         <img class="b-page__ipad" src="/images/triangle_<?=($is_my ? 'grey' : 'red')?>.gif" alt="" width="4" height="11" style="border:0;" />
                      <strong><?=$uname?> <?=$usurname?> [<?=$login?>]</strong> <?=date("d.m.y в H:i",strtotimeEx($frase['post_time']))?>:
                      <? if ( $frase['modified'] && !$frase['deleted'] ) {?>&nbsp; &nbsp;<?
                          if ( !$frase['modified_id'] || $frase['modified_id'] == $frase['from_id'] ) { ?>[внесены изменения: <?=date("d.m.Y | H:i]",strtotimeEx($frase['modified'])); }
                          else { ?>Отредактировано модератором <?=date("[d.m.Y | H:i]",strtotimeEx($frase['modified']));?><?}
                          }?><br />
                      <?php
                      
                      if ( $login == 'admin' ) {
                          $msg_text = reformat($frase['msg_text'], 60, 0, -1, 1);
                      } else {
                          if ( $frase['moderator_status'] === '0' && $frase['to_id'] && !in_array($user->login, $userNotBeSpam) ) {
                              $msg_text = $stop_words->replace( $frase['msg_text'] );
                          }
                          elseif ( $frase['deleted'] ) {
                              $msg_text = '[Сообщение удалено модератором]';
                              if ($frase['from_id'] == get_uid(false) && strlen($frase['reason'])) {
                                  $msg_text .= '<br/>Причина: '. $frase['reason'];
                              }
                          }
                          else {
                              $msg_text = $frase['msg_text'];
                          }
                          
                          $msg_text = reformat( $msg_text, 50, 0, -($is_chuck=='t'), 1 );
                      }
                      
                      $mask    = array('%USER_NAME%', '%USER_SURNAME%', '%USER_LOGIN%', '%URL_PORTFOLIO%', '%URL_LK%', '%URL_BILL%');
                      if (!$is_my) {
                          $user_link = "<a href='{$GLOBALS['host']}/users/{$_SESSION['login']}/portfolio/' target='_blank'>{$_SESSION['name']} {$_SESSION['surname']} [{$_SESSION['login']}]</a>";
                          $url_lk = "<a href='{$GLOBALS['host']}/users/{$_SESSION['login']}/' target='_blank'>{$_SESSION['name']} {$_SESSION['surname']} [{$_SESSION['login']}]</a>";
                          $url_bill = "<a href='{$GLOBALS['host']}/users/{$_SESSION['login']}/bill/' target='_blank'>{$_SESSION['name']} {$_SESSION['surname']} [{$_SESSION['login']}]</a>";
                          
                          $replace = array($_SESSION['name'], $_SESSION['surname'], $_SESSION['login'], $user_link, $url_lk, $url_bill);
                      } else {
                          $user_link = "<a href='{$GLOBALS['host']}/users/{$_SESSION['login']}/portfolio/' target='_blank'>{$user->uname} {$user->usurname} [{$user->login}]</a>";
                          $url_lk = "<a href='{$GLOBALS['host']}/users/{$_SESSION['login']}/' target='_blank'>{$_SESSION['name']} {$_SESSION['surname']} [{$_SESSION['login']}]</a>";
                          $url_bill = "<a href='{$GLOBALS['host']}/users/{$_SESSION['login']}/bill/' target='_blank'>{$_SESSION['name']} {$_SESSION['surname']} [{$_SESSION['login']}]</a>";
                          
                          $replace = array($user->uname, $user->usurname, $user->login, $user_link, $url_lk, $url_bill);
                      }
                      $msg_text = str_replace($mask, $replace, $msg_text);
                      
                echo $msg_text;
                ?>
                <? if ( !$frase['deleted'] && $frase['files'] ) {
                    $nn = 1;
                    ?>
                    <br/>
                    <br/>
                    <div class="filesize1">
                              <div class="attachments attachments-p">
                          <?php
                          foreach ($frase['files'] as $attach) {
                              /*
                              $str =   viewattachLeft( $login, $attach['fname'], 'contacts', $file, 0, 0, 0, 0, 0, 0, $nn );
                              echo '<div class = "flw_offer_attach">', $str, '</div>';
                              $nn++;
                              */
                              
                              $att_ext = CFile::getext($attach['fname']);
              
                              //$str = viewattachLeft( $login, $attach['fname'], "contacts", $tmp, 1000, 600, 307200, true);
                              $aData = getAttachDisplayData( $login, $attach['fname'], 'contacts', 1000, 600, 307200, 0 );
                              
                              if ( $aData && $aData['success'] ) {
                                  if ( $aData['file_mode'] || $aData['virus_flag'] || $att_ext == "swf" ) {
                                      $str = viewattachLeft( $login, $attach['fname'], 'contacts', $file, 0, 0, 0, 0, 0, 0, $nn );
                                      echo '<div class = "flw_offer_attach">', $str, '</div>';
                                  }
                                  else {
                                      echo "<div class = \"flw_offer_attach\"><div style=\"float: left; margin-right:7px;\">$nn.</div><img src=\"".WDCPREFIX.'/users/'.$login.'/contacts/'.$aData['file_name']."\" alt=\"{$aData['file_name']}\" title=\"{$aData['file_name']}\" width=\"{$aData['img_width']}\" height=\"{$aData['img_height']}\" /></div>";
                                  }
                              } 
                              
                              $nn++;
                          }
                          ?>
                              </div>
                          </div>
                          <?php
                       } ?>
                      <br /><br />
                      </div>
                      </td>
                  </tr>
                  <?
                  }
                  ?>
              <? // Страницы
              
                  $pages = ceil($num_msgs_from / $page_size);
                  $sHref = "%s?from={$user->login}&curpage=%d%s";
                  if ($pages > 1){ ?>
                  <tr>
                      <td>&nbsp;</td>
                      <td class="n_qpr" style="padding-right:20px; height:40px;">
                          <?
                          echo new_paginator($curpage, $pages, 4, $sHref);
                          //echo get_pager2($pages,$curpage,'?from='.$user->login.'&curpage=');
                          ?>
                      </td>
                  </tr>
                  <? } // Страницы закончились?>
              </table>
              
              <?php
              // жалоба на спам
              include_once( $_SERVER['DOCUMENT_ROOT'] . '/contacts/spam_complaint_popup.php' );
              ?>
              
          <? } ?>
      </td>
						<?php if(!$is_pro):?>
        <td class="b-layout__td b-layout__td_width_240 b-layout__td_padleft_20 b-layout__td_width_null_iphone">
            <?= printBanner240($is_pro); ?>
        </td>
						<?php endif; ?>
   </tr>
</table>



<script type="text/javascript">
DraftInit(2);
</script>
