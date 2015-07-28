<?
require_once ($_SERVER['DOCUMENT_ROOT'].'/classes/blogs.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/links.php");
$GLOBALS[LINK_INSTANCE_NAME] = new links('blogs');

$bOrd = (isset($_GET['ord']) && preg_match("/^[-_A-Za-z0-9]+$/", $_GET['ord']))? $_GET['ord']: '';
$bPageFrom = (isset($_GET['pageform']) && preg_match("/^[0-9]+$/", $_GET['pageform']))? $_GET['pageform']: '';

$foto_alt = $blog->title;

$answers = array();
$has = is_array($_POST['answers_exists'])? $_POST['answers_exists']: array();
if ($blog->poll) {
	for ($i=0; $i<count($blog->poll); $i++) {
		$ok = !isset($_POST['question']);
		for ($j=0; $j<count($has); $j++) {
			//if ($blog->poll[$i]['id'] == $has[$j]) {
			if (!empty($has[ $blog->poll[$i]['id'] ])) {
				$ok = true;
				break;
			}
		}
		if ($ok) $answers[] = array('id'=>$blog->poll[$i]['id'], 'answer'=>(($has[$blog->poll[$i]['id']])? str_replace(array('"', "'", "\\"), array('&quot;', '&#039;', '&#92;'), stripslashes($has[$blog->poll[$i]['id']])): $blog->poll[$i]['answer']));
	}
}
if ($_POST['answers']) {
	foreach ($_POST['answers'] as $answer) $answers[] = array('id'=>0, 'answer'=>str_replace(array('"', "'", "\\"), array('&quot;', '&#039;', '&#92;'), stripslashes($answer)));
}
if (!$answers) {
	$answers[] = array('id'=>0, 'answer'=>'');
}

?>
<script type="text/javascript">
<!--
var yt_link = false;
var submitFlag = 1;
var blogs_max_desc_chars = <?=blogs::MAX_DESC_CHARS?>;
//-->
</script>
<? if (!empty($multiattach_mode)) { ?>
<style type="text/css">
.addButton INPUT { width: 28px; }
</style>
<? } ?>
<?

if ($blog->close_comments && !hasPermissions('blogs')) $closed_comments = 1;
else $closed_comments = 0;

if (!$form_uri) $form_uri="/blogs/view.php";

if ($gr_base == 0 || $gr_base == 1) {
    $favs = $blog->GetFavorites($uid);
	$grey_line = 1;
}


// такая же есть в viewgr_cnt.php и xajax/banned.server.php
function BlockedThreadHTML($reason, $date, $moder_login='', $moder_name='') {
    return "
        <div class='br-moderation-options'>
            <a href='/help/?all' class='lnk-feedback' style='color: #fff;'>Служба поддержки</a>
            <div class='br-mo-status'><strong>Топик заблокирован.</strong> Причина: ".str_replace("\n", "<br />", $reason)."</div>
            <p class='br-mo-info'>".
            ($moder_login? "Заблокировал: <a href='/users/$moder_login' style='color: #FF6B3D'>$moder_name [$moder_login]</a><br />": '').
            "Дата блокировки: ".dateFormat('d.m.Y H:i', $date)."</p>
        </div>
    ";
}

function JS_Obj($login, $files) {
    $CFile = new CFile;
    $result = '';
    if(is_array($files)) {
        for ($i=0; $i<count($files); $i++) {
            $CFile->GetInfo('users/'.substr($login, 0, 2).'/'.$login.'/upload/'.$files[$i]['fname']);
            $result .= ',{name: "'.addslashes($CFile->fname).'", path: "'.addslashes($CFile->path).'", user: "'.$login.'", size: '.$CFile->size.', mb_size: "'.ConvertBtoMB($CFile->size).'", ';
            $result .= 'ftype: "'.addslashes($CFile->getext()).'", id: '.intval($files[$i]['id']).' }';
        }
    } elseif($files) {
        $CFile->GetInfo('users/'.substr($login, 0, 2).'/'.$login.'/upload/'.$files);
        $result .= ',{name: "'.addslashes($CFile->fname).'", path: "'.addslashes($CFile->path).'", user: "'.$login.'", size: '.$CFile->size.', mb_size: "'.ConvertBtoMB($CFile->size).'", ';
        $result .= 'ftype: "'.addslashes($CFile->getext()).'", id: '.intval($CFile->id).' }'; 
    }
    return $result? ('['.substr($result, 1).']'): '[]';
}

///////////////////////////////////////////////////////////////////////
////////////////////////stat_collector/////////////////////////////////
///////////////////////////////////////////////////////////////////////
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
///////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////


require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
$xajax->printJavascript('/xajax/');


$user = new users();

if($use_draft) {
    // Заполнение данных из черновика
    $draft_id = intval($_GET['draft_id']);
    $uid = get_uid(false);
    $draft_data = drafts::getDraft($draft_id, $uid, 3);
    if($draft_data) {
       $edit_msg['title'] = str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), $draft_data['title']);
       $blog->title = $edit_msg['title'];
       $edit_msg['msgtext'] = str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), $draft_data['msgtext']);
       $blog->msgtext = $edit_msg['msgtext'];
       $edit_msg['yt_link'] = $draft_data['yt_link'];
       $blog->yt_link = $draft_data['yt_link'];
       $is_yt_link = ($draft_data['yt_link']?true:false);
       $edit_msg['close_comments'] = $draft_data['is_close_comments'];
       $edit_msg['is_private'] = $draft_data['is_private'];
       $edit_msg['poll_question'] = str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), $draft_data['poll_question']);
       $blog->poll_question = $edit_msg['poll_question'];
       $edit_msg['poll_multiple'] = ($draft_data['poll_type']?'t':'f');
       $blog->poll_multiple = ($draft_data['poll_type']?'t':'f');
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
    }
}

?>

<script type="text/javascript">
<!--
var oldid = 0;
errmsg1 = errmsg2 = '';
navigated = 0;
<? if ($uid) {
	$tmp=(($gr_base == 5)? "Публиковать работу" : "Комментировать");
	$act_text = ($action == "edit")? "Редактировать" : $tmp;
  if ($action == "edit") print("goToAncor('efrm');");
	if ($error_flag) {
		if ($alert[1]) print("errmsg1=\"".ref_scr(view_error($alert[1]))."\";");
		if ($alert[2]) print("errmsg2=\"".ref_scr(view_error($alert[2]))."\";");
	}
	?>
	var act_text = "<?=$act_text?>";
	var attach_text='';

	<?
	if ($ban_where) {
        $ban=$user->GetBan($uid,$ban_where);
		?>
		function GetForm(){
			out = "<div style=\"padding:10px 10px 10px 20px;\"><h1>Команда Free-lance.ru заблокировала вам возможность оставлять записи в сервисе «Блоги» <?=($ban["to"] ? "до ".date("d.m.Y  H:i",strtotimeEx($ban["to"])) : '')?>по причине: <?=addslashes(reformat( $ban["comment"], 24, 0, 0, 1, 24 ))?></h1><br /><br />Если у вас возникли вопросы, напишите нам на <a href='mailto:info@free-lance.ru'>info@free-lance.ru</a><br /><br /></div>";
			return(out); }

            <? } else if (
            ($blog->is_blocked || ($blog->close_comments && $action != "edit")) && !(hasPermissions('blogs') || $uid == $blog->fromuser_id) || $blog->deleted) { ?>

            function GetForm(mode){out=''; return out;}
            
			<?} else { ?>
      function GetForm(mode, multiattach, is_attach){
          if(is_attach==undefined) is_attach = 1;
        out = "<div style=\"background: url(/images/shadow_t.gif) repeat-x; padding: 0 15px;\">";
        out += "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" id=\"editForm\"><tr><td style=\"padding:10px;border:0;\"><a name=\"efrm\" id=\"efrm\"></a><form action=\"<?=$form_uri?>\" method=\"post\" enctype=\"multipart/form-data\" name=\"frm\" id=\"frm\" onkeypress=\"if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {submitLock(this);}\" onSubmit=\"if (checkexts()) { this.btn.value='Подождите'; this.btn.disabled=true; } else { return false; }\"><br />";
        <? $count_drafts = drafts::CheckBlogs($uid); if($count_drafts) { ?>out += '<div class="form fs-p drafts-v" id="draft_div_info"><b class="b1"></b><b class="b2"></b><div class="form-in" id="draft_div_info_text"><?='Не забывайте, у вас в черновиках <a href="/drafts/?p=blogs">'.ending($count_drafts, 'сохранен', 'сохранено', 'сохранено').' '.$count_drafts.' '.getSymbolicName($count_drafts, 'blogs').'</a>'?></div><b class="b2"></b><b class="b1"></b></div>'; <? } ?>
        out += "<h1 class=\"bl\">"+act_text+":<\/h1>";
        out += "<?= view_hint_access_action(false, 'b-fon_padleft_120');?>";
        out += "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"n_qpr\">\
				<col width=\"120\"><col>\
				<tr valign=\"top\">\
          <td style=\"padding-top:5px\">Заголовок:</td>\
          <td style=\"padding-top:5px\"><div class=\"b-input\"><input type=\"text\" id=\"name\" name=\"msg_name\" class=\"b-input__text\" ><\/div>\
<\/td>\
        </tr>\
				<tr valign=\"top\">\
          <td style=\"padding-top:5px\">Комментарий:</td>\
          <td style=\"padding-top:5px\"><div class=\"b-textarea\"><textarea style=\"height:180px\" id=\"msg\" name=\"msg\" class=\"b-textarea__textarea b-textarea__textarea_fontsize_11\" cols=\"50\" row=\"20\"><\/textarea><\/div>"+errmsg2+
          "Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;&lt;cut&gt;&lt;h&gt;&lt;s&gt;\
          <?= ($alert[1] ? addslashes(view_error($alert[1])) : '');?></td>\
        </tr>\
        ";
          

        out+="<tr valign=\"top\">\
          <td>&nbsp;</td>\
          <td><br/><div id=\"attachedfiles\" class=\"b-fon b-fon_width_full\"></div><input type=\"hidden\" name=\"olduser\" value=\"<?=$blog->fromuser_id?>\" /><br/><br/></td>\
        </tr>\
        ";

<?
        //убираем в СБР youtube-линк
	if ($hide_youtube != 1)
	{
?>
        out += "<tr valign=\"top\">\
          <td>&nbsp;</td>\
          <td><a href=\"javascript:void(null);\" class=\"blue\" onClick=\"toggle_yt_link();\" style=\"border-bottom:1px dashed; height:15px; text-decoration:none;\">Добавить ссылку на YouTube/RuTube/Vimeo видео</a></td>\
        </tr>\
        <tr valign=\"top\">\
          <td>&nbsp;</td>\
          <td><div id=\"yt_link\" style=\"display:"+((mode==1)? 'none': 'none')+";padding-top:4px\"><input type=\"text\" class=\"wdh98\" id=\"fyt_link\" name=\"yt_link\" value=\"\" onfocus=\"isFocus = true;\" onblur=\"isFocus = false;\"></div>\
                    <? if (isset($alert) && (is_array($alert)) && ($alert[4])) { print(addslashes(view_error($alert[4]))); }?>
          </td>\
        </tr>"
<?
        }
        if ($action == "edit")
        {
?>
	if (mode==1)
	{
        out += "<tr valign=\"top\">\
          <td>&nbsp;</td>\
          <td><br /><a href=\"javascript:void(null);\" onClick=\"toggle_settings();\" class=\"blue\" style=\"border-bottom:1px dashed; height:15px; text-decoration:none;\">Дополнительные настройки</a></td>\
        </tr>\
        <tr valign=\"top\">\
          <td>&nbsp;</td>\
          <?php
          $bClose   = !empty($close_comments) ? $close_comments == 't' : $edit_msg['close_comments'] == 't';
          $bPrivate = !empty($is_private)     ? $is_private == 't'     : $edit_msg['is_private'] == 't';
          ?>
          <td><div id=\"settings\" style=\"<?=( $bClose || $bPrivate ? '' : 'display:none;' )?>padding-top:4px\">\
							<div class=\"b-check b-check_padtop_3\">\
              <input onclick=\"toggle_close()\" id=\"ch_close_comments\" class=\"b-check__input\" type=\"checkbox\" name=\"close_comments\" value=\"1\" <?=( $bClose ? 'checked=\"checked\"' : '' )?>>\
              <LABEL class=\"b-check__label\" for=\"ch_close_comments\" id=\"label_close_comments\">Запретить комментирование</LABEL>\
              </div>\
							<div class=\"b-check b-check_padtop_3\">\
              <input id=\"ch_is_private\" class=\"b-check__input\" type=\"checkbox\" onclick=\"toggle_private()\"  name=\"is_private\" value=\"1\" <?=( $bPrivate ? 'checked=\"checked\"' : '' )?>>\
              <LABEL class=\"b-check__label\" for=\"ch_is_private\"  id=\"label_is_private\">Показывать только мне<?=(($edit_msg['is_private']=='t')?" (скрытые от пользователей темы видны модераторам)":"")?></LABEL>\
              </div>\
            </div>\
          </td>\
        </tr>";

		<? if (empty($no_poll)) { ?>
		out += "\
		<tr valign=\"top\">\
			<td>&nbsp;</td>\
			<td><br /><a href=\"javascript:void(null);\" onClick=\"toggle_pool();\" class=\"blue\" style=\"border-bottom:1px dashed; height:15px; text-decoration:none;\"><?=($blog->poll_question? 'Редактировать опрос': 'Добавить опрос')?></a></td>\
		</tr>\
		<tr id=\"trpollquestion\" valign=\"top\" class=\"poll-st\"<?=((!empty($alert[5]) || $blog->poll_question)? '': ' style=\\"display: none\\"')?>>\
			<td>Вопрос</td>\
			<td>\
				<textarea cols=\"50\" row=\"20\" id=\"poll-question-source\" style=\"display: none\"><?=str_replace(array("\r\n", "\n"), array("\\r\\n", "\\n"), isset($_POST['question'])? str_replace(array('"', "'", "\\", "<", ">"), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), stripslashes($_POST['question'])): ($blog->poll_question? str_replace("\\", '&#92;', $blog->poll_question): ''))?></textarea>\
				<? if ($blog->poll_question && !(hasPermissions('blogs') || $blog->fromuser_id == $uid)) { ?>\
				<input type=\"hidden\" name=\"question\" value=\"<?=str_replace("\\", "&#92;", (preg_replace("/\\r?\\n/", "\\\\n", (isset($_POST['question'])? str_replace(array('"', "'"), array('&quot;', '&#039;'), stripslashes($_POST['question'])): $blog->poll_question))))?>\" />\
				<textarea cols=\"50\" row=\"20\" tabindex=\"199\" style=\"height: 50px\" id=\"poll-question\" name=\"no_question\" disabled><?//=str_replace("\\", "&#92;", (preg_replace("/\\r?\\n/", "\\\\n", (isset($_POST['poll_question'])? str_replace(array('"', "'"), array('&quot;', '&#039;'), stripslashes($_POST['poll_question'])): $blog->poll_question))))?></textarea>\
				<? } else { ?>\
				<textarea cols=\"50\" row=\"20\" tabindex=\"199\" style=\"height: 50px\" id=\"poll-question\" name=\"question\"><?//=str_replace("\\", "&#92;", preg_replace("/\\r?\\n/", "\\\\n", (isset($_POST['poll_question'])? str_replace(array('"', "'"), array('&quot;', '&#039;'), stripslashes($_POST['poll_question'])): $blog->poll_question)))?></textarea>\
				<? } ?>\
				<div id=\"poll-warn\">&nbsp;</div>\
				<? if (isset($alert) && (is_array($alert)) && ($alert[5])) { print(addslashes(view_error($alert[5]))); }?>\
			</td>\
		</tr>\
	   <tr id=\"trpolltype\" class=\"poll-type\"<?=((!empty($alert[5]) || $blog->poll_question)? '': ' style=\\"display: none\\"')?>>\
	   <td>Тип опроса:</td>\
	   <td>\
		<table>\
			<tr>\
				<td><input id=\"fmultiple0\" type=\"radio\" name=\"multiple\" value=\"0\" <?=((($blog->poll_multiple != 't') && empty($_POST['multiple']))? "checked": "")?> />&nbsp;</td>\
				<td>&nbsp;Один вариант ответа&nbsp;&nbsp;&nbsp;</td>\
				<td><input id=\"fmultiple1\" type=\"radio\" name=\"multiple\" value=\"1\" <?=((($blog->poll_multiple == 't') || !empty($_POST['multiple']))? "checked": "")?> />&nbsp;</td>\
				<td>&nbsp;Несколько вариантов ответа</td>\
			</tr>\
		</table>\
	   </td>\
	   </tr>\
		<?
		  $i = 0;
		  $c = count($answers);
		  foreach ($answers as $answer) {
		?><tr valign=\"top\" class=\"poll-line\"<?=((!empty($alert[5]) || $blog->poll_question)? '': ' style=\\"display: none\\"')?>>\
          <td>Ответ #<span class=\"poll-num\"><?=($i+1)?></span></td>\
          <td>\
			<? if ($answer['id'] && !(hasPermissions('blogs') || $blog->fromuser_id == $uid)) { ?><input class=\"poll-answer-exists\" type=\"hidden\" name=\"answers_exists[<?=$answer['id']?>]\" value=\"1\" /><? } ?>\
			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\
			<tr>\
\
				<td><input maxlength=\"<?=blogs::MAX_POLL_ANSWER_CHARS?>\" class=\"poll-answer\" type=\"text\" value=\"<?=str_replace("\\", "&#92;", $answer['answer'])?>\" <?=($answer['id']? ((hasPermissions('blogs') || $blog->fromuser_id == $uid)? "name='answers_exists[{$answer['id']}]'": 'name=\\"it_no\\"  disabled'): "name='answers[]'")?> tabindex=\"20<?=$i?>\"></td>\
\
				<td class=\"poll-btn\"><a class=\"poll-del\" href=\"javascript: return false\" onclick=\"poll.del('Blogs', <?=$i++?>); return false;\"><img src=\"/images/delpoll.png\" width=\"15\" height=\"15\" border=\"0\" alt=\"Удалить ответ\" title=\"Удалить ответ\"></a></td>\
				<td class=\"poll-btn\"><span class=\"poll-add\">&nbsp;</span></td>\
			</tr>\
			</table>\
			</td>\
            </tr>\
			<?
			  }
            ?>";
		<? } ?>
    } else {
        out += "<input type='hidden' name='not_is_private' value='1'><input type='hidden' name='not_close_comments' value='1' />";
    }

		<?
        }
        ?>
        out += "<tr>"+
        <? if ($groups && $action == "edit") { ?>
         (mode==1 ? "<tr valign=\"top\">\
          <td><br />Раздел</td>\
          <td><br />\
             <select id=\"fcategory\" name=\"category\" onfocus=\"isFocus = true;\" onblur=\"isFocus = false;\" tabindex=300>\
               <? foreach ($groups as $id => $group) {
                    if ((!$group['read_only'] || ($group['read_only'] && !$mod)) && ($group['id']!=55 || $allow_love)) { ?>
                      <option value=\"<?=$group['id']?>|<?=$group['t']?>\" <?
                      if ((!$edit_msg['id'] && $gr_id == $group['id'] && $group['t'] == $base) || ($edit_msg['id'] && $edit_msg['id_gr'] == $group['id'] && $group['t'] == $edit_msg['base']) || $draft_data['category']==$group['id'])
                         {?>SELECTED<? } ?>><?=$group['t_name']?></option>\
               <? } } ?>
             </select>\
          </td><?php if ( hasPermissions('blogs') ) { ?></tr>\
          <tr valign=\"top\">\
            <td>&nbsp;</td>\
            <td><br />\
            <div class=\"b-check\"><input class=\"b-check__input\" type=\"checkbox\" id=\"ontopid\" name=\"ontop\" value=\"t\" <?=($edit_msg['ontop'] == 't')? 'checked=\"checked\"': ''?> />\
            <label class=\"b-check__label b-check__label_fontsize_11\" for=\"ontopid\">Закрепить тему наверху</label><\/div><\/td><?php } ?>" : '')+
        <? } ?>
       "</tr>\
          <td>&nbsp;</td><td><br />\
					    <input type=\"hidden\" name=\"ord\" value=\"<?=$bOrd?>\" />\
						<input type=\"hidden\" name=\"tr\" value=\"<?=$thread?>\" />\
						<input type=\"hidden\" name=\"reply\" value=\"<?=$main?>\" />\
						<input type=\"hidden\" name=\"page\" value=\"<?=$page?>\" />\
						<input type=\"hidden\" name=\"pagefrom\" value=\"<?=$bPageFrom?>\" />\
						<input type=\"hidden\" name=\"onpage\" value=\"\" />\
						<input type=\"hidden\" name=\"draft_id\" id=\"draft_id\" value=\"<?=$draft_id?>\" />\
						<input type=\"hidden\" name=\"draft_post_id\" id=\"draft_post_id\" value=\"<? echo $edit_msg['id']; ?>\" />\
            <input type=\"hidden\" name=\"u_token_key\" value=\""+U_TOKEN_KEY+"\" />\
						<input type=\"hidden\" name=\"action\" value=\"post_msg\" /><input <?=$use_draft?'style=\"display:none;\"':''?> tabindex=301 type=\"submit\" name=\"btn\" class=\"btn\" value=\"<?=(($gr_base == 5)? "Публиковать работу" : "Комментировать")?>\" />\
<? if($use_draft) { ?><div class=\"form-el\">\
<span class=\"todrafts\">\
<span class=\"time-save\" id=\"draft_time_save\" style=\"display:none;\"></span> <a href=\"javascript:DraftSave();\" onclick=\"this.blur();\" class=\"btnr-mb\"><span class=\"btn-lc\"><span class=\"btn-m\"><span class=\"btn-txt\">В черновики</span></span></span></a>\
</span>\
<span style=\"float: left;\">\
  <a id=\"btn\" class=\"b-button b-button_rectangle_color_green\"  href=\"javascript: void(0)\" onmousedown=\"return false\" onmouseup=\"if($('btn').get('disabled')==true) { return false; } $('btn_text').set('html','Подождите'); $('btn').set('disabled',true); $('btn' ).addClass('b-button_rectangle_color_disable'); $('btn' ).removeClass('b-button_rectangle_color_green'); submitLock($('frm')); return false;\">\
                <span class=\"b-button__b1\">\
                    <span class=\"b-button__b2\">\
                        <span class=\"b-button__txt\" id=\"btn_text\">Сохранить</span>\
                    </span>\
                </span>\
  </a>\
</span>\
</div><? } ?><\/td>\
				<\/tr>\
      <\/table><br /><\/form></td></tr></table></div>";
				<? /*if (!empty($multiattach_mode)) { ?>new mAttach(document.getElementById('attaches'), 10);<? }*/ ?>
                return(out);
			}
			<?}?>
			var formhtml = GetForm(0);

      function answer(num, attach, user, mode, max) {  <? // mode: 1 -- редактировать топ, 2 -- редактировать комментарий ?>
                if(max == undefined) max = 0;
                yt_link = false;
                var max_blog_file = <?=blogs::MAX_FILES;?>;
				td = document.getElementById('form'+num);
				if (oldid > 0){
					td1 = document.getElementById('form'+oldid);
					td1.innerHTML = "";
				}
        act_text = "<?=(($gr_base == 5)? "Публиковать работу" : "Комментировать")?>";
        if(mode==1||mode==2) act_text = 'Редактировать';
				if (!user) { user='<?=$blog->login?>'; }
                if (attach && attach.length) {

                    attach_text = '<div class="apf-addedfiles">\
                        <h3 style="margin: 16px 0 4px 0">Добавленные файлы:</h3>\
                        <table cellpadding="2" cellspacing="0" border="0">';

                    for (var i=0; i<attach.length; i++) {
                
                        attach_text += '\
                        <tr> \
                        <td> \
                            <img src="/images/list.gif" width="5" height="5" border="0"> \
                        </td> \
                        <td> \
                            <input type="hidden" name="filecount" value="10" /> \
                            <a href="<?=WDCPREFIX?>/' + attach[i].path + '/' + attach[i].fname + '" target="_blank">Посмотреть</a> ('+attach[i].ftype+'; '+attach[i].mb_size+') \
                        </td> \
                        <td> \
                            <input type="checkbox" name="delattach[]" value="' + attach[i].id + '" /> удалить \
                            <input type="hidden" name="filetodelete" id="filetodelete" value="" /> \
                        </td> \
                        </tr> \
                        ';

                
                    }
                    
                    attach_text += '</table> </div> <br /><br />';

                
            <?
            /*$edit_attach = array();
            for ($i = 0; $i < count($blog->thread); $i++) {
                if ($blog->thread[$i]['id'] == $edit_id) $edit_attach =& $blog->thread[$i]['attach'];
            }
            ?>                
            <? if ($edit_attach) { ?>
                attach_text = '<div class="apf-addedfiles">\
            <h3 style="margin: 16px 0 4px 0">Добавленные файлы:</h3>\
            <table cellpadding="2" cellspacing="0" border="0">\
            <?
            foreach ($edit_attach as $attach) {
                $attachFile = new CFile("users/".substr($blog->login, 0, 2)."/".$blog->login."/upload/".$attach['fname']);
                $attach['ftype'] = $attachFile->getext();
            ?> \
            <tr> \
                <td> \
                <img src="/images/list.gif" width="5" height="5" border="0"> \
                </td> \
                <td> \
                    <input type="hidden" name="filecount" value="10" /> \
                    <input type="hidden" id="hidden_file<?=$i+1?>_name" name="hidden_file_name" value="<?=$attach['fname']?>" /> \
                    <input type="hidden" name="hidden_file<?=$i+1?>_size" value="<?=$attach['size']?>" /> \
                    <input type="hidden" name="hidden_file<?=$i+1?>_ftype" value="<?=$attach['ftype']?>" /> \
                    <a href="<?=WDCPREFIX?>/users/<?=$blog->login?>/upload/<?=$attach['fname']?>" target="_blank">Посмотреть</a> (<?=$attach['ftype']?>; <?=ConvertBtoMB($attachFile->size)?> ) \
                </td> \
                <td> \
                    <input type="checkbox" name="delattach[]" value="<?=$attach['id']?>"> удалить \
                    <input type="hidden" name="filetodelete" id="filetodelete" value="" /> \
                </td> \
                </tr> \
                <?}?> \
                </table> \
                </div> <br><br>';
                <? } else { ?>
                    attach_text = '';
                <? } */ ?>
                }
				else {
					attach_text='';
				}
    				if(max_blog_file <= max) {
    				    is_attach = 0; 
                    } else {
                        is_attach = 1;
    				}

        td.innerHTML = GetForm(mode, 0, is_attach);


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
                                      if(!$alert) {
                                        $attachedfiles_tmpblog_files = blogs::getAttachedFiles($edit_msg['id']);
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

if(!mode && mode!=0) {
  xajax_ResetAttachedfiles();
}
        
		if (mode) {
      if (document.getElementById('poll-question')) {
			poll.init('Blogs', 'editForm', <?=blogs::MAX_POLL_ANSWERS?>, '<?=$_SESSION['rand']?>');
	  		maxChars('poll-question', 'poll-warn', <?=blogs::MAX_POLL_CHARS?>);
      }
		}
        <? if (!empty($multiattach_mode)) { ?>if (document.getElementById('attaches')) new mAttach(document.getElementById('attaches'), <?=blogs::MAX_FILES?>-max);<? } ?>
				if (document.getElementById('frm')) document.getElementById('frm').reply.value = num;
				oldid = num;
			}

			<? } else { ?>

			var formhtml = "";

			function answer(num){
				window.location.replace("/fbd.php");
			}

			<? } ?>

			//-->
</script>
<?


if ($gr_base == 3 || $gr_base == 4 || $gr_base == 5 || !isset($gr_base)) {
	if (isset($_SESSION['fp_kind'])){
		$ref_uri = "/projects/?kind=".$_SESSION['fp_kind']."&page=".$_SESSION['fp_page'];
		unset($_SESSION['fp_kind']);
		unset($_SESSION['fp_page']);
		$count = 1;
	} else $ref_uri = str_replace(HTTP_PREFIX.$_SERVER["HTTP_HOST"], "", $_SERVER["HTTP_REFERER"], $count);

	if ($_SESSION['p_ref'] != $ref_uri && $count)
	$_SESSION['p_ref'] = $ref_uri;
} ?>


<?php
$crumbs = array();
$crumbs[] = array("title"=>"Блоги", "url"=>"/blogs/");
$crumbs[] = array("title"=>$gr_name, "url"=>getFriendlyURL("blog_group", $gr_id)."?from={$thread}".($gr_base ? "&amp;t=prof" : ""));
?>
<div class="b-menu b-menu_crumbs "><?=getCrumbs($crumbs, 'blogs')?></div>


<?if($ban_where):?>
<form id="frm">
<div>
 <input type="hidden" name="ord" value="<?=$bOrd?>" />
 <input type="hidden" name="olduser" value="" />   
						<input type="hidden" name="tr" value="<?=$thread?>" />
						<input type="hidden" name="reply" value="<?=$main?>" />
						<input type="hidden" name="page" value="<?=$page?>" />
						<input type="hidden" name="pagefrom" value="<?=$bPageFrom?>" />
						<input type="hidden" name="onpage" value="" />
</div>
</form>
<? endif;?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 10px;">
<tr>
	<td  valign="top"  <? if ($gr_base < 100) { ?> class="box2" <? } ?>>

<table width="100%" cellspacing="0" cellpadding="15" border="0">
	<? if ($gr_base < 100) { 
	    
	    ?>
	<tr>
		<td style=" padding: 15px 19px 0 0; text-align:right;"><?
		seo_start();
		if (get_class($blog) == "blogs_articles") { ?><b>[<a href="/articles/<?=(($page = (int)InGet('bp'))? "?page=$page": '')?>" class="blue"><b>Назад</b></a>]</b><? } else {
			if (($gr_base == 3 || $gr_base == 4 || $gr_base == 5 || !isset($gr_base)) && $_SESSION['p_ref']) {

				if (!$_SESSION["prj_ref_link"]) { $_SESSION["prj_ref_link"]=$_SESSION['p_ref']; }

		     ?><b>[<a href="<?=($_SESSION["prj_ref_link"] ? $_SESSION["prj_ref_link"]."#prj".$gr_id  : $_SESSION['p_ref']."#prj".$gr_id)?>" class="blue"><b>Назад</b></a>]</b>
		<? } elseif ($gr_base != 3 && $gr_base != 4  && $gr_base != 5) {
			$_SESSION["prj_ref_link"]='';
		    ?>
		<b>[<a href="<?=getFriendlyURL("blog_group", $gr_id)?>?<?=($page)?"page=$page&":''?><?=(($page = (int)InGet('bp'))? "page=$page": '')?><?=($gr_base)?"&t=prof":""?>&from=<?=$thread?><?=($bOrd?"&ord=".$bOrd:"")?>#b<?=$thread?>" class="blue"><b>Назад</b></a>]</b>
		<? } } ?>
        <?= seo_end();?>
		</td>
	</tr>
	<tr>
	<td style="padding:19px">
		<? if ($error) print(view_error($error)."<br /><br />")?>
                                        <?
                                        $additional_info = '';
                                        if((hasPermissions('blogs')) && $blog->deleted) {
                                            $css_deleted = ' style="color:#cccccc;" ';
                                            $additional_info = '['.$blog->deluser_id.']';
                                        } else {
                                            $css_deleted = '';
                                        }
                                        ?>

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr valign="top">
			<td width="60"><div style="width:60px"><?=view_avatar_info($blog->login, $blog->photo, 1)?></div></td>
			<td class="bl_text">

<div style="margin-bottom:15px;">

			
			
			<? /*if ($blog->payed == 't') { ?><?=($blog->cnt_role == 'emp')?view_pro_emp():view_pro2(($blog->payed_test=="t")?true:false)?><? } */?>
      <? 
      
      if ($blog->login) {?> 
      <span class="<?=$blog->role?>_name11 b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><a class="<?=$blog->role?>_name11" href="/users/<?=$blog->login?>/?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>" title="<?=($blog->uname." ".$blog->usurname)?>"><?=($blog->uname." ".$blog->usurname)?></a> 
      <? seo_start()?>
      [<a href="/users/<?=$blog->login?>/?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>" class="<?=$blog->role?>_name11" title="<?=$blog->login?>"><?=$blog->login?></a>]</span>
      <?= seo_end()?>
			<? if ($blog->title!=='' && !strcmp($blog->login,"Anonymous") && $gr_id == 3){
				preg_match("/^([^(@@@:)]*)@@@: ([^\s]*)/", $blog->title, $matches);
				$blog->uname = $matches[1];
				
				
				$blog->msgtext = "<a href=\"mailto:".$blog->msgtext."\">".$matches[2]."</a><br />".$blog->msgtext;
				$blog->title = "";
			}
			    /*!!!is_team!!!*/
			    $pro = ($blog->payed == 't'?($blog->role == 'emp'?view_pro_emp():view_pro2(($blog->payed_test=="t")?true:false)):""); 
                $is_team = view_team_fl();
                ?>
                <?=$blog->is_team=='t'?$is_team:$pro?><?= is_verify($blog->login) ? view_verify() : ''?> <?= ( $blog->completed_cnt > 0 ? view_sbr_shield() : '' );?>      <? if ($blog->post_time) { ?> &#160; <span class=" b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_71"><?=date("[d.m.Y | H:i]",strtotimeEx($blog->post_time))?></span><? } ?>
			<? } else { ?> <span class="frlname11 b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><?=$blog->uname?></span><? }  ?>
			
			<? if ($blog->modified) {?> 
     <span class="b-layout__txt b-layout__txt_fontsize_11">
         <?	if (!$blog->modified_id || $blog->modified_id == $blog->fromuser_id) { ?> [внесены изменения: <?=date("d.m.Y | H:i]",strtotimeEx($blog->modified)); }
               else { ?>Отредактировано модератором <? if (!$mod) { ?>( <? if (!$user) $user = new user(); $mod_user = $user->GetName($blog->modified_id, $err); print($mod_user['login'] . ' : ' . $mod_user['usurname'] . ' ' . $mod_user['uname']); ?> ) <? } ?> <?=date("[d.m.Y | H:i]",strtotimeEx($blog->modified));?><? }?>
     </span>      
   <br />
			<? }?>
			<? if ($blog->deleted) {?> 
       <span class="b-layout__txt b-layout__txt_color_c10600">
          <? if (!$blog->deluser_id || $blog->deluser_id == $blog->fromuser_id) { ?>
            Удалено автором <?=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted));?>
          <? } else { ?>
            Удалено модератором<? if (!$mod) { ?> 
              ( <? if (!$user) $user = new user(); $mod_user = $user->GetName($blog->deluser_id, $err); print($mod_user['login'] . ' : ' . $mod_user['usurname'] . ' ' . $mod_user['uname']); ?> )
										<? } ?> 
														<? if($blog->thread[count($blog->thread) - 1]['deleted_reason']) { ?>Причина удаления: <?=$blog->thread[count($blog->thread) - 1]['deleted_reason']?><? } ?> 
														<?=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted));?>
          <? } ?>
        </span><br />
     <? }?>
      
</div>
			<? if ($blog->title!=='') { 
                $sTitle = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->title) :*/ $blog->title;
            ?><h1 class="bl_name" <?=$css_deleted?>><?=reformat($sTitle,52,0,1); ?></h1><? } ?>


	<!--<div class="blog-one-cnt" id="message<?=$blog->id?>" <?=$css_deleted?> style="width:<?=(855-(($blog->level>18?18:$blog->level)*20))."px;"?>"> эти стили слишком расширяли блоги и они ломались, поэтому пока закомментил-->
			<div class="blog-one-cnt" id="message<?=$blog->id?>" <?=$css_deleted?> >


            <?php $sMessage = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->msgtext) :*/ $blog->msgtext; ?>
            <? $sMessage = reformatExtended($sMessage); ?>
			<?=reformat($sMessage, 83, 0, -($blog->is_chuck=='t'), 1)?></div><br />

			<? 
			$i = 0;
			if ($blog->poll) { 
				$voted = $blog->Poll_Voted($_SESSION['uid'], $thread);
				$max = 0;
				if ($blog->poll_closed == 't') {
					foreach ($blog->poll as $poll) $max = max($max, $poll['votes']);
				}
			?>
			
			
			<div id="poll-<?=$thread?>" class="poll">
                <?php $sQuestion = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->poll_question) :*/ $blog->poll_question; ?>
				<div class="poll-theme"><?=reformat($sQuestion, 40, 0, 1)?></div>
				<div id="poll-answers-<?=$thread?>">
				<? if (($blog->poll_closed == 't' || $blog->deleted)||($voted || !$_SESSION['uid'] || $ban_where == 1 || $blog->is_blocked)) { ?>
				<table class="poll-variants">
				<? foreach ($blog->poll as $poll) { ?>
                <?php $sAnswer = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($poll['answer']) :*/ $poll['answer']; ?>
				<tr>
				<? if ($blog->poll_closed == 't' || $blog->deleted) { ?>
					<td class="bp-res"><?=$poll['votes']?></td>
					<td class="bp-vr"><label for="poll_<?=$i?>"><?=reformat($sAnswer, 40, 0, 1)?></label></td>
					<td><div class="res-line rl1" style="width: <?=($max? round(((100 * $poll['votes']) / $max) * 3): 0)?>px;"></div></td>
				<? } else { ?>
					<? if ($voted || !$_SESSION['uid'] || $ban_where == 1 || $blog->is_blocked) { ?>
						<td class="bp-gres"><?=$poll['votes']?></td>
						<td class="bp-vr"><label for="poll_<?=$i?>"><?=reformat($sAnswer, 40, 0, 1)?></label></td>
						<td><div class="res-line rl1" style="width: <?=($max? round(((100 * $poll['votes']) / $max) * 3): 0)?>px;"></div></td>
					<? } ?>
				<? } ?>
				</tr>
				<? } ?>
				</table>
				<? } ?>
                
				<? if (!(($blog->poll_closed == 't' || $blog->deleted)||($voted || !$_SESSION['uid'] || $ban_where == 1 || $blog->is_blocked))) { ?>
                <? if (!($blog->poll_multiple == 't')) { ?><div class="b-radio b-radio_layout_vertical"><? } ?>
				<? foreach ($blog->poll as $poll) { ?>
                <?php $sAnswer = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($poll['answer']) :*/ $poll['answer']; ?>
                
						<? if ($blog->poll_multiple == 't') { ?>
							<div class="b-check  b-check_padbot_5">
                            <input  class="b-check__input" type="checkbox" name="poll_vote[]" id="poll-<?=$thread?>_<?=$i?>" value="<?=$poll['id']?>" /><label class="b-check__label b-check__label_fontsize_13" for="poll_<?=$i++?>"><?=reformat($sAnswer, 40, 0, 1)?></label>
                            </div>
						<? } else { ?>
                        	<div class="b-radio__item  b-radio__item_padbot_10">
                            	<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                                	<tr class="b-layout__tr">
                                    	<td class="b-layout__left b-layout__left_width_15"><input class="b-radio__input" type="radio" name="poll_vote" id="poll-<?=$thread?>_<?=$i?>" value="<?=$poll['id']?>" /></td>
                                        <td class="b-layout__right"><label class="b-radio__label b-radio__label_fontsize_13" for="poll_<?=$i++?>"><?=reformat($sAnswer, 40, 0, 1)?></label></td>
                                    </tr>
                                </table>
                            </div>
						<? } ?>
				<? } ?>
				<? if (!($blog->poll_multiple == 't')) { ?></div><? } ?>
				<? } ?>
                
                </div>


				<div class="poll-options">
					<? if (!$voted && $_SESSION['uid'] && $blog->poll_closed != 't' && $ban_where != 1 && !$blog->is_blocked && !$blog->deleted) { ?>
                    <div class="b-buttons b-buttons_inline-block">
					<span id="poll-btn-vote-<?=$thread?>"><a class="b-button b-button_rectangle_color_transparent" href="javascript: return false;" onclick="poll.vote('Blogs', <?=$thread?>); return false;"><span class="b-button__b1"><span class="b-button__b2"><span class="b-button__txt">Ответить</span></span></span></a>&nbsp;&nbsp;&nbsp;</span>
					<span id="poll-btn-result-<?=$thread?>"><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false;" onclick="poll.showResult('Blogs', <?=$thread?>); return false;">Посмотреть результаты</a>&nbsp;&nbsp;&nbsp;</span>
                    </div>
					<? } else { ?>
					<span id="poll-btn-vote-<?=$thread?>"></span>
					<span id="poll-btn-result-<?=$thread?>"></span>
					<? } ?>
					<? if (($blog->fromuser_id == $_SESSION['uid'] && $ban_where != 1 && !$blog->is_blocked) || hasPermissions('blogs')) { ?>
					<span id="poll-btn-close-<?=$thread?>"><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false" onclick="poll.close('Blogs', <?=$thread?>); return false;"><?=(($blog->poll_closed == 't')? 'Открыть': 'Закрыть')?> опрос</a>&nbsp;&nbsp;&nbsp;</span>
					<span id="poll-btn-remove-<?=$thread?>"><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false" onclick="poll.remove('Blogs', <?=$thread?>); return false;">Удалить опрос</a></span>
					<? } ?>
				</div>
			</div>
			<? } ?>
			</td>
		</tr>
			<?
            seo_start();
			if ($blog->attach)
			{
				foreach ($blog->attach as $attach)
                {
                    $att_ext = CFile::getext($attach['fname']);
                    $str = '';

                    /*if ($att_ext == "swf") {
                        $str = viewattachExternal($blog->login, $attach['fname'], "upload", "/blogs/view_attach.php?user=".$blog->login."&attach=".$attach['fname']);
                    
                    } elseif($att_ext == 'flv') {
                        $str = viewattachLeft($blog->login, $attach['fname'], "upload", $file, 1000, 470, 307200, true);
                    } else {
                        $str = '<div class="flw_offer_attach">'.viewattachLeft($blog->login, $attach['fname'], "upload", $file, 1000, 600, 307200, false, (($attach['small']==2)?1:0)).'</div>';
                    }*/
                    $str = '<div class="flw_offer_attach" style="padding-left:60px">'.viewattachLeft($blog->login, $attach['fname'], "upload", $file, 1000, 600, 307200, false, (($attach['small']==2)?1:0), 1, 0, "{$foto_alt} фото {$attach['fname']}", "{$foto_alt} (фото)").'</div>';
                    print("<tr><td colspan='2'>".$str."</td></tr>");
                
                }

				
			}
			elseif ($attach_ar && count($attach_ar))
			{
					foreach ($attach_ar as $temp) {

						$att_ext = CFile::getext($temp['fname']);

						/*if ($att_ext == "swf") {
							$str = viewattachExternal($blog->login, $temp["fname"], "upload", "/blogs/view_attach.php?user=".$blog->login."&attach=".$temp["fname"]);
						} elseif($att_ext == 'flv') {
                            $str = viewattachLeft($blog->login, $temp['fname'], "upload", $file, 1000, 470, 307200, true);
                        } else {
							$str = '<div class="flw_offer_attach">'.viewattachLeft($blog->login, $temp["fname"], "upload", $file, 1000, 600, 307200, false, 0).'</div>';
						}*/
                        $str = '<div class="flw_offer_attach" style="padding-left:60px">'.viewattachLeft($blog->login, $temp["fname"], "upload", $file, 1000, 600, 307200, false, 0, 1, 0, "{$foto_alt} фото {$temp['fname']}").'</div>';

						if ($file) { print("<tr><td colspan='2'><br />".$str."<br /></td></tr>"); }
						else { print("<tr><td colspan=\"2\">".$str."</td></tr>"); }
					}

			}?>
			<?= seo_end();?>
		</table>
		<?
        if ($blog->yt_link)
        {
            print('<br clear="all" /><center>' . show_video($blog->id,$blog->yt_link).'</center>');
        }
		?>
        <div id="thread-reason-<?=$thread?>" style="margin-top: 20px;<?=($blog->is_blocked? 'display: block': 'display: none')?>"><? 
				if ($blog->is_blocked) {
					$moder_login = (hasPermissions('blogs'))? $blog->admin_login: '';
					$reason      = reformat( $blog->reason, 24, 0, 0, 1, 24 );
					print BlockedThreadHTML($reason, $blog->blocked_time, $moder_login, "{$blog->admin_name} {$blog->admin_uname}");
				} else {
					print '&nbsp;';
				}
		?></div>
        <div id="warnreason-<?=$blog->id?>" style="display:none">&nbsp;</div>
		</td>
	</tr>
	<? } ?>
<?
//rus

if ($_GET["openlevel"]) { $openlevel=intval($_GET["openlevel"]); } else { $openlevel=0; }// Ид с которого открыть тред
if ($bPageFrom) { $PageFrom=$bPageFrom; } else { $PageFrom=0; } // постраничка
if ($_GET["openalllevels"]) { $OpenAllLevels=$_GET["openalllevels"]; } else { $OpenAllLevels=0; } // все треды развернуты
if ($_GET["wopages"]) { $MaxOnPage=999999999; } else { $MaxOnPage=10; } // максимум корневых записей на стр

$MaxOnPage=999999999;
$OpenAllLevels=1;

$MinOpenLevel=2; // до какого уровня развернуты

$BlockLevel=false;
$FirstTop=true;
$OpenLevelFrom=999;
$PageCounter=0;
//$FirstClose=$blog->SearchFirstChild($blog->id);
//$LastClose=$blog->SearchLastChild($blog->id);
$FirstClose=0;
$LastClose=0;
$LevelCounter=0;
$LevelArray=  array();
$FirstId=0;
$msg_num = 1;
$innerDiv='<center><br /><img src=/images/processing.gif width=152 height=56 ><br /></center>';
$clearQueryStr=preg_replace("|\&pagefrom=.*|","",$_SERVER['QUERY_STRING']);
$clearQueryStr=preg_replace("|\&openlevel=.*|","",$clearQueryStr);
$clearQueryStrOpen=preg_replace("|\&openlevel=.*|","",$_SERVER['QUERY_STRING']);
$AllRootRecord=0;

if ($blog->thread)
foreach ($blog->thread as $node) {
	if ( $node['reply_to']==$blog->id) { $AllRootRecord++; }
}
$NextPostPage= ($PageFrom+$MaxOnPage<$AllRootRecord ? floor($AllRootRecord/$MaxOnPage)*$MaxOnPage : 0);

//print $NextPostPage."---";
?>	<? if ($gr_base < 100) { ?>
	<tr><td style="padding:0;" valign="bottom"><div class="footer">
				<? if ($grey_line) { ?>

<?
		if ($uid)
		{
?>
				<div class="star-outer"><img src="/images/bookmarks/<?=(!isset($favs[$thread]))?'bsw.png':blogs::$priority_img[$favs[$thread]['priority']]?>" alt="Добавить в закладки" title="Добавить в закладки" class="star" id="favstar<?=$thread?>" style="position: absolute; cursor: pointer;" onclick="ShowFavFloat(<?=$thread?>)" /><div id="FavFloat<?=$thread?>" style="postiton:absolute; margin-left:-20px; margin-top:-15px;"></div></div>
				<div id="favcnt<?=$thread?>" class="favor-number"><span><?=$blog->fav_cnt?></span></div>

<?
		}
		else {
?>
    <div class="star-outer"><img src="/images/bookmarks/bsw.png" alt="" title="" class="star" id="favstar<?=$thread?>" style="position: absolute;" /></div>
    <div class="favor-number"><span><?=$blog->fav_cnt?></span></div>
<?php
		}
seo_start();		
?>
			<div class="section-blog">
				<div class="section ">Раздел:</div>
				<div class="small"><a href="<?=getFriendlyURL("blog_group", $gr_id)?>"><?=$gr_name?></a></div>
			</div>
<?= seo_end();?>
				<? } ?>
				<div class="commline">

<? if(hasPermissions('blogs') && $blog->deleted) { ?>				
    <a href="?id=<?=$blog->id?>&action=restore&ord=<?=$bOrd?>" onclick="return warning(14);">Восстановить</a> | 
<? } else { ?>
				<?if (hasPermissions('blogs') && $blog->login!=$_SESSION["login"] && $blog->login['login']!="admin") {
				    ?>
				    <script type="text/javascript">
                    banned.addContext( 'blog_<?=$thread?>', 2, '<?=$GLOBALS['host']?><?=getFriendlyURL("blog", $thread)?>', "<?=($blog->title!=='' ? $blog->title : '<без темы>')?>" );
                    </script>
				    <?php
            if(hasPermissions('users')) {
					if ( $blog->warn<3 && !$blog->is_banned && !$blog->ban_where ) {
						?><span class="warnlink-<?=$blog->fromuser_id?>"><a style="color: #D75A29; font-size:9px;" href="javascript: void(0);" onclick="banned.warnUser(<?=$blog->fromuser_id?>, 0, 'blogs', 'blog_<?=$thread?>', 0); return false;">Предупредить (<span class="warncount-<?=$blog->fromuser_id?>"><?=($blog->warn ? $blog->warn : 0)?></span>)</a></span> | <?
					}
					else /*if (!$blog->is_banned)*/ {
					    $sBanTitle = (!$blog->is_banned && !$blog->ban_where) ? 'Забанить!' : 'Разбанить';
						?><span class="warnlink-<?=$blog->fromuser_id?>"><a href="javascript:void(0);" onclick="banned.userBan(<?=$blog->fromuser_id?>, 'blog_<?=$thread?>',0)" style="color: Red;font-size:9px;"><?=$sBanTitle?></a></span> | <?
					} } ?><span id="thread-button-<?=$thread?>"><a style="color: Red; font-size:9px;" href="javascript: void(0);" onclick="banned.<?=($blog->is_blocked? 'unblockedThread': 'blockedThread')?>(<?=$thread?>); return false;"><?=($blog->is_blocked? 'Разблокировать': 'Блокировать')?></a></span> | <?
                }
                     $parent_login = $blog->login;
                     if ($blog->id && $blog->login == $_SESSION['login'] && !$blog->is_blocked && $gr_base != 3 && $gr_base != 4 || !$mod) {
					?>
            <? if ($gr_base == 5 && $winner) { ?> <a href="#winner">Победитель</a> |
            <a  style="color: Red; " href="view.php?tr=<?=$thread?>&action=deletewinner&winner=<?=$winner?>&ord=<?=$bOrd?>" style="color: #D75A29;" onclick="return warning(1);">Отменить победителя!</a> |
					
            <? } ?>
            <? if ($gr_base == 5 && !$winner) { ?> Победитель не определен | <? } ?>
			
            <? if ($gr_base == 5 || $gr_base == 3) { ?>
              <a href="<?=rawurlencode($_SERVER['PHP_SELF'])."?".htmlspecialchars($clearQueryStrOpen)?>&amp;id=<?=$blog->id?>&amp;action=delete&ord=<?=$bOrd?>" onclick="return warning(1);">Удалить</a> |
              <a href="/public/?step=1&public=<?=$blog->id_gr?>&red=<?=rawurlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'])?>">Редактировать</a> |
            <? }
               else
               {
                 if ( ($gr_base == 0 && !$blog->deleted && $blog->login == $_SESSION['login']) || !$mod ) { // в комментах.
                   ?><a href="/blogs/viewgroup.php?id=<?=$blog->id?>&action=delete&ord=<?=$bOrd?>&u_token_key=<?=$_SESSION['rand']?>" onclick="return warning(1);">Удалить</a>
                   | <?
                 }
            ?>
            <?php if (!$blog->deleted) {?>
              <a href="?id=<?=$blog->id?>&amp;action=edit&pagefrom=<?=$bPageFrom?>&amp;tr=<?=$thread?>&ord=<?=$bOrd?>">Редактировать</a> |
              <?}?>
            <? } ?>
          <? } ?>
          <?  $_i=''; if(blogs::isBlogSubscribe($thread, $uid) == true && $_SESSION["login"] != $blog->login && $uid && (!$blog->is_blocked && !$closed_comments)): $_i=' | ' ?>
          <a href="javascript:void(1)" id="blog_subscribe" onClick="xajax_DelBlogSubscribe(<?=$thread?>)">Отписаться от темы</a>
          <? elseif($uid && $_SESSION["login"] != $blog->login && (!$blog->is_blocked && !$closed_comments)):  $_i=' | ' ?>
          <a href="javascript:void(1)" id="blog_subscribe" onClick="xajax_SetBlogSubscribe(<?=$thread?>, 1)">Подписаться на тему</a>
          <? endif; ?>
<? } ?>
          <?php if (!$blog->deleted) {?>
              <? if ((!$blog->is_blocked && !$closed_comments) || hasPermissions('blogs')) { seo_start();?>
                 <?=$_i?><a <? if($_SESSION["login"]){?> href="javascript: void(0);" onclick="javascript:answer(<?=$blog->id?>, '','<?=get_login($uid)?>'); document.getElementById('frm').olduser.value = '<?=$uid?>'; document.getElementById('frm').scrollIntoView(true); <?=($NextPostPage? "document.getElementById('frm').onpage.value = '".$NextPostPage."';" : '' )?>" <? }else{?>href='/fbd.php'<? }?>>
                     <?=(($gr_base == 5)? "Публиковать работу" : "Комментировать")?>
                   </a>
                   <?= seo_end();?>
              <? } ?>
          <?php }?>

					<?
					$link = "/contacts/?from=".$blog->login."#form";
					if ($gr_base == 5 || $gr_base == 3) {?> | <a href="javascript: void(0);"  onclick="javascript: document.frmdet.submit();"> Оговорить детали</a><form action="<?=$link?>" method="post" name="frmdet"><input type="hidden" name="prjname" value="<?=htmlspecialchars($blog->title)?>"></form><?}?>
					
                </div>
                
                
			</div>
		</td></tr>


    <? if ($blog->close_comments || $blog->is_private) { ?>
      <tr>
        <td style="padding-top:25px">
          	<div style="padding:0 19px 19px">
          <? if ($blog->close_comments) { ?>
            <? if ($blog->fromuser_id == get_uid()) {?>
                <h3 class="bl">Вы запретили оставлять комментарии.</h3>
            <? } else { ?>
                <h3 class="bl">Автор блога запретил оставлять комментарии.</h3>
            <? } ?>
          <? } if ($blog->is_private) { ?>
          <h3 class="bl">Вы запретили просматривать эту запись.</h3>
          <? } ?>
          </div>
        </td>
      </tr>
    <? } ?>

	<tr style="display: none">
		<td>
		<input type="text" id="msg_name_source" value="<?=($error_flag? str_replace(array("'", '"', '<', '>'), array('&#039;', '&quot;', '&lt;', '&gt;'), stripslashes($_POST['msg_name'])): $blog->title)?>" />
		<textarea cols="50" rows="20" id="msg_source" style="display: none"><?=($error_flag? str_replace(array("'", '"', '<', '>'), array('&#039;', '&quot;', '&lt;', '&gt;'), stripslashes($_POST['msg'])): $blog->msgtext)?></textarea>
		</td>
	</tr>
		<tr>
			<td style="padding:0;">
<?php $sTitle = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->title, 'plain', false) :*/ $blog->title; ?>

<?php if (!$blog->deleted) {?>
<div class="b-free-share ">
  <div class="b-free-share__body b-free-share__body_padright_10 b-free-share__body_padbot_10 b-free-share__body_padtop_5">
  	<?= ViewSocialButtons('blog', $sTitle, true);?>
  </div>
</div>			
<?} ?>
			</td>
		</tr>
    <tr valign="bottom" class="qpr_b"><td  <? if (!($blog->msg_num == 1 && $uid)) {?>style="padding: 0px; height: 0px; margin-bottom: 0px; margin-top: 0px; "<?}?>  id="form<?=$blog->id?>"><? if ($blog->msg_num == 1 && $uid && !$blog->close_comments) {?>
    
    <script type="text/javascript">
    <!--
	answer(<?=$blog->id?>, '', '<?=get_login($uid)?>', 0);
    if (document.getElementById('frm')) document.getElementById('frm').olduser.value = '<?=$uid?>';
	//-->
	</script><? } ?><?
    
	if ($blog->id == $edit_id && ($blog->login == $_SESSION['login'] || !$mod)) {
		?>
		<script type="text/javascript">
		<!--
		domReady( function() {
		    
		answer(<?=$blog->id?>, <?=JS_Obj($blog->login, $blog->attach)?>, '',1, <?=count($blog->attach)?>);
		document.getElementById('frm').olduser.value = '<?=$blog->fromuser_id?>';
		<? /*document.getElementById('frm').msg_name.value = '<?=($error_flag)?(stripslashes(html_entity_decode($msg_name,ENT_QUOTES))):input_ref_scr(html_entity_decode($blog->title,ENT_QUOTES))?>';*/ ?>
		document.getElementById('frm').msg_name.value = document.getElementById('msg_name_source').value;
		<?/*document.getElementById('frm').msg.value = '<?=str_replace("\n", "\\n", ($error_flag)?input_ref(html_entity_decode($msg,ENT_QUOTES)):input_ref(html_entity_decode($blog->msgtext,ENT_QUOTES)))?>';
		document.getElementById('frm').msg.value = "<?=str_replace("\n", "\\n", ($error_flag)?input_ref(html_entity_decode($msg,ENT_QUOTES)):addslashes(input_ref(html_entity_decode($blog->msgtext,ENT_QUOTES))))?>"; */?>
        document.getElementById('frm').msg.value = document.getElementById('msg_source').value;
		if (document.getElementById('frm').yt_link != undefined) document.getElementById('frm').yt_link.value = '<?=($error_flag)?(stripslashes(html_entity_decode($yt_link,ENT_QUOTES))):input_ref_scr(html_entity_decode($blog->yt_link,ENT_QUOTES))?>';
		if (document.getElementById('frm').yt_link.value) document.getElementById('yt_link').style.display = '';
        document.getElementById('frm').btn.value = 'Сохранить';
		document.getElementById('frm').action.value = 'change';
		errmsg1 = errmsg2 = '';
		document.getElementById('poll-question').value = document.getElementById('poll-question-source').value;	
		} );
		//-->
			</script>
			<? } ?></td></tr>

			<? } ?><? if ($gr_base < 100) { ?><tr valign="top"><td style="padding-bottom: 0px; padding-top: 0px; height: 6px; margin-bottom: 0px; margin-top: 0px; min-height: 0px; line-height: 0px; background-image: url(/images/shadow_t.gif); background-repeat: repeat-x; padding: 0; background-position: top; border: none;"></td></tr><? } ?>
	<? if ($blog->msg_num > 1) {?>
    <tr>
	<td style="padding:0px;">
        <a name="comments"></a>
		<? if ($gr_base < 100) { ?><h1 class="bl" style="margin: 10px 0px 25px 15px;"><?=(($gr_base == 5)? "Конкурсные работы" : "Комментарии")?>:</h1><? } ?>
		<?




		//print $AllRottRecord;

		//$blog->GetThreeId(249, $threearr ,0);
		//print_r($threearr);
		//print "<pre>";
		//print_r($blog);
		//print "</pre>";

		$allow_del = ($blog->login == $_SESSION['login'])? 1 : 0;

		while ($blog->GetNext()) {
          $level = ($blog->level > 18?18:$blog->level);   // @todo Вынести максимальный левел в какую-нибудь глобальную для блогов переменную
		  $maxw_msg = round(81-2*($level));
		  $maxw_ttl = round(50-1.5*($level));
		  $width_level = (855-($level*20));
			if (!$blog->level) {
				$LastLink=$blog->SearchLastChildId($blog->id);
				if ($PageCounter<$PageFrom) { $PageCounter++; continue; $BlockLevel=true;  }
				if ($PageCounter>=$PageFrom+$MaxOnPage) { break; }
				$PageCounter++;
				$BlockLevel=false;
				$FirstTop=false;
			}
			if ($BlockLevel || $FirstTop) { continue; }
            $blog->msgtext = preg_replace("/(\<|\&lt;)\/script(\>|\&gt;)/i", '<\script>', $blog->msgtext);
            $msgtext = $blog->msgtext;

			$msg_num++;

			if ($last_id == $blog->id) print("<a name=\"post\" id=\"post\"></a>");
			if ($blog->id == $edit_id && $blog->login == $_SESSION['login']) print("<a name=\"edit\" id=\"edit\"></a>");
            if ($blog->attach && is_array($blog->attach)) {
                $attach_html = '';
                foreach ($blog->attach as $i=>$attach) {
                    $i++;
                    $att_ext = CFile::getext($attach['fname']);
                    if($i != count($blog->attach)) $br = "<br /><br />";
                    else $br = "";
                    /*if ($att_ext == "swf") {
                        $attach_html .= viewattachExternal($blog->login, $attach['fname'], "upload", "/blogs/view_attach.php?user=".$blog->login."&attach=".$attach['fname']).$br;
                    } elseif($att_ext == 'flv') {
                        $attach_html .= viewattachLeft($blog->login, $attach['fname'], "upload", $file, 1000, 470, 307200, true).$br;
                    } else {
                        $attach_html .= '<div class="flw_offer_attach">'.viewattachLeft($blog->login, $attach['fname'], "upload", $file, 1000, 600, 307200, false, (($attach['small']==2)?1:0)).'</div>';
                    }*/
                    $attach_html .= '<div class="flw_offer_attach">'.viewattachLeft($blog->login, $attach['fname'], "upload", $file, 1000, 600, 307200, false, (($attach['small']==2)?1:0), 1, 0, "{$foto_alt} фото {$attach['fname']}").'</div>';
                }
            } else if ($blog->attach) {
                $att_ext = CFile::getext($blog->attach);
                /*if ($att_ext == "swf") {
                    $attach_html = viewattachExternal($blog->login, $blog->attach, "upload", "/blogs/view_attach.php?user=".$blog->login."&attach=".$blog->attach);
                } elseif($att_ext == 'flv') {
                    $attach_html = viewattachLeft($blog->login, $blog->attach, "upload", $file, 1000, 470, 307200, true);
                } else {
                    $attach_html = '<div class="flw_offer_attach">'.viewattachLeft($blog->login, $blog->attach, "upload", $file, 1000, 600, 307200, false, (($blog->small==2)?1:0)).'</div>';
                }*/
                $attach_html = '<div class="flw_offer_attach">'.viewattachLeft($blog->login, $blog->attach, "upload", $file, 1000, 600, 307200, false, (($blog->small==2)?1:0), 1, 0, "{$foto_alt} фото {$blog->attach}").'</div>';
            }
			$padding = ($blog->level > 18) ? 380 : (($blog->level+1)*20);
			//if (in_array($blog->reply, $cur_user_msgs)) $allow_del = 1;
			//if ($blog->login == $_SESSION['login']) $cur_user_msgs[] = $blog->id;


			if ($blog->level<=$OpenLevelFrom) { $OpenLevelFrom=999; }
			if ($openlevel && $openlevel==$blog->id) { $OpenLevelFrom=$blog->level; }
			if (!$OpenAllLevels && $blog->level) {

				if ($blog->level<$OpenLevelFrom && $blog->level>$MinOpenLevel) {
					// закрытые треды
					if (!$blog->deleted)
					{
						print '<span  name="'.$blog->id.'" id="'.$blog->id.'">';
						$LevelCounter++;
						$FirstClose=$blog->id;
						$LastClose=$blog->SearchLastChildId($blog->id);
						$LevelArray[$LastClose]++;
						//$blog->GetThreeId($blog->id, $threearr ,0);
						//print_r($threearr);
						//print "<br>";
						//$LastClose=$threearr[count($threearr)-1];
						$FirstId=1;

					}
					// print $blog->id." - ".$blog->level." - ".$PageCounter." - ".$FirstClose." - ".$LastClose."<br>";
					?>
	<?if ($openlevel==$blog->id) {?><a name="o<?=$openlevel?>"></a><?}?><table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr valign="top">
		<!-- <? if ($msg_num != $blog->msg_num) { if (!$blog->attach || ($blog->attach && $file)) { ?> class="qpr" <? } } ?> //-->
		<td style="<? if ($blog->level) { ?>padding-left: <?=$padding?>px;<? } ?> padding-right: 10px; <? if($blog->new == 't'){?>background-color: #f0ffe2;<?}?>">&nbsp;</td>
		<td class="bl_text" width="100%" <? if($blog->new == 't'){?> style="background-color: #f0ffe2;"<? }?>>
		
		<? /*if ($blog->payed == 't')
		{
			if ($blog->cnt_role == 'emp')
			view_pro_emp();
			else
			view_pro2(($blog->payed_test=="t")?true:false);
			} */?>

      <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color<?=$blog->cnt_role?>"><a href="/users/<?=$blog->login?>/?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>" class="b-layout__link b-layout__link_color<?=$blog->cnt_role?>" title="<?=($blog->uname." ".$blog->usurname)?>"><?=($blog->uname." ".$blog->usurname)?></a> <?seo_start();?>[<a href="/users/<?=$blog->login?>/?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>" class="b-layout__link b-layout__link_color<?=$blog->cnt_role?>" title="<?=$blog->login?>"><?=$blog->login?></a>]<?= seo_end();?></span>	
      	<?
		/*!!!is_team!!!*/
		$pro = ($blog->payed == 't'?($blog->role == 'emp'?view_pro_emp():view_pro2(($blog->payed_test=="t")?true:false)):""); 
        $is_team = view_team_fl();
        ?>
        <?=$blog->is_team=='t'?$is_team:$pro?><?= is_verify($blog->login) ? view_verify() : ''?> <?= ( $blog->completed_cnt > 0 ? view_sbr_shield() : '' );?> &#160; 
      <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_71"><?=date("[d.m.Y | H:i]",strtotimeEx($blog->post_time))?></span>
			<? if ($blog->deleted) {
				if (isset($blog->thread) && is_array($blog->thread) && (count($blog->thread) > 0))
				{
					$buser_id = $blog->thread;
					$buser_id = array_pop($buser_id);
					$buser_id = $buser_id['fromuser_id'];
				}
				if ($blog->deluser_id == $blog->fromuser_id) { ?> Комментарий удален автором <?=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted)); }
			     elseif ($blog->deluser_id == $buser_id) { ?> Комментарий удален автором темы <?=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted));?>
			<?   } else { ?> Комментарий удален модератором <? if (!$mod) { ?>( <? $del_user = $user->GetName($blog->deluser_id, $err); print($del_user['login'] . ' : ' . $del_user['usurname'] . ' ' . $del_user['uname']); ?> ) <? } ?><?=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted));?><?}?>
			<? } else {?>
  			<b>
  			<? if ($blog->title!=='') { ?>
  			<? if ($blog->login == "Anonymous"){
  				list($name, $mail) = sscanf($blog->title, "%s @@@: %s");
  				print $name." ".$mail;
  			} else { 
                $sTitle = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->title) :*/ $blog->title;
                print reformat($blog->title, $maxw_ttl); } ?><? } else {?><?}?>
  			</b>
  			<br /><br />
  			<div style="color: #D75A29; font-size:9px;<? if ($blog->attach && !$file) { ?> padding-left: <?=$padding+60?>px;<? } ?>">
  			
  			<? if ($blog->login == $_SESSION['login'] || $allow_del || !$mod) {?>
  			<a href="<?=rawurlencode($_SERVER['PHP_SELF'])."?".htmlspecialchars($clearQueryStrOpen)?>&amp;id=<?=$blog->id?>&amp;action=delete&ord=<?=$bOrd?>&u_token_key=<?=$_SESSION['rand']?>" style="color: #D75A29;" onclick="return warning(1);">Удалить</a> |
  			<? } if ($blog->login == $_SESSION['login'] || (!$mod)) {?>
  			<a href="<?=rawurlencode($_SERVER['PHP_SELF'])."?".htmlspecialchars($clearQueryStrOpen)?>&amp;id=<?=$blog->id?>&amp;action=edit&pagefrom=<?=$bPageFrom?>&amp;tr=<?=$thread?>&ord=<?=$bOrd?>#efrm" style="color: #D75A29;">Редактировать</a> |
  			<? } ?>
  			<?seo_start();?>
  			  <? if (!$closed_comments) { ?>
            <a <?if($_SESSION['login']){?> href="javascript: void(0);" onclick="javascript:answer(<?=$blog->id?>, '', '<?=get_login($uid)?>'); document.getElementById('frm').olduser.value = '<?=$uid?>'; " <?}else{?>href="/fbd.php"<?}?> style="color: #D75A29">Комментировать</a> |
  			  <? } ?>
  			<a href="<?=rawurlencode($_SERVER['PHP_SELF'])."?".htmlspecialchars($clearQueryStrOpen)."&openlevel=".$blog->id.$ord_get_part."#o".$blog->id?>" style="color: #D75A29">Ссылка</a>
  			<?= seo_end();?>
  			<? if ($FirstId) { $FirstClose=$blog->id;  $FirstId=0; ?> <?if (!$blog->deleted) {?> |<? }else {?><br /><br /><div style="color: #D75A29;font-size:9px;"><?}?> <a href="javascript: void(0);" onclick="javascript: var ddiv=document.getElementById('<?=$FirstClose?>'); ddiv.innerHTML='<?=$innerDiv?>'; xajax_openlevel(<?=$thread?>, <?=$mod?>, <?=$FirstClose?>, <?=$LastClose?>, <?=$PageFrom?>, <?=$thread?>, <?=$LastLink?>,'<?=$bOrd?>');" style="color: #D75A29">Развернуть </a>  <?if ($blog->deleted) {?></div><?} }?>
  			<?php } ?>
   			</div></td></tr>
  			<tr <? if (!$blog->level || $LastLink==$blog->id) { ?> class="qpr" <? } ?>><td colspan="2" ><br /></td></tr>

  			</table>
  			
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-bottom: 0px; padding-top: 0px; height: 0px;" >
		<tr   style="padding-bottom: 0px; padding-top: 0px; height: 0px;"  class="n_qpr"><td  style="padding-bottom: 0px; padding-top: 0px; height: 0px;"  colspan="3" id="form<?=$blog->id?>">
		<? if ($blog->id == $edit_id && ($blog->login == $_SESSION['login'] || !$mod)) {?>
            <script language="JavaScript" type="text/javascript">
			<!--
      answer(<?=$blog->id?>, <?=JS_Obj($blog->login, $blog->attach)?>, '<?=get_login($uid)?>');
            document.getElementById('frm').olduser.value = '<?=$uid?>';
			<?/*document.getElementById('frm').msg_name.value = '<?=($error_flag)?input_ref_scr($msg_name):input_ref_scr($blog->title)?>';
			*/?>document.getElementById('frm').msg_name.value = '<?=($error_flag)?(stripslashes(html_entity_decode($msg_name,ENT_QUOTES))):input_ref_scr(html_entity_decode($blog->title,ENT_QUOTES))?>';
			<?/*document.getElementById('frm').msg.value = '<?=str_replace("\n", "\\n", ($error_flag)?input_ref(html_entity_decode($msg,ENT_QUOTES)):input_ref(html_entity_decode($blog->msgtext,ENT_QUOTES)))?>';
			*/?>document.getElementById('frm').msg.value = "<?=str_replace(array("\r","\n"), array('', '\n'), addslashes(($error_flag)?input_ref(addslashes(html_entity_decode($msg,ENT_QUOTES))):input_ref(html_entity_decode($blog->msgtext,ENT_QUOTES))))?>";
            if(document.getElementById('frm').yt_link != undefined) document.getElementById('frm').yt_link.value = '<?=($error_flag)?(stripslashes(html_entity_decode($yt_link,ENT_QUOTES))):input_ref_scr(html_entity_decode($blog->yt_link,ENT_QUOTES))?>';
			if (document.getElementById('frm').yt_link.value) document.getElementById('yt_link').style.display = '';
            document.getElementById('frm').btn.value = 'Сохранить';
			document.getElementById('frm').action.value = 'change';
			//-->
			</script>
		<? } ?>
		</td></tr>
		</table>
			
			
		
					<?php

					if ($LevelArray[$blog->id]) {
						for ($i=1;$i<=$LevelArray[$blog->id];$i++)
						{ print '</span>'; }
						$LevelCounter--;
					}
					if (!$LevelCounter)
					{
						unset($LevelArray);
						$LevelArray=array();
						$FirstClose=false;
						$LastClose=0;
					}
					/*
					if ($FirstClose && 	$blog->id==$LastClose)
					{
					for ($i=1;$i<=$LevelCounter;$i++)
					{ print '</span>'; }
					$LevelCounter=0;
					$FirstClose=false;
					$LastClose=0;
					}*/


				} else {

					// открытые треды

						?>
		<? if ($openlevel==$blog->id) {?><a name="o<?=$openlevel?>"></a><?}?>
		<? if($blog->new == 't' && !$iunread): $iunread=true?><a name="unread"></a><? endif; ?>
		<a name="c_<?=$blog->id?>"></a>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr valign="top" >
			<td style="width:50px !important;padding:5px 10px 5px <?=$padding?>px; <? if ($openlevel==$blog->id) {?> background-color: #fff7dd;<? }elseif($blog->new == 't'){?>background-color: #f0ffe2;<? }?> ">
			<?=view_avatar_info($blog->login, $blog->photo, 1)?>
			</td>
			<td class="bl_text" style="padding:5px 15px 5px 0px; <?if ($openlevel==$blog->id) {?>background-color: #fff7dd;<?}elseif($blog->new == 't'){?>background-color: #f0ffe2;<?}?>">
			<? if ($winner == $blog->id) { ?><a name="winner" id="winner"></a><? } ?>
      <? /* if ($blog->payed == 't') { ?><?=($blog->cnt_role == 'emp')?view_pro_emp():view_pro2(($blog->payed_test=="t")?true:false)?><? }*/?> <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color<?=$blog->cnt_role?>"><a href="/users/<?=$blog->login?>/?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>" class="b-layout__link b-layout__link_color<?=$blog->cnt_role?>" title="<?=($blog->uname." ".$blog->usurname)?>"><?=($blog->uname." ".$blog->usurname)?></a> <?seo_start();?>[<a href="/users/<?=$blog->login?>/?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>" class="b-layout__link b-layout__link_color<?=$blog->cnt_role?>" title="<?=$blog->login?>"><?=$blog->login?></a>]<?= seo_end();?></span>			<?
			/*!!!is_team!!!*/
			    $pro = ($blog->payed == 't'?($blog->role == 'emp'?view_pro_emp():view_pro2(($blog->payed_test=="t")?true:false)):""); 
                $is_team = view_team_fl();
                ?>
                <?=$blog->is_team=='t'?$is_team:$pro?><?= is_verify($blog->login) ? view_verify() : ''?> <?= ( $blog->completed_cnt > 0 ? view_sbr_shield() : '' );?> &#160;
   <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_71"><?=date("[d.m.Y | H:i]",strtotimeEx($blog->post_time))?></span>
			<? if ($blog->deleted) {
				if (isset($blog->thread) && is_array($blog->thread) && (count($blog->thread) > 0))
				{
					$buser_id = $blog->thread;
					$buser_id = array_pop($buser_id);
					$buser_id = $buser_id['fromuser_id'];
				}
				if ($blog->deluser_id == $blog->fromuser_id) { ?><br /><br />Комментарий удален автором <?=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted)); }
			     elseif ($blog->deluser_id == $buser_id) { ?><br /><br />Комментарий удален автором темы <?=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted));?>
			<?   } else { ?><br /><br />Комментарий удален модератором1 <? if (!$mod) { ?>( <? $del_user = $user->GetName($blog->deluser_id, $err); print($del_user['login'] . ' : ' . $del_user['usurname'] . ' ' . $del_user['uname']); ?> ) <? } ?><?=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted));?><?}?><br /><br />
			<? } else {
				if ($blog->modified) { ?> 
				<span class="b-layout__txt b-layout__txt_fontsize_11">
				<? if (!$blog->modified_id || $blog->modified_id == $blog->fromuser_id) { ?>[внесены изменения: <?=date("d.m.Y | H:i]",strtotimeEx($blog->modified)); }
    else {?>Отредактировано модератором <? if (!$mod) { ?>( <? $mod_user = $user->GetName($blog->modified_id, $err); print($mod_user['login'] . ' : ' . $mod_user['usurname'] . ' ' . $mod_user['uname']); ?> ) <? } ?> <?=date("[d.m.Y | H:i]",strtotimeEx($blog->modified));?><? }?><? }?>
    </span>
  			<br />
  			<? if ($winner == $blog->id) { ?><span class="b-layout__txt" color="#000099" style="font-size:20px">Победитель</span><? } ?>
  			<br />
  			<? if ($blog->title!=='') { ?><h1 class="bl_name">
  			<? if ($blog->login == "Anonymous"){
  				list($name, $mail) = sscanf($blog->title, "%s @@@: %s");
  				print $name." ".$mail;
  			} else {
                $sTitle = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->title) :*/ $blog->title;
                print reformat($sTitle,$maxw_ttl);} ?></h1><? } ?>
 <!--       <div class="blog-one-cnt" id="message<?=$blog->id?>" style="width:<?= $width_level."px;"?>">-->
        <div class="blog-one-cnt" id="message<?=$blog->id?>">
        <?php $sMessage = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->msgtext) :*/ $blog->msgtext; ?>
        <?=reformat($sMessage, $maxw_msg, 0, -($blog->is_chuck=='t'), 1)?></div><br />
  			<? if ($blog->attach){
  				print("<br>".$attach_html."<br />");

  			}
            if ($blog->yt_link)
            {
                print('<br clear="all" /><center>' . show_video($blog->id,$blog->yt_link).'</center>');
            }
            ?>
            <br />
  			<? if ($gr_base == 5 && !$winner && $parent_login == $_SESSION['login']) { ?><input type="submit" name="btn" value="Это победитель" onClick="if (warning(0)) window.location.replace('./view.php?tr=<?=$thread?>&winner=<?=$blog->id?>&ord=<?=$bOrd?>'); else return false;"><? } ?>
  			<div style="color: #D75A29;font-size:9px;<? if ($blog->attach && !$file) { ?> padding-left: <?=$padding+60?>px;<? } ?>">
  			<? if ($blog->login == $_SESSION['login'] || $allow_del || !$mod) {?>
  			<a href="<?=rawurlencode($_SERVER['PHP_SELF'])."?".htmlspecialchars($clearQueryStrOpen)?>&amp;id=<?=$blog->id?>&amp;action=delete&ord=<?=$bOrd?>&u_token_key=<?=$_SESSION['rand']?>" style="color: #D75A29;" onclick="return warning(1);">Удалить</a> |
  			<? } if ($blog->login == $_SESSION['login'] || (!$mod)) {?>
  			<a href="<?=rawurlencode($_SERVER['PHP_SELF'])."?".htmlspecialchars($clearQueryStrOpen)?>&amp;id=<?=$blog->id?>&amp;action=edit&pagefrom=<?=$bPageFrom?>&amp;tr=<?=$thread?>&ord=<?=$bOrd?>#efrm" style="color: #D75A29;">Редактировать</a> |
  			<? } ?>
  			<?seo_start();?>
			  <? if (!$closed_comments) { ?>
            <a <?if($_SESSION['login']){?> href="javascript: void(0);" onclick="javascript: answer(<?=$blog->id?>, '', '<?=get_login($uid)?>'); document.getElementById('frm').olduser.value = '<?=$uid?>';" <?}else{?>href="/fbd.php"<?}?> style="color: #D75A29">Комментировать</a> |
  			  <? } ?>

  			<a href="<?=rawurlencode($_SERVER['PHP_SELF'])."?".htmlspecialchars($clearQueryStrOpen)."&openlevel=".$blog->id.$ord_get_part."#o".$blog->id?>" style="color: #D75A29">Ссылка</a>
  			<?= seo_end();?>
  			</div>
  		<?php } ?>
  					</td>
		</tr>
		<tr <? if (!$blog->level || $LastLink==$blog->id) { ?> class="qpr" <? } ?>><td colspan="2" ><br /></td></tr>
		</table>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-bottom: 0px; padding-top: 0px; height: 0px;" >
		<tr style="padding-bottom: 0px; padding-top: 0px; height: 0px;" class="n_qpr"><td colspan="3" id="form<?=$blog->id?>">
		<? if ($blog->id == $edit_id && ($blog->login == $_SESSION['login'] || !$mod)) {?>
			<script language="JavaScript" type="text/javascript">
			<!--
      answer(<?=$blog->id?>,<?=JS_Obj($blog->login, $blog->attach)?>, '<?=get_login($uid)?>');
            document.getElementById('frm').olduser.value = '<?=$uid?>';
			<?/*document.getElementById('frm').msg_name.value = '<?=($error_flag)?input_ref_scr($msg_name):input_ref_scr($blog->title)?>';
			*/?>document.getElementById('frm').msg_name.value = '<?=($error_flag)?(stripslashes(html_entity_decode($msg_name,ENT_QUOTES))):input_ref_scr(html_entity_decode($blog->title,ENT_QUOTES))?>';
			<?/*document.getElementById('frm').msg.value = '<?=str_replace("\n", "\\n", ($error_flag)?input_ref(html_entity_decode($msg,ENT_QUOTES)):input_ref(html_entity_decode($blog->msgtext,ENT_QUOTES)))?>';
			*/?>document.getElementById('frm').msg.value = "<?=str_replace(array("\r","\n"), array('', '\n'), addslashes(($error_flag)?input_ref(addslashes(html_entity_decode($msg,ENT_QUOTES))):input_ref(html_entity_decode($blog->msgtext,ENT_QUOTES))))?>";
            if(document.getElementById('frm').yt_link != undefined) document.getElementById('frm').yt_link.value = '<?=($error_flag)?(stripslashes(html_entity_decode($yt_link,ENT_QUOTES))):input_ref_scr(html_entity_decode($blog->yt_link,ENT_QUOTES))?>';
			document.getElementById('frm').btn.value = 'Сохранить';
			document.getElementById('frm').action.value = 'change';
			//-->
			</script>
		<? } ?>
		</td></tr>
		</table>
	<?php

	if ($LevelArray[$blog->id]) {
		for ($i=1;$i<=$LevelArray[$blog->id];$i++)
		{ print '</span>'; }
		$LevelCounter--;
	}
	if (!$LevelCounter)
	{
		unset($LevelArray);
		$LevelArray=array();
		$FirstClose=false;
		$LastClose=0;
	}


		        } ?>
	
	<?php
			}
			else {

				// как обычно открытые
		?>
		<?if ($openlevel==$blog->id) {?><a name="o<?=$openlevel?>"></a><?}?>
		<?if($blog->new == 't' && !$iunread): $iunread=true; ?><a name="unread"></a><? endif; ?>
        <a name="c_<?=$blog->id?>"></a>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr valign="top" >
			<td style="width:50px !important;padding:5px 10px 5px <?=$padding?>px; <?if ($openlevel==$blog->id) {?> background-color: #fff7dd;<?}elseif($blog->new == 't'){?> background-color: #f0ffe2;<?}?>">
			<?=view_avatar_info($blog->login, $blog->photo, 1)?>
			</td>
			<td class="bl_text" style="padding:5px 15px 5px 0px; <?if ($openlevel==$blog->id) {?>background-color: #fff7dd;<?}elseif($blog->new == 't'){?>background-color: #f0ffe2;<?}?>">
			
      <? /* if ($blog->payed == 't') { ?><?=($blog->cnt_role == 'emp')?view_pro_emp():view_pro2(($blog->payed_test=="t")?true:false)?><? } */?> <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color<?=$blog->cnt_role?>"><a href="/users/<?=$blog->login?>/?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>" class="b-layout__link b-layout__link_color<?=$blog->cnt_role?>" title="<?=($blog->uname." ".$blog->usurname)?>"><?=($blog->uname." ".$blog->usurname)?></a> <?php seo_start();?>[<a href="/users/<?=$blog->login?>/?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>" class="b-layout__link b-layout__link_color<?=$blog->cnt_role?>" title="<?=$blog->login?>"><?=$blog->login?></a>]<?= seo_end();?></span>			<? if ($winner == $blog->id) { ?><a name="winner" id="winner"></a><? } ?>
			<?
			/*!!!is_team!!!*/
			    $pro = ($blog->payed == 't'?($blog->role == 'emp'?view_pro_emp():view_pro2(($blog->payed_test=="t")?true:false)):""); 
                $is_team = view_team_fl();
                ?>
                <?=$blog->is_team=='t'?$is_team:$pro?><?= is_verify($blog->login) ? view_verify() : ''?> <?= ( $blog->completed_cnt > 0 ? view_sbr_shield() : '' );?> &#160;
<span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_71"><?=date("[d.m.Y | H:i]",strtotimeEx($blog->post_time))?></span>

			<? $del_allow = $gr_base == 101 ? ($blog->login == $_SESSION['login'] || hasPermissions('blogs')) : $allow_del; ?>
            <? if ($blog->deleted) { ?>
			    <? if (isset($blog->thread) && is_array($blog->thread) && (count($blog->thread) > 0))
			    {
			    	$buser_id = $blog->thread;
			    	$buser_id = array_pop($buser_id);
			    	$buser_id = $buser_id['fromuser_id'];
			    }
			    if ($blog->deluser_id == $blog->fromuser_id) { if (!hasPermissions('blogs')) echo "<br /><br />"; ?>Комментарий удален автором <?=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted)); }
			     elseif ($blog->deluser_id == $buser_id) { if (!hasPermissions('blogs')) echo "<br /><br />"; ?>Комментарий удален автором темы <?=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted));?>
			<?   } else { if (!hasPermissions('blogs')) echo "<br /><br />"; ?>Комментарий удален модератором <? if (!$mod) { ?>( <? $del_user = $user->GetName($blog->deluser_id, $err); print($del_user['login'] . ' : ' . $del_user['usurname'] . ' ' . $del_user['uname']); ?> ) <? } ?> <?=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted));?><?php if($blog->deleted_reason) { ?><br/>Причина: <br/><?=$blog->deleted_reason?><?php } ?> <?}?>
			<? } if (!$blog->deleted || hasPermissions('blogs') || $del_allow) {
				if ($blog->deleted && hasPermissions('blogs')) { ?> <span class="b-layout__txt b-layout__txt_fontsize_11" style=" color:#ccc"> <? }
				if ($blog->modified) { ?> 
				<span class="b-layout__txt b-layout__txt_fontsize_11">
				   <? if (!$blog->modified_id || $blog->modified_id == $blog->fromuser_id) { ?>[внесены изменения: <?=date("d.m.Y | H:i]",strtotimeEx($blog->modified)); }
      			 else {?>Отредактировано модератором <? if (!$mod) { ?>
            ( <? $mod_user = $user->GetName($blog->modified_id, $err); print($mod_user['login'] . ' : ' . $mod_user['usurname'] . ' ' . $mod_user['uname']); ?> ) 
												<? } ?> 
												<?=date("[d.m.Y | H:i]",strtotimeEx($blog->modified));?>
												<? }?>
												<? }?>
     </span>
  			<? if ($blog->is_banned  && hasPermissions('blogs')) {?>
  			<span style="color:#000" ><b>Пользователь забанен.</b></span>
  			<? }?>
  			<br />
  			<? if ($winner == $blog->id) { ?><span color="#000099" style="font-size:20px">Победитель</span><? } ?>
  			<br />
  			<? if ($blog->is_banned  && !hasPermissions('blogs')) {
  			    ?>
  			    Ответ от заблокированного пользователя
  			    <?
  			}
  			else if ( $blog->deluser_id == $_SESSION['uid'] || $blog->deluser_id == $_SESSION['uid'] || hasPermissions('blogs') || !$blog->deleted ) {
  			if ($blog->title!=='') { ?><span class="bl_name" <?=($blog->deleted && hasPermissions('blogs'))?"style='color:#CCCCCC'":""?>>

  			<?
  			if ($blog->login == "Anonymous"){
  				list($name, $mail) = sscanf($blog->title, "%s @@@: %s");
  				print $name." ".$mail;
  			} else {
                $sTitle = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->title) :*/ $blog->title;
                print reformat($sTitle,$maxw_ttl,0,1);} ?></span><br /><? } ?>
        <?php $sMessage = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->msgtext) :*/ $blog->msgtext; ?>
        
<!--        <div class="blog-one-cnt" id="message<?=$blog->id?>" style="width:<?= $width_level."px;"?><?=($blog->deleted && hasPermissions('blogs'))?"color:#CCCCCC":""?>">-->
        <div class="blog-one-cnt" id="message<?=$blog->id?>" style=" <?=($blog->deleted && hasPermissions('blogs'))?"color:#CCCCCC":""?>">
		
		<?=reformat($sMessage, $maxw_msg, 0, -($blog->is_chuck=='t'), 1)?></div><br />
  			<? if ($blog->attach){
  			    seo_start();
  				print("<br />".$attach_html."<br />");
                print seo_end();
  			}
            if ($blog->yt_link)
            {
                print('<br clear="all" /><center>' . show_video($blog->id,$blog->yt_link) . '</center><br />');
            }
            ?>

            <div id="warnreason-<?=$blog->id?>" style="display: none">&nbsp;</div>
  			
  			<? if ($blog->deleted && hasPermissions('blogs')) { ?> </span> <? } ?>
  				<br />
  			<? if ($gr_base == 5 && !$winner && $parent_login == $_SESSION['login']) { ?><input type="submit" name="btn" value="Это победитель" onClick="if (warning(0)) window.location.replace('./view.php?tr=<?=$thread?>&winner=<?=$blog->id?>&ord=<?=$bOrd?>'); else return false;"><? } ?>
  			<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr valign="middle"><td align="left"  width="100%"  style="color: #D75A29; font-size:9px; white-space:nowrap">

  			<?
  			//  || $parent_login == $_SESSION['login']
  			if ((!$blog->deleted && !$blog->is_banned) && !$blog->is_blocked){ //|| hasPermissions('blogs')
        if (($blog->login == $_SESSION['login'] || $del_allow || !$mod) && (!$post_only || !$mod)) { ?>
          <a href="<?=($gr_base == 101 ? $form_uri.'&' : '?')?>id=<?=$blog->id?>&amp;action=delete&ord=<?=$bOrd?>&u_token_key=<?=$_SESSION['rand']?>" style="color: #D75A29;" onclick="return warning(1);">Удалить</a> | 
        <? } if (($blog->login == $_SESSION['login'] || !$mod || hasPermissions('blogs')) && (!$post_only || !$mod)) {?>
        <a href="<?=($gr_base == 101 ? $form_uri.'&' : '?')?>id=<?=$blog->id?>&amp;action=edit&pagefrom=<?=$bPageFrom?>&amp;tr=<?=$thread?>&ord=<?=$bOrd?>" style="color: #D75A29;">Редактировать</a> |
  			<? } ?>
  			<? seo_start();?>
  			<? if ($form_uri != "/payed/") { ?>
  			  <?if (!$closed_comments && !$blog->is_blocked){ ?>
                            <a <?if ($_SESSION["login"]){?> href="javascript: void(0);" onclick="javascript:answer(<?=$blog->id?>,'', '<?=get_login($uid)?>'); document.getElementById('frm').olduser.value = '<?=$uid?>'; document.getElementById('frm').scrollIntoView(true);" <?}else{?>href="/fbd.php"<?}?> style="color: #D75A29">Комментировать</a> |
  			  <? } ?>
  			<? } ?>
  			<a href="<?=($gr_base == 101 ? $form_uri.'&' : '?')."openlevel=".$blog->id.$ord_get_part."#o".$blog->id?>" style="color: #D75A29">Ссылка</a>
  			<?= seo_end();?>
  			</td><?
			} else if ($blog->deleted) {	 ?>
                <a href="<?=($gr_base == 101 ? $form_uri.'&' : '?')?>id=<?=$blog->id?>&amp;action=restore&ord=<?=$bOrd?>" style="color: #D75A29;" onclick="return warning(1);">Вернуть</a> |
                <a href="<?=($gr_base == 101 ? $form_uri.'&' : '?')."openlevel=".$blog->id.$ord_get_part."#o".$blog->id?>" style="color: #D75A29">Ссылка</a>
            <? } ?>
  			<td style="white-space:nowrap">
  			<? if (hasPermissions('blogs') && $blog->login!=$_SESSION["login"] && $blog->login['login']!="admin") {
  			    ?>
  			    <script type="text/javascript">
                banned.addContext( 'blog_msg_<?=$blog->id?>', 2, '<?="{$GLOBALS['host']}/blogs/view.php?".htmlspecialchars($clearQueryStrOpen)."&openlevel=".$blog->id.$ord_get_part."#o".$blog->id?>', "<?=($blog->title!=='' ? $blog->title : '<без темы>')?>" );
                </script>
  			    <?php
            if(hasPermissions('users')) {
  				if ( $blog->warn<3 && !$blog->is_banned && !$blog->ban_where ) {
  					?><span class="warnlink-<?=$blog->fromuser_id?>"><a style="color: #D75A29; font-size:9px;" href="javascript: void(0);" onclick="banned.warnUser(<?=$blog->fromuser_id?>, 0, 'blogs', 'blog_msg_<?=$blog->id?>', 0); return false;">Предупредить (<span class="warncount-<?=$blog->fromuser_id?>"><?=($blog->warn ? $blog->warn : 0)?></span>)</a></span><?
  				}
  				else {
  				    $sBanTitle = (!$blog->is_banned && !$blog->ban_where) ? 'Забанить!' : 'Разбанить';
  					?><span class="warnlink-<?=$blog->fromuser_id?>"><a href="javascript:void(0);" onclick="banned.userBan(<?=$blog->fromuser_id?>, 'blog_msg_<?=$blog->id?>',0)" style="color: Red;font-size:9px;"><?=$sBanTitle?></a></span><?
  				}
        }
  			}?>
            </td></tr></table>
  		<? 	} }?></td></tr>
		<tr <? if ((!$blog->level || $LastLink==$blog->id)) { ?> class="qpr" <? } ?>><td colspan="2" ><br /></td></tr>
		</table>
		<table width="100%"  cellspacing="0" cellpadding="0"  style="padding-bottom: 0px; padding-top: 0px; height: 0px; border:0" >
		<tr   style="padding-bottom: 0px; padding-top: 0px; height: 0px;"  class="n_qpr"><td colspan="3" id="form<?=$blog->id?>">
		<? if ($blog->id == $edit_id && ($blog->login == $_SESSION['login'] || !$mod)) {?>
            <script type="text/javascript">
			<!--
      answer(<?=$blog->id?>,<?=JS_Obj($blog->login, $blog->attach)?>, '<?=get_login($uid)?>',2, <?=count($blog->attach)?>);
            document.getElementById('frm').olduser.value = '<?=$uid?>';
            document.getElementById('frm').msg_name.value = '<?=($error_flag)?(stripslashes(html_entity_decode($msg_name,ENT_QUOTES))):input_ref_scr(html_entity_decode($blog->title,ENT_QUOTES))?>';
			<?/*document.getElementById('frm').msg_name.value = '<?=($error_flag)?input_ref_scr($msg_name):input_ref_scr($blog->title)?>';
			*/?>document.getElementById('frm').msg.value = "<?=str_replace(array("\r","\n"), array('\n', '\n'), ($error_flag)?html_entity_decode($msg,ENT_QUOTES):addslashes(html_entity_decode($blog->msgtext,ENT_QUOTES)))?>";
            if(document.getElementById('frm').yt_link != undefined) document.getElementById('frm').yt_link.value = '<?=($error_flag)?(stripslashes(html_entity_decode($yt_link,ENT_QUOTES))):input_ref_scr(html_entity_decode($blog->yt_link,ENT_QUOTES))?>';
			document.getElementById('frm').btn.value = 'Сохранить';
			document.getElementById('frm').action.value = 'change';
			//-->
			</script>
		<? } ?>
		</td></tr>
		</table>
				<? }	//килец	?>
				
<br />

		<? } ?>
	</td></tr>
	
	<? } ?>
</table>
					<table width="100%" border="0" cellpadding="10" cellspacing="0"><tr valign="middle"><td align="left"><?
					// постраничка

					if ($AllRootRecord>$MaxOnPage) {
						$counter=1;
						for ($i=0;$i<$AllRootRecord;$i+=$MaxOnPage) {
							if ($i==$PageFrom) {
								print '<b>['.$counter.']</b> ';

							}
							else {
								print '<a  style="color: #666;" href="'.rawurlencode($_SERVER['PHP_SELF'])."?".htmlspecialchars($clearQueryStr).'&pagefrom='.$i.'">['.$counter.']</a> ';
							}
							$counter++;
						}


					}

					if (!$OpenAllLevels) {
						print "&nbsp;&nbsp;<a  style='color: #666;' href='".rawurlencode($_SERVER['PHP_SELF'])."?".htmlspecialchars($clearQueryStr).'&openalllevels=1&wopages=1'."'>Развернуть все</a>";
					}
					else {
						$qstr=eregi_replace("&openalllevels=1&wopages=1","",$clearQueryStr);
						// print "&nbsp;&nbsp;<a  style='color: #666;' href='".$_SERVER['PHP_SELF']."?".htmlspecialchars($qstr).''."'>Показать свернутым</a>";
					}
				?></td><td style="text-align:right; padding-right:19px;">
				<? seo_start(); ?>
				<a href="#top" style='color: #666;'  >Наверх</a>
				<?= seo_end();?>
				</td></tr></table>
				<br />
	</td>
</tr>
</table>


<script type="text/javascript">

<? if ($error_flag && $action != "edit") { ?>
//answer(<?=$reply?>, <?=JS_Obj($blog->login, $blog->attach)?>, '');
answer(<?=$reply?>, null, '');
document.getElementById('frm').msg_name.value = "<?=stripslashes(input_ref_scr($msg_name))?>";
document.getElementById('frm').msg.value = "<?=input_ref_scr($msg)?>";
if(document.getElementById('frm').yt_link != undefined) document.getElementById('frm').yt_link.value = "<?=preg_replace("/\//",'\/',stripslashes(input_ref_scr($yt_link)))?>";
<? if ($yt_link) { ?>
yt_link = true;
if(document.getElementById('frm').yt_link != undefined) $('yt_link').style.display = 'block';
<? } else { ?>
yt_link = false;
if(document.getElementById('frm').yt_link != undefined) $('yt_link').style.display = 'none';
<? } ?>
goToAncor('efrm');
errmsg1 = errmsg2 = '';
<? } ?>

<? if (empty($no_poll)) { ?>
poll.init('Blogs', 'editForm', <?=blogs::MAX_POLL_ANSWERS?>, '<?=$_SESSION['rand']?>');
<? } ?>

<? if ((isset($alert) && (is_array($alert)) && ($alert[4] || $alert[3])) || $action=='edit') { ?>goToAncor('efrm');<? } ?>

<? if ($uid) { ?>
function InitHideFav()
{
	HideFavFloat(0,0);
	HideFavOrderFloat(currentOrderStr);
}

document.body.onclick = InitHideFav;

<? } ?>

<? if ($openlevel) { ?>goToAncor('o<?=$openlevel?>');<? } ?>

<? if($use_draft) { ?>
window.addEvent('domready', function(){	 
    DraftInit(3);	 
});	 
<? } ?>
</script>

<?php
if ( hasPermissions('blogs') ) {
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/del_overlay.php' );
}
?>
