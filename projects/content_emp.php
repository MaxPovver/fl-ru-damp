<? 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects_ci.common.php");
$xajax->printJavascript('/xajax/');
require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");

// Подгружаем данные по мнения создалю проекта
$op_data = opinions::getCounts($project['user_id'], array('frl', 'norisk', 'all', 'total'));
$project_exRates = project_exrates::GetAll();
$exch = array(1 => 'FM' , 'USD' , 'Euro' , 'Руб');
$translate_exRates = array(0 => 2 , 1 => 3 , 2 => 4 , 3 => 1);
$project['exec_po_id'] = 0;
if (isset($offers) && is_array($offers)) {

    //количество загруженных в данный момент проектов на странице
    $_SESSION['offers_on_page'] = sizeof($offers); 

    //не позволяем перегрузиться странице, после перемещения всех предложений, если нахоимся на единственной странице, для этого просто добавим 1 к количеству предложений
    if (ceil($num_offers / MAX_OFFERS_AT_PAGE) == 1)
        $_SESSION['offers_on_page'] = sizeof($offers) + 1; 

    foreach ($offers as $key => $value) {
        if ($value['user_id'] == $project['exec_id']) {
            $project['exec_po_id'] = $value['id'];
        }
    }
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/HTML/projects_lenta.php");
require_once (ABS_PATH . "/classes/op_codes.php");
$op_codes = new op_codes();
$pProjCost = ($project['kind'] == 2) ? $op_codes->GetField(9, $error, "sum") : $op_codes->GetField(8, $error, "sum");
$user_obj = new users();

$answer_button_href = (get_uid()?(is_emp($_SESSION['role'])?"/public/?step=1&kind=".$project['kind']."&red=" : "#new_offer" ) : "/login/");
$answer_button_text = get_uid()&&is_emp($_SESSION['role'])
        ? $project['kind']==7 
            ? "Разместить аналогичный конкурс"
            : $project['kind']==4 
                ? "Опубликовать аналогичную вакансию" 
                : "Опубликовать аналогичный проект"
        : "Ответить на проект";
?>
<script type="text/javascript">
<!--
var in_office = '<?= (int)($project['kind'] == 4)?>';
var old_num = 0;
var inner = false;
var dialogue_count = new Array(<?=count($offers)?>);

<?
if (isset($offers) && is_array($offers)) {
    foreach ($offers as $key => $value) {
        ?>
dialogue_count[<?=$value['id']?>] = <?=count($value['dialogue'])?>;
<?
        if ($value['user_id'] == $project['exec_id']) {
            $project['exec_po_id'] = $value['id'];
        }
    }
}
?>

function GetForm(num, commentid){
	if (!commentid) {commentid=0}

	out = "<form action=\"javascript:void(null);\" method=\"post\" name=\"amfrm\" id=\"amfrm\" onKeyPress=\"if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {submitDialogueForm()}\" onsubmit=\"submitDialogueForm()\">\
<input type=\"hidden\" id=\"action\" name=\"action\" value=\"answer\">\
<input type=\"hidden\" id=\"from_\" name=\"from\" value=\"emp\">\
<input type=\"hidden\" id=\"po_id\" name=\"po_id\" value=\"" + num + "\">\
<input type=\"hidden\" id=\"po_commentid\" name=\"po_commentid\" value=\"" + commentid + "\">\
<input type=\"hidden\" id=\"prj_id\" name=\"prj_id\" value=\"<?=$prj_id?>\">\
<table width=\"96%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\
<tr>\
	<td colspan=\"2\" style=\"padding-bottom:4px;\">Сообщение:</td>\
<\/tr>\
<tr>\
	<td colspan=\"2\" style=\"padding-bottom:4px;\"><div class=\"b-textarea\"><textarea placeholder=\"Ваш ответ будет виден только автору предложения\" class=\"b-textarea__textarea\" tabindex=\"1\" id=\"po_text\" name=\"po_text\" rows=\"4\" onkeydown=\"document.getElementById('po_text_msg').innerHTML = '';\"></textarea></div><div id=\"po_text_msg\"></div>";
   // var is_pro = "<div class=\"b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padtop_5 b-layout__txt_padbot_5\" id=\"confirm_messages_project\"><span class=\"b-icon b-icon_sbr_forb\"></span>Обмен контактами запрещен. Чтобы оставить свои контакты, <a class=\"b-layout__link\" href=\"/payed/\">купите</a> <span class=\"b-icon b-icon__pro b-icon__pro_e\"></span></div>";
   // if (offersPROFlags[num] == 1) {
  //      is_pro = "";
  //  } 
<?php /* if(is_pro()) { ?>    
   is_pro = "<div class=\"b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padtop_5 b-layout__txt_padbot_5\" id=\"confirm_messages_project\"><span class=\"b-icon b-icon_sbr_allow\"></span>Вы можете оставлять свои контакты, так как являетесь владельцем аккаунта <span class=\"b-icon b-icon__pro b-icon__pro_e\"></span></div>";
<?php } */ ?>
    
  //  out += is_pro;
    out += "</td>\
<\/tr>\
<tr>\
	<td colspan=\"2\" ><div class=\"b-buttons\"><button class=\"b-button b-button_flat b-button_flat_green\" type=\"submit\" name=\"savebtn\" id=\"savebtn\" tabindex=\"2\">Публиковать</button>&#160;&#160;&#160;<a class=\"b-buttons__link\" id=\"resetbtn\" onclick=\"resetfld('"+num+"');\" tabindex=\"3\" name=\"resetbtn\">Отменить</a></div></td>\
<\/tr>\
<tr>\
	<td colspan=\"2\" id=\"po_id_error_" + num + "\"></td>\
<\/tr>\
<\/table>\
<\/form>";
	return(out);
}

function markRead(num) {
  var nmbx=$('new_msgs_' + num);
	if(nmbx) {
	  nmbx.innerHTML = '';
  	xajax_ReadOfferDialogue(num, <?=$project['id']?>, '<?=$po_type?>');
	}
}

function answer(num, commentid) {
	setInterval("check_com_text()", 10);
	$('po_dialogue_talk_' + num).style.display = 'block';
	$('po_comments_' + num).className = 'po_comments b-layout b-layout_margbot_20';
	td = $('po_dialogue_answer_' + num);
	if ((old_num > 0) && (old_num != num)) {
		resetfld(old_num)
	}
	td.innerHTML = GetForm(num, commentid);
    
    if(in_office == 1) {
        if($('confirm_messages_project')) $('confirm_messages_project').dispose();
    }
	old_num = num;
	if (commentid) {
		$('po_text').value = $('po_comment_original_' + commentid).innerHTML.replace(/&amp;/gi, '&').replace(/<.*?br.*?>/gi, '\n')
		                     .replace(/&nbsp;/gi, ' ').replace(/&gt;/gi, '>').replace(/&lt;/gi, '<');
		$('savebtn').value = "Сохранить";
	}
	$('po_text').focus();
}

var edit_block = new Array();
var last_commentid = 0;

function resetfld(num){
	if ($('po_dialogue_talk_' + num)) {
    	$('po_dialogue_talk_' + num).style.display = 'none';
    	$('po_comments_' + num).className = 'po_comments_hide b-layout b-layout_margbot_20';
    	td1 = $('po_dialogue_answer_' + num);
    	innerHTML = '';
    	if (dialogue_count[num] > 1) {
            innerHTML = '<span style="float: right;"><a href="javascript:void(null)" onClick="dialogue_toggle(' + num + ');markRead(' + num + ');" class="internal" id="toggle_dialogue_' + num + '">Развернуть всю переписку</a> ' + dialogue_count[num] + '</span>';
    	}
    	innerHTML = innerHTML + '<span><a href="javascript:void(0);" onClick="answer(' + num + ');markRead(' + num + ');" class="internal">Написать ответ</a></span>';
	if (edit_block[num]) innerHTML = innerHTML + edit_block[num];

    	td1.innerHTML = innerHTML;
    }
}

function submitDialogueForm()
{
	$('savebtn').disabled = true;
	xajax_AddDialogueMessage(xajax.getFormValues("amfrm"));
	return false;
}

function show_fpopup(img,num)
{
	$(img).blur();
	$(num).style.display = 'block';
}

function hide_fpopup(num)
{
	if (!inner)
	{
		e = $(num);
		if (e && e.style) {
		    e.style.display = 'none';
		}
	}
}

function mouseout(num)
{
	setTimeout("hide_fpopup('"+num+"')", 500);
}

function toggle_link_text(num)
{
	el_top = $('toggle_dialogue_' + num);
	el_div = $('po_comments_'+num);
	if (el_top.innerHTML == 'Свернуть переписку')
	{
		el_top.innerHTML = 'Развернуть всю переписку';
		if(el_div)
		  el_div.className = 'po_comments_hide b-layout b-layout_margbot_20';
	}
	else
	{
		el_top.innerHTML = 'Свернуть переписку';
		if(el_div)
  		el_div.className = 'po_comments b-layout b-layout_margbot_20';
	}
}

function dialogue_toggle(num) {
	el_top = $('toggle_dialogue_' + num);
	el_div = $('po_comments_' + num);
	el_tlk = $('po_dialogue_talk_' + num);
	if(el_div) {
  	if (el_top.innerHTML == 'Свернуть диалог')
  		el_div.className = 'po_comments b-layout b-layout_margbot_20';
  	else
  		el_div.className = 'po_comments_hide b-layout b-layout_margbot_20';
	}
	if (el_tlk.style.display == 'none')
	{
		el_tlk.style.display = 'block';
	}
	else
	{
		el_tlk.style.display = 'none';
	}
	toggle_link_text(num);
}

<? if($uid == $project['user_id']) { ?>
function addToFav(lg) {
    new Request.XAJAX({ call: 'team.AddInTeamNew' }).post(lg);
}
function delFromFav(lg) {
    new Request.XAJAX({ call: 'team.DelInTeamNew' }).post(lg);
}
function addNoteForm(el, lg) {
    el = $(el);
    cancelNote();
    f = document.getElement('div.uprj-note.form-templ');
    f.getElement('textarea').set('value', '');
    f.inject($('team_'+lg), 'after');
    f.store('login', lg);
    f.store('action', 'add');
    f.setStyle('display', 'block');
}
function editNoteForm(el, lg) {
    el = $(el);
    cancelNote();
    f = document.getElement('div.uprj-note.form-templ');
    f.getElements('textarea').set('value', '');

    f.inject($('team_'+lg), 'after');
    f.store('login', lg);
    f.store('action', 'update');
    f.setStyle('display', 'block');

    $('note_' + lg).setStyle('display', 'none');
    f.getElements('input,textarea').set('disabled', true);

    new Request.XAJAX({ call: 'notes.GetNote' }).post(lg);
//    f.getElement('textarea').set('value', $('note_' + lg).getElement('.uprj-note-cnt>div').childNodes[0].nodeValue);
}

function checknote(el) {
    var maxLen = 200;
    el = $(el);
    var message = el.get('value');
    var k = message.split("\n");
    var d = k.length*4;
    if ((message.length+d) > maxLen)
    {
        alert('Максимальный размер заметки 200 символов!');
        message = message.substring(0, maxLen-d);
        el.set('value', message);
    }
}

function updateNote(el) {
    el = $(el);
    el.set('disabled', true);
    f = el.getParent('div.form-templ');
    lg = f.retrieve('login');
    action = f.retrieve('action');
    rating = 0;
    n_txt = f.getElement('textarea').get('value');
    
    if ( n_txt.length > 200 ) {
        alert('Максимальный размер заметки 200 символов!');
        el.set('disabled', false);
        return false;
    }
    
    new Request.XAJAX({ call: 'notes.EditNote' }).post(lg, action, n_txt, rating);
}
function deleteNote(lg) {
    new Request.XAJAX({ call: 'notes.EditNote' }).post(lg, 'update', '');
}
function cancelNote(el) {
//    el = $(el);
//    if(!el) {
//        el = document.getElement('div.uprj-note.form-templ input.i-btn-cancel');
//    }
//    f = el.getParent('div.uprj-note.form-templ');
    f = document.getElement('div.uprj-note.form-templ');
    f.getElements('input,textarea').set('disabled', false);
    
    if(f.retrieve('action') == 'update') {
        if($('note_' + f.retrieve('login'))) $('note_' + f.retrieve('login')).setStyle('display', 'block');
    }
    f.store('action', false);
    f.setStyle('display', 'none');
}
<? } ?>

function removeNoteBar(lg) {
    if($('team_' + lg)) $('team_' + lg).dispose();
    if($('note_' + lg)) $('note_' + lg).dispose();
    if(document.getElement('div.uprj-note.form-templ'))
        document.getElement('div.uprj-note.form-templ').setStyle('display', 'none');
}


//-->
</script>

		    <?php /* if($project['login'] != $_SESSION["login"] && $project['closed'] != "t") { ?><a class="b-button b-button_flat b-button_flat_green b-button_float_right" href="<?=$answer_button_href?>"><?=$answer_button_text?></a><?php }//if */ ?>
            <?php if ($project['ico_closed'] == "t")  $sBox1 .= "<img src=\"/images/ico_closed.gif\" alt=\"Проект закрыт\" style='vertical-align: middle;margin: 0px 8px 4px 0px;'/>"; ?>
			<?php if (!($project['pro_only'] == 't' || $project['verify_only'] == 't')): ?>
               <?php include(dirname(__FILE__).'/only_pro_verify.inc.php') ?>
            <?php endif; ?>
            <h1 class="b-page__title b-page__title_ellipsis" id="prj_name_<?=$project['id']?>"><?=$sBox1?><?=reformat($sTitle,30,0,1); ?></h1>
            <?php if ($project['pro_only'] == 't' || $project['verify_only'] == 't'): ?>
               <?php include(dirname(__FILE__).'/only_pro_verify.inc.php') ?>
            <?php endif; ?>


		<? // если проект платный или создатель проекта ПРО, то банер не показываем
        if(!((isset($project) && isset($project['payed']) && $project['payed'] > 0) || ($project['is_pro'] === 't'))) { ?>
      <div class="b-layout b-layout_float_right b-layout_width_240 b-layout_margleft_30 b-page__desktop">
          <?= printBanner240(false) ?>
      </div>
      <style type="text/css">@media screen and (max-width: 1250px){#po_selector .b-pic{ display:block; margin:0 auto;}} </style>
        <? } ?>

      
      <div class="b-layout <? if(!((isset($project) && isset($project['payed']) && $project['payed'] > 0) || ($project['is_pro'] === 't'))) { ?>b-layout_margright_270<? } ?> b-layout_marg_null_ipad">
            <?php include "tpl.prj-main.php";?>

	<? if (($project['is_blocked'] != 't' || hasPermissions('projects')) && !($project['pro_only'] == 't' && !$is_pro && $project['user_id']!=$_SESSION['uid'] && !hasPermissions('projects'))) { ?>
        <a name="offers"></a>
    

<? if ($op_count_all > 0) { ?>
        
<?php if (isset($projectObject) && $projectObject->isOwner($uid)): ?>        
    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_16 b-layout__txt_center b-layout__txt_padbot_40">    
        Фрилансеры не видят предложения друг друга. 
        Все предложения видны только вам, как и переписка по ним!
    </div>        
<?php endif; ?>
        
		<? if ($project['login'] == $_SESSION["login"]) { ?>
               <div id="po_selector">
                    <table class="b-layout__table b-layout__table_margbot_20 b-layout__table_width_full">
                        <tr class="b-layout__tr">
                            <td id="po_offers" class="b-layout__td b-layout__td_padright_35 b-layout__td_center">
                                <img class="b-pic b-pic_margright_10 b-pic_valign_mid" src="/images/ico_po_offers.gif" />
                                <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_valign_middle">
                                    <? if ($po_type == 'o') { ?>
                                          <span class="b-layout__txt">Не определился</span> 
                                          <? } else { ?>
                                          <a  class="b-layout__link b-layout__link_bold" href="?pid=<?=$prj_id?>&amp;type=o&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Не определился</a> 
                                          <? } ?>
                                          <span id="po_offers_count" class="b-layout__txt b-layout__txt_color_808080 b-layout__txt_bold"><?=$op_count_offers?></span>
												<? if ($op_count_offers_new_msgs > 0) { ?>
                                         <div id="op_count_offers_new_msgs" class="b-layout__txt b-layout__txt_fontsize_11">
                                             <img class="b-pic b-pic_valign_mid" src="/images/ico_mail_2.gif" alt="" width="10"height="8" border="0">
                                             <?=$op_count_offers_new_msgs?> <?=ending($op_count_offers_new_msgs, 'новое сообщение', 'новых сообщения', 'новых сообщений')?>
                                         </div>
                                    <? }?>
                                </div>
                            </td>
                            <td id="po_refuse" class="b-layout__td b-layout__td_padright_35 b-layout__td_center">
                                <img class="b-pic b-pic_margright_10 b-pic_valign_mid" src="/images/ico_po_refuse.gif" />
                                <div class="b-layout__txt b-layout__txt_inline-block">
                                    <? if ($po_type == 'r') { ?>
                                               <span class="b-layout__txt">Отказал</span>
                                             <? } else {?>
                                               <a class="b-layout__link b-layout__link_bold" href="?pid=<?=$prj_id?>&amp;type=r&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Отказал</a>
                                             <? } ?>
                                               <span id="po_refuse_count" class="b-layout__txt b-layout__txt_color_808080 b-layout__txt_bold"><?=$op_count_refuse?></span>
                                </div>
                                <div id="op_count_refuse_new_msgs" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block">
									<? if ($op_count_refuse_new_msgs > 0) { ?>
                                        <img class="b-pic b-pic_valign_mid" src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0">
                                        <?=$op_count_refuse_new_msgs?> <?=ending($op_count_refuse_new_msgs, 'новое сообщение', 'новых сообщения', 'новых сообщений')?>
                                    <? }?>
                                </div>
                            </td>
                            <td id="po_candidate" class="b-layout__td b-layout__td_padright_35 b-layout__td_center">
                                <img class="b-pic b-pic_margright_10 b-pic_valign_mid" src="/images/ico_po_candidate.gif" />
                                <div class="b-layout__txt b-layout__txt_inline-block">
                                    <? if ($po_type == 'c') { ?>
                                                 <span class="b-layout__txt">Кандидаты</span>
                                             <? } else { ?>
                                                 <a class="b-layout__link b-layout__link_bold" href="?pid=<?=$prj_id?>&amp;type=c&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Кандидаты</a>
                                             <? }?>
                                                 <span id="po_candidate_count" class="b-layout__txt b-layout__txt_color_808080 b-layout__txt_bold"><?=$op_count_candidate?></span>
                                </div>
                                <div id="op_count_candidate_new_msgs" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block">
                                    <? if ($op_count_candidate_new_msgs > 0) { ?>
                                    <img class="b-pic b-pic_valign_mid" src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0">
                                    <?=$op_count_candidate_new_msgs?> <?=ending($op_count_candidate_new_msgs, 'новое сообщение', 'новых сообщения', 'новых сообщений')?><? } ?>
                                </div>
                            </td>
                            <td id="po_executor" class="b-layout__td b-layout__td_padright_35 b-layout__td_center">
                                <img class="b-pic b-pic_margright_10 b-pic_valign_mid" src="/images/ico_po_executor.gif" />
                                <div class="b-layout__txt b-layout__txt_inline-block">
                                 <? if ($po_type == 'i') {?>
                                             <span class="b-layout__txt">Исполнитель</span>
                                          <? } else {?>
                                             <a class="b-layout__link b-layout__link_bold" href="?pid=<?=$prj_id?>&amp;type=i&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Исполнитель</a>
                                          <? } ?>
                                             <span id="po_executor_count" class="b-layout__txt b-layout__txt_color_808080 b-layout__txt_bold"><?=$op_count_executor?></span>
                                </div>
                                <div id="op_count_executor_new_msgs" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block">
                                     <? if ($op_count_executor_new_msgs > 0) { ?>
                                       <img class="b-pic b-pic_valign_mid" src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0">
                                       <?=$op_count_executor_new_msgs?> <?=ending($op_count_executor_new_msgs, 'новое сообщение', 'новых сообщения', 'новых сообщений')?>
                                     <? }?>
                               </div>
                          </td>
                         <td id="po_refuse" class="b-layout__td b-layout__td_center">
                                <img class="b-pic b-pic_margright_10 b-pic_valign_mid" src="/images/ico_po_canceled.gif" >
                                <div class="b-layout__txt b-layout__txt_inline-block">
									<? if ($po_type == 'fr') {?>
                                        <span class="b-layout__txt">Отказались</span>
                                    <? } else {?>
                                        <a class="b-layout__link b-layout__link_bold"  href="?pid=<?=$prj_id?>&amp;type=fr&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Отказались</a>
                                    <? } ?>
                                    <span id="po_refuse_count" class="b-layout__txt b-layout__txt_color_808080 b-layout__txt_bold"><?=$op_count_frl_refuse?></span>
                                </div>
                                <div id="op_count_frl_refuse_new_msgs" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block">
								   <? if ($op_count_frl_refuse_new_msgs > 0) {?>
                                        <img class="b-pic b-pic_valign_mid" src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0">
                                        <?=$op_count_frl_refuse_new_msgs?> <?=ending($op_count_frl_refuse_new_msgs, 'новое сообщение', 'новых сообщения', 'новых сообщений')?>
								   <? } ?>
                               </div>
                         </td>
                        </tr>
                    </table>
               </div>     
		<? } ?>
  
								   <?  if (isset($offers) && is_array($offers) && (count($offers) > 0)) { ?>
												<script type="text/javascript">
            var offersPROFlags = new Object();
            </script>
            <?
            $offers_count = count($offers) - 1;
            foreach ($offers as $key => $value) {
                unset($is_end); if($key == $offers_count) $is_end = true;
                if(($value['refused'] == 't' && $value['refuse_reason'] == 0) 
                    && !hasPermissions('projects')
                    && !($uid == $project['user_id'])) continue;
                if ($value["is_pro"]) {?>
                    <script type="text/javascript">offersPROFlags["<?=$value["id"] ?>"] = <?= $value["is_pro"] == 't' ? 1 : 0?>;</script>
              <?php   }   ?>
    
            <div id="po_<?=$value['id']?>" class="b-layout <?php if ($key < $offers_count) { ?>b-layout_bordbot_dedfe0 b-layout_margbot_20<? } ?>">
            <div class="b-fon b-fon_bg_f5 b-fon_pad_10 b-fon_margbot_20">
            <table class="b-layout__table b-layout__table_width_full">
             <tr class="b-layout__tr">
               <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad"><a class="b-layout__link" name="freelancer_<?=$value['user_id'] ?>" href="/users/<?=$value['login']?>"><?=view_avatar($value['login'], $value['photo'])?></a></td>
               <td class="b-layout__td b-layout__td_width_33ps b-layout__td_padright_20 b-layout__td_ipad">
                        
                         <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_5">
						    <?=$session->view_online_status($value['login'])?>
                            <a href="/users/<?=$value['login']?>" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold" title="<?=($value['uname'] . " " . $value['usurname'])?>"><?=($value['uname'] . " " . $value['usurname'])?></a>
                            [<a href="/users/<?=$value['login']?>" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold" title="<?=$value['login']?>"><?=$value['login']?></a>]
                            <span style="line-height:1; vertical-align:top;"><?= (view_mark_user($value)); /*!!!is_team!!!*/?> <?=($value['completed_cnt'] > 0?'<a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" title="Пользователь работал через Безопасную Сделку" target="_blank"><span class="b-icon b-icon__shield "></span></a>':'') ?></span> &#160; <?php if ( $value['is_banned'] ) { ?><span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_c10600 b-layout__txt_bold">Пользователь&nbsp;забанен.</span><?php } ?>
                         </div>
						 <? if ($value['spec_name'] != '') { ?><div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_5 b-layout__txt_bold">Специализация: <?=$value['spec_name']?></div><? }?>
                            <?php if ( $value['frl_refused'] == 't' ) { ?>
                                <div class="b-layout__txt b-layout__txt_color_c10600">Пользователь отказался от проекта</div>
                            <?php } else { ?>
                                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_5">
                                    <span class="b-layout__txt b-layout__txt_fontsize_11">Отзывы работодателей:</span>
                                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335"><a class="b-layout__link b-layout__link_color_6db335" href="/users/<?=$value['login']?>/opinions/?sort=1#op_head" target="_blank">+&nbsp;<?= (int)$value['opinions_plus'] ?></a></span>
                                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_c10600"><a class="b-layout__link b-layout__link_color_c10600" href="/users/<?=$value['login']?>/opinions/?sort=3#op_head" target="_blank">-&nbsp;<?= (int)$value['opinions_minus'] ?></a></span>
                                </div>
                                <div class="b-layout__txt b-layout__txt_fontsize_11">Рейтинг: <?=rating::round($value['rating'])?></div>
                            <?php } ?>
               </td>
                        
               <td class="b-layout__td b-layout__td_ipad">
                      
                   <? if(($uid == $project['user_id'])&&($value['frl_refused'] != 't')&&($project['status'] == projects_status::STATUS_NEW)) { ?>
                   <div class="b-layout b-layout_float_left b-layuot_width_full">
                      <div class="b-buttons b-buttons_clear_both b-buttons_float_right b-buttons_padbot_20" id="po_bar_<?=$value['id']?>">	 
                          <ul class="b-button-multi">
              <?
              if (($_GET['type'] == 'i') && ($value['user_id'] == $project['exec_id'])) {
                                          ?>
                                          <li id="po_b_exec_<?=$value['id']?>" class="b-button-multi__item b-button-multi__item_float_right b-button-multi__item_last b-button-multi__item_active"><a title="Буду работать с этим человеком." href="javascript:void(0)" onclick="yaCounter6051055.reachGoal('proj_isp');  xajax_SelectProjectExecutor(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>', <?=$project['exec_po_id']?>);" class="b-button-multi__link __ga__project__performer"><span class="b-button-multi__inner b-button-multi__inner_nowrap"><span class="b-button-multi__icon b-button-multi__icon_green"></span><span class="b-button-multi__txt">Выбрать исполнителем и предложить заказ</span></span></a></li>
                  <?
              } else {
                      if ($value['user_id'] == $project['exec_id']) {
                                          ;?><?
                      } else {
                                          ?><li id="po_b_exec_<?=$value['id']?>" class="b-button-multi__item b-button-multi__item_float_right b-button-multi__item_last"><a title="Буду работать с этим человеком." href="javascript:void(0)" onclick="yaCounter6051055.reachGoal('proj_isp'); xajax_SelectProjectExecutor(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>', <?=$project['exec_po_id']?>);" class="b-button-multi__link __ga__project__performer"><span class="b-button-multi__inner b-button-multi__inner_nowrap"><span class="b-button-multi__icon b-button-multi__icon_green"></span><span class="b-button-multi__txt">Выбрать исполнителем и предложить заказ</span></span></a></li><?
                      }
                                          ;?><?
              }
                                      ?>                              
                              
              <?
              if ($value['selected'] == 't') {
                                          ;?><li id="po_b_select_<?=$value['id']?>" class="b-button-multi__item b-button-multi__item_float_right b-button-multi__item_middle b-button-multi__item_active"><a title="Прошел предварительный отбор.
          Может быть исполнителем" href="javascript:void(0)" onclick="xajax_SelectProjectOffer(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>');" class="b-button-multi__link __ga__project__candidate"><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_blue"></span><span class="b-button-multi__txt">Кандидат</span></span></a></li><?
              } else {
                                          ?><li id="po_b_select_<?=$value['id']?>" class="b-button-multi__item b-button-multi__item_float_right b-button-multi__item_middle"><a title="Прошел предварительный отбор.
          Может быть исполнителем" href="javascript:void(0)" onclick="xajax_SelectProjectOffer(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>');" class="b-button-multi__link __ga__project__candidate"><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_blue"></span><span class="b-button-multi__txt">Кандидат</span></span></a></li><?
              }
                                      ?>                                          
                                          
                                      <?
              if ($value['refused'] == 't') {
                                          ;?><li id="po_b_refuse_<?=$value['id']?>"class="b-button-multi__item b-button-multi__item_float_right b-button-multi__item_first b-button-multi__item_active"><a title="Этот человек мне не подходит.
          Может быть в следующий раз." href="javascript:void(0)" onclick="xajax_RefuseProjectOffer(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>', 3);" class="b-button-multi__link"><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_red"></span><span class="b-button-multi__txt">Отказано</span></span></a></li><?
              } else {
                                          ?><li id="po_b_refuse_<?=$value['id']?>"class="b-button-multi__item b-button-multi__item_float_right b-button-multi__item_first"><a title="Этот человек мне не подходит.
          Может быть в следующий раз." href="javascript:void(0)" onclick="xajax_RefuseProjectOffer(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>', 3);" class="b-button-multi__link"><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_red"></span><span class="b-button-multi__txt">Отказать</span></span></a></li><?
              }
                                      ?>
                        </ul>
                      </div>
                       
                     <?php if(isset($order_url) && !empty($order_url) && $value['user_id'] == $project['exec_id']): ?>
                      <a href="<?=$order_url?>" class="b-button b-button_flat b-button_flat_green b-button_float_right b-button_margbot_20 b-button_clear_both">
                          Перейти в заказ
                      </a>
                     <?php endif; ?>                         
                       
                   </div>
              <?php } ?>
                   
                   
                   
                      <? if($uid == $project['user_id']) { ?>
                        <div class="b-layout b-layout_float_right">
                           <div class="b-layout b-layout_float_right uprj-bar<?=($value['in_team'] ? '-act' : '')?>" id="team_<?=$value['login']?>">
                               <? if(!$value['in_team']) { ?>
                               <div class="uprj-st1"><a href="javascript:void(0)" onclick="addToFav('<?=$value['login']?>')" class="lnk-dot-grey">Добавить в Избранные</a></div>
                               <? } else { ?>
                               <div class="uprj-st2">Этот исполнитель у вас в избранных (<a href="javascript:void(0)" onclick="delFromFav('<?=$value['login']?>')" class="lnk-dot-grey">убрать</a>)</div>
                               <? } ?>
                               <div class="uprj-st3" style="<?=$value['n_text']?'display:none;':'' ?>"><a href="javascript:void(0)" onclick="addNoteForm(this, '<?=$value['login']?>')" class="lnk-dot-grey">Добавить заметку</a></div>
                           </div>

                           <div class="b-layout b-layout_max-width_495 b-layout_float_right b-layout_clear_both uprj-note" id="note_<?=$value['login']?>" style="<?=!$value['n_text'] ? 'display:none;' : ''?>">
                               <strong class="b-layout__txt b-layout__txt_bold b-layout__txt_color_22b14c" style="margin-left:-1px;">Ваша заметка:</strong>
                               <div class="b-layout__txt b-layout__txt_inline b-layout__txt_color_22b14c b-layout__txt_fontsize_11 uprj-note-cnt">
                                   <p><?=reformat($value['n_text'], 54, 0, 0, 1, 54)?></p>&#160;&#160;<a href="javascript:void(0)" onclick="editNoteForm(this, '<?=$value['login']?>')" class="b-layout__link b-layout__link_dot_c10600">Изменить</a>
                                   <div style="display:none;"><?=$value['n_text']?></div>
                               </div>
                           </div>
                        </div>
                      <? } ?>
               </td>
             </tr>
            </table>
            </div> 
                
                <div class="b-layout__txt b-layout__txt_padbot_20">
                    Это предложение видно только вам и предложившему.
                </div>    
                
                <div id="po_u_<?=$value['id']?>" class="b-layout b-layout_padleft_60">
                    
                        <?php if (hasPermissions('projects') && 
                                  $project['exec_id'] == $value['user_id'] && 
                                  $uid != $project['user_id']) { ?>
                    
                            <?php if (isset($order_url) && !empty($order_url)): ?>
                                <div class="b-buttons b-buttons_padbot_20">
                                    <a href="<?= $order_url ?>" class="b-button b-button_flat b-button_flat_green">
                                        Перейти в заказ
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php } ?>
                    
                        <?php if($project['user_id'] == $_SESSION['uid'] || $_SESSION['uid'] == $value['user_id'] || hasPermissions('projects')) {?>
                           <? $txt_time = view_range_time($value['time_from'], $value['time_to'], $value['time_type']);?>
                                                    
                           <div class="b-layout__txt"><?=$txt_time?></div>
                           <? $txt_cost = view_range_cost2($value['cost_from'], $value['cost_to'], '', '', false, $value['cost_type']); if ($txt_cost != '') {  ?>
                              <div class="b-layout__txt"><?=$txt_cost?></div>
                           <?  } ?>
                        <?php }//if?>							
                         <? if ($value['prefer_sbr'] === 't') { ?>
                                <div class="b-layout__txt b-layout__txt_padbot_20">Предпочитаю оплату работы через <a href="/promo/bezopasnaya-sdelka/" target="_blank" class="b-layout__link">Безопасную Сделку</a> <?= view_sbr_shield('', 'b-icon_top_2') ?></div>
                         <? } ?>
                         
                         <? if (isset($value['dialogue']) && is_array($value['dialogue']) && (count($value['dialogue']) > 0)) {?>
                              <? if(count($value['dialogue'])> 1 || $value['is_pro'] == 't') { ?>
                                  <span id="count_<?=$value['id']?>" need_change="1" style="float: right;"></span>
                              <? }//if?>
                              <div class="b-layout b-layout_margbot_20 <?
                              if($uid == $project['user_id'] || (hasPermissions('projects') && $value['mod_new_msg_count'] > 0))
                                  echo ((($value['emp_new_msg_count'] > 0 || $value['po_emp_read'] == 'f') && ($project['login'] == $_SESSION["login"])) || ($value['emp_new_msg_count'] > 0 && hasPermissions('projects'))) ?
                                      "po_comments_new_hide" : "po_comments_hide";
                                                          ?>" id="po_comments_<?=$value['id']?>"><a name="comment<?=$value['id']?>"></a><?
                              if(hasPermissions('projects')) {
                                                          ?><span id="new_msgs_<?=$value['id']?>" need_change="<?=(count($value['dialogue'])==1?0:1)?>" style="float: right; <?=(count($value['dialogue'])==1?'display:none;':'')?>"><?
                                  if ($value['mod_new_msg_count'] > 0) {
                    if ($value['mod_new_msg_count'] == 1 && $value['msg_count'] == 1) {
                                                              ?><? if(count($value['dialogue'])==1) { ?><a href='javascript:void(null);' onclick="markRead('<?=$value['id']?>'); return false;"><img src="/images/ico_mail_2.gif" alt="Отметить как прочтенное" width="10" height="8" border="0" title="Отметить как прочтенное"></a><? } else { ?><a href='javascript:void(null);' onclick="markRead('<?=$value['id']?>'); return false;"><img src="/images/ico_mail_2.gif" alt="Отметить как прочтенное" width="10" height="8" border="0" title="Отметить как прочтенное"></a><? } ?>
                          <a href='javascript:void(null);' onclick="markRead('<?=$value['id']?>'); return false;" title="Отметить как прочтенное"><?=$value['emp_new_msg_count']?> <?=ending($value['mod_new_msg_count'], 'новое сообщение', 'новых сообщения', 'новых сообщений')?></a><?
                    } else {
                          ?><a href='javascript:void(null);' onclick="dialogue_toggle(<?=$value['id']?>); markRead('<?=$value['id']?>'); return false;"><img src="/images/ico_mail_2.gif" alt="Развернуть переписку" width="10" height="8" border="0" title="Развернуть переписку"></a>
                                                              <a href='javascript:void(null);' onclick="dialogue_toggle(<?=$value['id']?>); markRead('<?=$value['id']?>'); return false;" title="Развернуть переписку"><?=$value['mod_new_msg_count']?> <?=ending($value['mod_new_msg_count'], 'новое сообщение', 'новых сообщения', 'новых сообщений')?></a><?
                    }
                                  }
                                                          ?></span><?
              
                              } else {
                              if ($project['login'] == $_SESSION["login"]) {
                                                          ?><span id="new_msgs_<?=$value['id']?>" need_change="1" style="float: right;"><?
                                  if ($value['emp_new_msg_count'] > 0) {
                    if ($value['emp_new_msg_count'] == 1 && $value['msg_count'] == 1) {
                                                              ?><a href='javascript:void(null);' onclick="markRead('<?=$value['id']?>'); return false;"><img src="/images/ico_mail_2.gif" alt="Отметить как прочтенное" width="10" height="8" border="0" title="Отметить как прочтенное"></a>
                          <a href='javascript:void(null);' onclick="markRead('<?=$value['id']?>'); return false;" title="Отметить как прочтенное"><?=$value['emp_new_msg_count']?> <?=ending($value['emp_new_msg_count'], 'новое сообщение', 'новых сообщения', 'новых сообщений')?></a><?
                    } else {
                          ?><a href='javascript:void(null);' onclick="dialogue_toggle(<?=$value['id']?>); markRead('<?=$value['id']?>'); return false;"><img src="/images/ico_mail_2.gif" alt="Развернуть переписку" width="10" height="8" border="0" title="Развернуть переписку"></a>
                                                              <a href='javascript:void(null);' onclick="dialogue_toggle(<?=$value['id']?>); markRead('<?=$value['id']?>'); return false;" title="Развернуть переписку"><?=$value['emp_new_msg_count']?> <?=ending($value['emp_new_msg_count'], 'новое сообщение', 'новых сообщения', 'новых сообщений')?></a><?
                    }
                                  }
                                                          ?></span><?
                              }
                              }
                              $i = 0;
                              $dc = count($value['dialogue']);
                              if ($dc == 1) {
                                                          ?><div style="margin-bottom: 8px; font-size: 12px">
                                                              <? if($uid == $project['user_id']) { ?>
                                                              <span
                               class="<?=is_emp($value['dialogue'][0]['role']) ? 'emp' : 'frl'?>name11"><a
                               href="/users/<?=$value['dialogue'][0]['login']?>"
                               class="<?=is_emp($value['dialogue'][0]['role']) ? 'emp' : 'frl'?>name11"
                               title="<?=($value['dialogue'][0]['uname'] . " " . $value['dialogue'][0]['usurname'])?>"><?=($value['dialogue'][0]['uname'] . " " . $value['dialogue'][0]['usurname'])?></a>
                                  [<a href="/users/<?=$value['dialogue'][0]['login']?>"
                               class="<?=is_emp($value['dialogue'][0]['role']) ? 'emp' : 'frl'?>name11"
                               title="<?=$value['dialogue'][0]['login']?>"><?=$value['dialogue'][0]['login']?></a>]</span> <span id="po_date_<?=$comment['id']?>"><?=dateFormat("[d.m.Y | H:i]", $value['dialogue'][0]['post_date'])?></span><br />
                                                               <? } ?>
                                                          <?php $sPostText = ( $project['kind'] != 4 && ($value['dialogue'][0]['moderator_status'] === '0' || $value['moderator_status'] === '0')) ? $stop_words->replace($value['dialogue'][0]['post_text']) : $value['dialogue'][0]['post_text']; ?>
                                                 <div id="po_comment_<?=$comment['id']?>"><?=reformat(rtrim(strip_tags($sPostText)), 50, 0, 0, 1)?></div>
                                                       <div id="po_comment_original_<?=$comment['id']?>" style="display:none;"><?=str_replace(' ', '&nbsp;', reformat(rtrim(strip_tags($value['dialogue'][0]['post_text'])), 1000, 0, 1))?></div>
                                                          </div>
                                  <div id="po_dialogue_talk_<?=$value['id']?>" style="font-size: 12px; visibility: visible; height: auto; overflow: visible; display: none;">
                                  </div><?
                                  if ($project['login'] == $_SESSION["login"]) {
                                                          ?><div id="po_dialogue_answer_<?=$value['id']?>" style="font-size: 100%; margin: 16px 0px 6px 0px;"><?
                                      /* if ($project['closed'] == 'f') { // блок, если меможно писать ответ */
                                          if (count($value['dialogue']) > 1) {
                                                              ?><span style="float: right;"><a
                                   href="javascript:void(null)"
                                   onClick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');"
                                   class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть
                                      всю переписку</a> <?=count($value['dialogue'])?></span><?
                                          }
                                                              ?>
              <? if($po_type!='r' && $value['frl_refused'] != 't') { ?>
                                                  <span><a href="javascript:void(0);"
                                   onClick="answer(<?=$value['id']?>);markRead('<?=$value['id']?>');"
                                   class="internal">Написать ответ</a></span>
                                   
                                           <?
                                           if ($comment['user_id'] == get_uid() && count($value['dialogue']) > 1){
                                           ?>
              
                                           &nbsp;<span><a href="javascript:void(null)" onClick="answer(<?=$value['id']?>, <?=$comment['id']?>);markRead('<?=$value['id']?>');" class="internal">Редактировать</a></span>
                                           <SCRIPT language="javascript">
                    last_commentid = <?=$comment['id']?>;
                                           edit_block[<?=$value['id']?>] = '&nbsp;&nbsp;<span><a href="javascript:void(null)" onClick="answer(<?=$value['id']?>, last_commentid);markRead(\'<?=$value['id']?>\');" class="internal">Редактировать</a></span>';
                                           </SCRIPT>
              
                                           <?
                                           }
                                           ?>
              <? } else { ?>
                                                  <span>&nbsp;</span>
              <? } ?>
              
                                   <?
                                      
                                                          ?></div><?
                                  }
                              } elseif (($project['login'] == $_SESSION["login"]) || hasPermissions('projects')) {
                                  $nBlockedCnt = 0;
                                  
                                  foreach ($value['dialogue'] as $key => $comment) {
                                      if ($i == 1) {
                                                          ?><div id="po_dialogue_talk_<?=$value['id']?>" style="font-size: 12px; visibility: visible; height: auto; overflow: visible; display: none;"><?
                                      }
                                      $i ++;
                                      
                                      if ( $comment['is_blocked'] != 't' || $comment['login'] == $_SESSION["login"] || hasPermissions('projects') ) {
                                                              ?><div style="margin-bottom: 8px; font-size:12px;"><a name="comment_<?=$comment['id']?>" id="comment_<?=$comment['id']?>"></a><span
                                   class="<?=is_emp($comment['role']) ? 'emp' : 'frl'?>name11"><a
                                   href="/users/<?=$comment['login']?>"
                                   class="<?=is_emp($comment['role']) ? 'emp' : 'frl'?>name11"
                                   title="<?=($comment['uname'] . " " . $comment['usurname'])?>"><?=($comment['uname'] . " " . $comment['usurname'])?></a>
                                      [<a href="/users/<?=$comment['login']?>"
                                   class="<?=is_emp($comment['role']) ? 'emp' : 'frl'?>name11"
                               title="<?=$value['dialogue'][$i-1]['login']?>"><?=$value['dialogue'][$i-1]['login']?></a>]</span> <span id="po_date_<?=$comment['id']?>"><?=dateFormat("[d.m.Y | H:i]", $value['dialogue'][$i-1]['post_date'])?></span>
                                                          <?php if ( $i != 1 && hasPermissions('projects') && $comment['login'] != $_SESSION["login"] ) { ?>
                                                          <span style="float: right;" id="dialogue-button-<?= $comment['id'] ?>">
                                                              <a class="admn" href="javascript:void(0);" onclick="banned.<?=($comment['is_blocked']=='t'? 'unblockedDialogue': 'blockedDialogue')?>(<?=$comment['id']?>)"><?= $comment['is_blocked']=='f'?"Заблокировать":"Разблокировать"; ?></a>
                                                          </span>
                                                          <?php } ?>
                                                          <br />
                                                          <?php $sPostText = ($project['kind'] != 4 && ($value['dialogue'][$i-1]['moderator_status'] === '0' || $i == 1 && $value['moderator_status'] === '0')) ? $stop_words->replace($value['dialogue'][$i-1]['post_text']) : $value['dialogue'][$i-1]['post_text']; ?>
                                                 <div id="po_comment_<?=$comment['id']?>"><?=reformat(rtrim(strip_tags($sPostText)), 50, 0, 0, 1)?></div>
                                                       <div id="po_comment_original_<?=$comment['id']?>" style="display:none;"><?=str_replace(' ', '&nbsp;', reformat(rtrim(strip_tags($value['dialogue'][$i-1]['post_text'])), 1000, 0, 1))?></div>
                                                   </div>
                                                              
                                                          <?php if ( $i != 1 ) { ?>
                                                          <div id="dialogue-block-<?= $comment['id'] ?>" style="display: <?= ($comment['is_blocked'] ? 'block': 'none') ?>">
                                                              <? if ($comment['is_blocked'] == 't') { ?>
                                                              <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
                                                                  <b class="b-fon__b1"></b>
                                                                  <b class="b-fon__b2"></b>
                                                                  <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13">
                                                                      <span class="b-fon__attent"></span>
                                                                      <div class="b-fon__txt b-fon__txt_margleft_20">
                                                                              <span class="b-fon__txt_bold">Комментарий заблокирован</span>. <?= reformat($comment['blocked_reason'], 24, 0, 0, 1, 24) ?> <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                                                                              <div class='b-fon__txt'><?php if ( hasPermissions('projects') ) { ?><?= ($comment['admin_login'] ? "Заблокировал: <a class='b-fon__link' href='/users/{$comment['admin_login']}'>{$comment['admin_uname']} {$comment['admin_usurname']} [{$comment['admin_login']}]</a><br />": '') ?><?php } ?>
                                                                              Дата блокировки: <?= dateFormat('d.m.Y H:i', $comment['blocked_time']) ?></div>
                                                                      </div>
                                                                  </div>
                                                                  <b class="b-fon__b2"></b>
                                                                  <b class="b-fon__b1"></b>
                                                              </div>
                                                              <? } ?>
                                                          </div>
                                                          <?php } ?>
                                              <?
                                      }
                                      else {
                                          $nBlockedCnt++;
                                      }
                                      
                                      if ($i == $dc) {
                                                      ?></div><?
                                      }
                                  }
                                  if ($project['login'] == $_SESSION["login"]) {
                                                      ?><div id="po_dialogue_answer_<?=$value['id']?>" style="font-size: 100%; margin: 16px 0px 6px 0px;"><?
                                      /*
                                      if ($project['closed'] == 'f') {
                                      */
                                          if (count($value['dialogue']) > 1) {
                                                          ?><span style="float: right;"><a
                               href="javascript:void(null)"
          
                               onClick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');"
                               class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть
                                  всю переписку</a> <?=(count($value['dialogue']) - $nBlockedCnt)?></span><?
                                          }
                                                          ?>
              
              <? if($po_type!='r' && $value['frl_refused'] != 't') { ?>
                                              <span><a href="javascript:void(0);"
                               onClick="answer(<?=$value['id']?>);markRead('<?=$value['id']?>');"
                               class="internal">Написать ответ</a></span>
                               
                               
                                           <?
                                           if ($comment['user_id'] == get_uid() && count($value['dialogue']) > 1){
                                           ?>
              
                                           &nbsp;<span><a href="javascript:void(null)" onClick="answer(<?=$value['id']?>, <?=$comment['id']?>);markRead('<?=$value['id']?>');" class="internal">Редактировать</a></span>
                                           <SCRIPT language="javascript">
                                           edit_block[<?=$value['id']?>] = '&nbsp;&nbsp;<span><a href="javascript:void(null)" onClick="answer(<?=$value['id']?>, <?=$comment['id']?>);markRead(\'<?=$value['id']?>\');" class="internal">Редактировать</a></span>';
                                           </SCRIPT>
              
                                           <?
                                           }
                                           ?>
              <? } else { ?>
                                                  <span>&nbsp;</span>
              <? } ?>
                               
                               
                               </div><?
                                  } elseif (hasPermissions('projects') && (count($value['dialogue']) > 1)) {
                                                      ?><div id="po_dialogue_answer_<?=$value['id']?>" style="font-size: 100%; margin: 0px 0px 6px 0px;"><?
                                                          ?><span style="float: right;"><a
                                                          href="javascript:void(null)"
                                                          onClick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');"
                                                          class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть
                                                          всю переписку</a> <?=count($value['dialogue'])?></span><span>&nbsp;</span></div><?
                                  }
                              } else {
                                                          ?><div style="margin-bottom: 8px; font-size: 12px;">
                                                              <? if($uid == $project['user_id']) { ?>
                                                              <span
                                                          class="<?=is_emp($value['dialogue'][0]['role']) ? 'emp' : 'frl'?>name11"><a
                                                          href="/users/<?=$value['dialogue'][0]['login']?>"
                                                          class="<?=is_emp($value['dialogue'][0]['role']) ? 'emp' : 'frl'?>name11"
                                                          title="<?=($value['dialogue'][0]['uname'] . " " . $value['dialogue'][0]['usurname'])?>"><?=($value['dialogue'][0]['uname'] . " " . $value['dialogue'][0]['usurname'])?></a>
                                                          [<a href="/users/<?=$value['dialogue'][0]['login']?>"
                                                          class="<?=is_emp($value['dialogue'][0]['role']) ? 'emp' : 'frl'?>name11"
                                                          title="<?=$value['dialogue'][0]['login']?>"><?=$value['dialogue'][0]['login']?></a>]</span> <?=dateFormat("[d.m.Y | H:i]", $value['dialogue'][0]['post_date'])?><br />
                                                              <? } ?>
                                                          <?php $sPostText = ($project['kind'] != 4 && ($value['dialogue'][0]['moderator_status'] === '0' || $value['moderator_status'] === '0')) ? $stop_words->replace($value['dialogue'][0]['post_text']) : $value['dialogue'][0]['post_text']; ?>
                                                          <?=reformat(strip_tags($sPostText), 50, 0, 0, 1)?>
                                                          </div>
                                                          <div id="po_dialogue_talk_<?=$value['id']?>" style="font-size: 12px; visibility: visible; height: auto; overflow: visible; display: none;">
                                                          </div><?
                              }
                                                  ?></div>
                         <? } ?>
                         
                         
                        
        
        <?  if ($value['is_pro'] == 't' || $value['is_pro_test'] == 't')  { ?>
        
                                <? if ($value['pict1'] != '') { ?>
                                <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
                                    <tr class="b-layout__tr">
                                        <?php for ($i=1; $i<=3; $i++) { ?>
                                        <td class="b-layout__td b-layout__td_padright_20">
                                              <? if ($value['pict'.$i] != '') { ?>
                                                      <?php  $aData = getAttachDisplayData( $value['login'], $value['pict'.$i], "upload", 200, 200, 307200, 0 ); ?>
                                                      <table class="b-layout__table">
                                                         <tr class="b-layout__tr">
                                                             <td class="b-layout__td b-layout__td_valign_mid b-layout__td_center b-layout__td_width_200 b-layout__td_height_200">
                                                                <?php
                                                                if ( $aData['virus_flag'] ) {
                                                                ?>
                                                                <div class="filesize">
                                                                    <a <?=$aData['link']?> target="_blank"><img src="/images/<?=$aData['file_ico']?>" alt="<?=$aData['file_name']?>" title="<?=$aData['file_name']?>" /></a>
                                                                    <div><?=$aData['file_ext']?> <?=$aData['file_size_str']?>&nbsp;&nbsp;</div>
                                                                </div>
                                                                <?php
                                                                }
                                                                else {
                                                                    echo '<div>';
                                                                    
                                                                    if ($value['portf_id'.$i] == 0) { ?>
                                                                            
                                                                            
                                                                            
                                                                                <? if (in_array(CFile::getext($value['pict'.$i]), $GLOBALS['graf_array']) || strtolower(CFile::getext($value['pict'.$i])) == "mp3") { ?>
                                                                                    <? if ($value['prev_pict'.$i] != '') { ?>
                                                                            <div ><a href="/projects/viewwork.php?pid=<?=$value['project_id']?>&user=<?=$value['login']?>&wid=<?=$i?>" target="_blank" class="blue" title="" style="text-decoration:none"><?=view_preview($value['login'], $value['prev_pict'.$i], "upload", $align, false, true)?></a></div>
                                                                                    <? } else { ?>
                                                                            <div style="font-size:11px;">
                                                                                <a href="/projects/viewwork.php?pid=<?=$value['project_id']?>&user=<?=$value['login']?>&wid=<?=$i?>" target="_blank" class="blue" title=""><img src="/images/<?=$aData['file_ico']?>" alt="<?=$aData['file_name']?>" title="<?=$aData['file_name']?>" /></a></div>
                                                                                <div><?=$aData['file_ext']?> <?=$aData['file_size_str']?>&nbsp;&nbsp;</div>
                                                                                    <? } ?>
                                                                                <? } else { 
                                                                                    ?>
                                                                                    <? if ($value['prev_pict'.$i] != '') { ?>
                                                                            <div><a href="<?=WDCPREFIX?>/users/<?=$value['login']?>/upload/<?=$value['pict'.$i]?>" target="_blank" class="blue" title="" style="text-decoration:none"><?=view_preview($value['login'], $value['prev_pict'.$i], "upload", $align, false, true)?></a></div>
                                                                                    <? } else { ?>
                                                                            <div style="font-size:11px;"><a href="<?=WDCPREFIX?>/users/<?=$value['login']?>/upload/<?=$value['pict'.$i]?>" target="_blank" class="blue" title=""><img src="/images/<?=$aData['file_ico']?>" alt="<?=$aData['file_name']?>" title="<?=$aData['file_name']?>" /></a></div>
                                                                            <div><?=$aData['file_ext']?> <?=$aData['file_size_str']?>&nbsp;&nbsp;</div>
                                                                                    <? } ?>
                                                                                <? } ?>
                                                                                
                                                                                
                                                                            <? } else { 
                                                                                ?>
                                                                            
                                                                            
                                                                                <? if ($value['prev_pict'.$i] != '') { ?>
                                                                            <div><a href="/users/<?=$value['login']?>/viewproj.php?prjid=<?=$value['portf_id'.$i]?>" target="_blank" class="blue" title="" style="text-decoration:none"><?=view_preview($value['login'], $value['prev_pict'.$i], "upload", $align, false, true)?></a></div>
                                                                                <? } else { ?>
                                                                            <div style="font-size:11px;"><a href="/users/<?=$value['login']?>/viewproj.php?prjid=<?=$value['portf_id'.$i]?>" target="_blank" class="blue" title=""><img src="/images/<?=$aData['file_ico']?>" alt="<?=$aData['file_name']?>" title="<?=$aData['file_name']?>" /></a></div>
                                                                            <div><?=$aData['file_ext']?> <?=$aData['file_size_str']?>&nbsp;&nbsp;</div>
                                                                            
                                                                                <? } ?>
                                                                            <? }  
                                                                    
                                                                    echo '</div>';
                                                                }
                                                                ?>
                                                             </td>
                                                         </tr>
                                                      </table>
                                              <? }  ?>
                                        </td>
                                        <?php  } ?>
                                    </tr>
                                </table>
                                <?php } ?>
        
        <?   }  ?>
        
                                <?php if (hasPermissions('projects')) { ?>
                                <div class="prj-admin-btn c" style=" padding-bottom:0; padding-top:0; float:right">
                                    <ul>
                                        <li id="project-button-<?=$value['id']?>"><a class="admn" href="javascript:void(0);" onclick="banned.<?=($value['is_blocked']=='t'? 'unblockedProjectOffer': 'blockedProjectOffer')?>(<?=$value['id']?>,<?=$value['user_id']?>,<?= $project['id']?>)"><?= $value['is_blocked']=='f'?"Заблокировать":"Разблокировать"; ?></a></li>
                                        <li><?php if ($value['warn']<3 && !$value['is_banned'] && !$value['ban_where'] ) { ?>
                                    <span class='warnlink-<?= $value['user_id']?>'><a style='color: red;' href='javascript: void(0);' onclick='banned.warnUser(<?= $value['user_id']?>, 0, "projects", "p<?= $project['id']?>", 0); return false;'>Сделать предупреждение (<span class='warncount-<?= $value['user_id']?>'><?= ($value['warn'] ? $value['warn'] : 0);?></span>)</a></span> | 
                                    <?php } else { 
                                        $sBanTitle = (!$value['is_banned'] && !$value['ban_where']) ? 'Забанить!' : 'Разбанить';
                                        ?>
                                    <span class='warnlink-<?= $value['user_id']?>'><a class="admn" href="javascript:void(0);" onclick="banned.userBan(<?=$value['user_id']?>, 'p<?= $project['id']?>',0)"><?=$sBanTitle?></a></span> | 
                                    <?php } //else ?></li>
                                    </ul>
                                </div>
                                <?php } //if?>
        
                                <div id="project-offer-block-<?= $value['id'] ?>" style="display: <?= ($value['is_blocked'] == 't' ? 'block': 'none') ?>">
                                    <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
                                        <b class="b-fon__b1"></b>
                                        <b class="b-fon__b2"></b>
                                        <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13">
                                            <span class="b-fon__attent"></span>
                                            <div class="b-fon__txt b-fon__txt_margleft_20">
                                                    <span class="b-fon__txt_bold">Предложение заблокировано</span>. <?= reformat($value['blocked_reason'], 24, 0, 0, 1, 24) ?> <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                                                    <div class='b-fon__txt'><?php if ( hasPermissions('projects') ) { ?><?= ($value['admin_login'] ? "Заблокировал: <a class='b-fon__link' href='/users/{$value['admin_login']}'>{$value['admin_uname']} {$value['admin_usurname']} [{$value['admin_login']}]</a><br />": '') ?><?php } ?>
                                                    Дата блокировки: <?= dateFormat('d.m.Y H:i', $value['blocked_time']) ?></div>
                                            </div>
                                        </div>
                                        <b class="b-fon__b2"></b>
                                        <b class="b-fon__b1"></b>
                                    </div>
                                </div>
        
                               <div class="b-buttons b-buttons_padbot_30">
                                    <?php if ($value['user_id'] != $project['exec_id']): ?>
                                    <a href="/new-personal-order/<?=$value['login']?>/" class="b-button b-button_flat b-button_flat_green ">
                                        Предложить заказ
                                    </a>
                                    <?php endif; ?>
                                    <span class="b-layout__txt b-layout__txt_fontsize_11"> &#160; <a class="b-layout__link b-layout__link_bold" href="/users/<?=$value['login']?>/tu/">Посмотреть все услуги фрилансера</a>  или <a class="b-layout__link b-layout__link_bold" href="/users/<?=$value['login']?>/">его портфолио</a></span>
                               </div>
                    
                    </div>
        
            </div>
            <?  } } else { ?>&nbsp;<? } ?>
        <? if($count_hidden_offers && count($offers)) { ?>
                <h2 class="offer_project"><br />Остальные ответы фрилансеров скрыты и видны только заказчику</h2>
        <? } ?>

<?php 
    // Страницы
    $pagesCount = ceil($num_offers / MAX_OFFERS_AT_PAGE);
    if ($pagesCount):
        $href = '%s/projects/index.php?pid='. $prj_id;
        if (isset($po_sort)) $href .= '&sort=' . $po_sort;
        if (isset($po_type)) $href .= '&type=' . $po_type;
        $href .= '&page=%d%s';
        if(new_paginator2($item_page, $pagesCount)) {
            echo new_paginator2($item_page, $pagesCount, 3, $href);
        }
    endif;
} 

} // if ($project['is_blocked'] != 't') ?>

		<script language="javascript">
var my1 = 0;

function check_com_text() {
    if((document.getElementById('po_text') != undefined) && document.getElementById('po_text').value.length > 1000) {
		document.getElementById('po_text').value = document.getElementById('po_text').value.substr(0, 1000);
		document.getElementById('po_text_msg').innerHTML = '<? print(ref_scr(view_error('Исчерпан лимит символов для поля (1000 символов)'))); ?>';
	}
}

function scroll() {
	var my2 = $('po_selector').getCoordinates();
	if (document.body.scrollTop > my2.top)
	{
		for (var i = 0; i < (document.body.scrollTop - my2.top); i++) {
			$('po_selector').setStyle('top', (my2.top - my1.top + i) + 'px');
		}
	}
	else
	{
		if (document.body.scrollTop > my1.top) {
			for (var i = 0; i > (document.body.scrollTop - my2.top); i--) {
				$('po_selector').setStyle('top', (my2.top - my1.top + i) + 'px');
			}
			//      $('po_selector').setStyle('top', (document.body.scrollTop - my1.top) + 'px');
		}
		else {
			if (document.body.scrollTop != my2.top) {
				$('po_selector').setStyle('top', '0px');
			}
		}
	}
}

function start_scroll() {
	my1 = $('po_selector').getCoordinates();
	setInterval('scroll()', 100);
}
</script>

<div class="b-layout b-layout_float_right b-layout_clear_both uprj-note c form-templ" style="display: none;">
    <strong class="uprj-note-lbl" style="margin-left:-1px;">Ваша заметка:</strong>
    <div class="uprj-note-cnt b-uprj-note-cnt">
        <input type="hidden" name="note_rating" id="note_rating" value="">
        <textarea id="f_n_text" name="n_text" rows="2" cols="50" onkeyup="checknote(this)"></textarea>
        <input type="button" value="Сохранить" class="i-btn i-bold" onclick="updateNote(this)" /> <input type="button" value="Отменить" class="i-btn i-btn-cancel" onclick="cancelNote(this)" />
    </div>
</div>
<?php if($project['login'] == $_SESSION["login"]) {
    $baners = array(
        'image' => array (
            'b-banner__sbr.png'
        ),
        'title' => array (
            "Работайте безопасно"
        ),
        'alt' => array (
            "«Безопасная Сделка»"
        ),
        'link' => array (
            '/'.sbr::NEW_TEMPLATE_SBR.'/'
        )
    );
    $index = 0;
    ?>
    
    <br><br>
            

<?php if($projectObject->isAllowShowOffers()): ?>
<?php if ($project['is_blocked'] != 't'): ?>
            <?php
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/masssending.php";
            $masssending = new masssending;
            $calc = $masssending->setCalcMethods('pro')->Calculate(get_uid(false), array());
            $tariff = masssending::GetTariff();
            ?>
            <? if (!($project["closed"] == 't')||(!$project['is_blocked'])) { ?>
            <div class="b-layout">
               <h2 class="b-layout__title">Пригласить фрилансеров в проект</h2>
               <section class="b-frm-filtr b-frm-filtr_pad_15 b-layout_bord_c6">
                   <form id="mass_frm" action="/masssending/add.php" method="POST">
   
                       <input type="hidden" name="msg" value="<?= str_replace('"', "&quot;", $project['descr']) ?>">
                       <input type="hidden" name="title" value="<?= str_replace('"', "&quot;", $project['name']) ?>">
                       <input type="hidden" name="link" value="<?= getFriendlyURL('project', $_GET['pid'])?>">
                       <input type="hidden" name="max_users" id="mass_max_users" value="0">
                       <input type="hidden" name="max_cost" id="mass_max_cost" value="0">
                       <input type="hidden" name="f_users" id="mass_f_users" value="<?=$calc['count']?>">
                       <input type="hidden" name="f_cost" id="mass_f_cost" value="<?=$calc['cost']?>">
                       <?
                       $cats_v = '';
                       if($project_specs) {
                           foreach($project_specs as $v) {
                               if($v['subcategory_id']==0) {
                                   $span_id = $v['category_id'].'_1';
                               } else {
                                   $span_id = $v['subcategory_id'].'_0';
                               }
                               $cats_v .= "mass_cat_span_{$span_id},";
                           }
                       }
                       ?>
                           
   
                       <input type="hidden" name="f_cats" id="mass_f_cats" value="<?=$cats_v?>">
   
   
                   
                      <table class="b-layout__table b-layout__table_width_full">
                         <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_width_210"><div class="b-layout__txt">Разделы</div></td>
                            <td class="b-layout__td b-layout__td_padbot_30">
   
   
   
                               <?
                               if($project_specs) {
                                   foreach($project_specs as $v) {
                                       $is_dop = 0;
                                       if($v['subcategory_id']==0) {
                                           $span_id = $v['category_id'].'_1';
                                           $span_name = professions::GetProfGroupTitle($v['category_id']);
                                       } else {
                                           $span_id = $v['subcategory_id'].'_0';
                                           $tt = professions::GetProfTitle($v['subcategory_id']);
                                           $span_name = $tt['name'];
                                           $is_dop = 1;
                                       }
                                   ?>
                                   <span id="mass_cat_span_<?=$span_id?>" class="b-frm-fltr__spec <?=$is_dop ? 'b-frm-fltr__spec_dop' : ''?>"><?=$span_name?><span class="b-frm-fltr__spec-close" onclick="mass_spam.delcat('<?=$span_id?>');"></span></span>
                                   <?
                                   }
                               }
                               ?>
                               <span id="mass_clist" style="display: none;"></span>
   
                               <span class="b-layout__txt b-layout__txt_valign_top" style="cursor: pointer;" onClick="this.getNext('.b-layout__table').setStyle('display','table'); return false;">Добавить раздел
                                   <a href="#" class="b-button b-button_poll_plus b-button_valign_top b-button_margtop_4" onClick="this.getParent().getNext('.b-layout__table').setStyle('display','table'); return false;"></a>
                               </span>
                                <table class="b-layout__table b-layout__table_margtop_10" style="display:none;">
                                   <tr class="b-layout__tr">
                                      <td class="b-layout__one">
                                          <div class="b-combo ">
                                               <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_530 b-combo__input_orientation_right b-combo__input_visible_height_200 b-combo__input_init_professionsList sort_cnt drop_down_default_0 multi_drop_down_default_column_0 exclude_value_0_0 override_value_id_0_0_Все+разделы"> 
                                                   <input id="mass_cats" class="b-combo__input-text" data-noinputchange="1" name="cats" type="text" size="80" value="Все разделы" /> 
                                                   <span class="b-combo__arrow"></span> 
                                               </div> 
   
   
                                          </div>
                                      </td>
                                      <td class="b-layout__one b-layout__one_padleft_5">
                                          <a class="b-button b-button_flat b-button_flat_grey" href="#" onclick="mass_spam.addcat(); return false;">Добавить</a>
                                      </td>
                                   </tr>
                                </table>  
                            </td>
                         </tr>
                         <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_width_210"><div class="b-layout__txt b-layout__txt_padtop_5">Откуда</div></td>
                            <td class="b-layout__td b-layout__td_padbot_30">
   
                               <div class="b-combo b-combo_padbot_15">
                                   <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_530 b-combo__input_orientation_right b-combo__input_init_citiesList b-combo__input_on_click_request_id_getcities override_value_id_0_0_Все+страны override_value_id_1_0_Все+города"> 
                                       <input id="mass_location" class="b-combo__input-text" name="location" type="text" size="80" value="Все страны" onblur="mass_spam.send();" /> 
                                       <label class="b-combo__label" for="mass_location"></label> 
                                       <span class="b-combo__arrow"></span> 
                                   </div> 
                               </div>
   
                               <div class="b-check ">
                                   <input id="mass_office" class="b-check__input" name="office" type="checkbox" value="1" onclick="mass_spam.send();"/>
                                   <label for="mass_office" class="b-check__label b-check__label_fontsize_13">Ищет работу в офисе</label>
                               </div>
                            </td>
                         </tr>
                         <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_width_210"><div class="b-layout__txt">Дополнительно</div></td>
                            <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_padtop_3">
                               <div class="b-txt b-txt_inline-block b-txt_width_250">
                                   <div class="b-check b-check_padbot_10">
                                       <input disabled id="mass_favorites" class="b-check__input" name="favorites" type="checkbox" value="1" onclick="mass_spam.send();" />
                                       <label for="mass_favorites" class="b-check__label b-check__label_fontsize_13">У меня в избранных (скоро)</label>
                                   </div>
                                   <div class="b-check b-check_padbot_10">
                                       <input id="mass_free" class="b-check__input" name="free" type="checkbox" value="1" onclick="mass_spam.send();" />
                                       <label for="mass_free" class="b-check__label b-check__label_fontsize_13">Только свободные</label>
                                   </div>
                                   <div class="b-check b-check_padbot_10">
                                       <input id="mass_pro" class="b-check__input" name="pro" type="checkbox" value="1" onclick="mass_spam.send();"/>
                                       <label for="mass_pro" class="b-check__label b-check__label_fontsize_13">С аккаунтом <a class="b-layout__link" href="/payed/"><span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_3" title="PRO"></span></a></label>
                                   </div>
                               </div>
                               <div class="b-txt b-txt_inline-block">
                                   <div class="b-check b-check_padbot_10">
                                       <input id="mass_withworks" class="b-check__input" name="withworks" type="checkbox" value="1" onclick="mass_spam.send();" />
                                       <label for="mass_withworks" class="b-check__label b-check__label_fontsize_13">Только с примерами работ</label>
                                   </div>
                                   <div class="b-check b-check_padbot_10">
                                       <input id="mass_ver" class="b-check__input" name="ver" type="checkbox" value="1" onclick="mass_spam.send();" />
                                       <label for="mass_ver" class="b-check__label b-check__label_fontsize_13">С аккаунтом <a class="b-layout__link" href="/promo/verification/"><span class="b-icon b-icon__ver"></span></a></label>
                                   </div>
                                   <div class="b-check b-check_padbot_10">
                                       <input id="mass_bs" class="b-check__input" name="bs" type="checkbox" value="1" onclick="mass_spam.send();" />
                                       <label for="mass_bs" class="b-check__label b-check__label_fontsize_13">С успешными &laquo;Безопасными Сделками&raquo; <a class="b-layout__link" href="/promo/bezopasnaya-sdelka/"><span class="b-icon b-icon__shield"></span></a></label>
                                   </div>
                               </div>
                            </td>
                         </tr>
                         <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padbot_30" colspan="2">
                               <div class="b-fon b-fon_bg_fff9bf b-fon_pad_15 b-fon_padleft_210">
                                  <div class="b-layout__txt b-txt_bold b-txt_padbot_10">Найдено фрилансеров: <span id="mass_find_count"><?=$calc['count']?></span></div>
                                  <div id="slider1" class="b-frm-filtr__opinion b-frm-filtr__opinion_width_200 b-frm-filtr__opinion_inline-block"><span id="knob1" class="b-frm-filtr__point_2"></span></div>                               
                                  <div class="b-layout__txt">Стоимость рассылки: <span id="mass_find_cost"><?=$calc['cost']?></span> руб.<br/>1 фрилансер – <?=preg_replace('/\.00$/','',$tariff['no_pro'])?> руб., 1 фрилансер с PRO или верифицированный – <?=preg_replace('/\.00$/','',$tariff['pro'])?> руб.</div>
                               </div>
                            </td>
                         </tr>
                         <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_width_210"></td>
                            <td class="b-layout__td">
                               <div class="b-buttons b-buttons_padbot_15">
                                  <a id="mass_sendit" href="" class="b-button b-button_flat b-button_flat_green" onclick="mass_sendit(); return false;">Пригласить фрилансеров в проект</a>                            
                               </div>
                            </td>
                         </tr>
                      </table>
                   </form>
               </section>
            </div>
            <?php } ?>
            <? require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_mas.php"); ?>
    
<?php endif; ?>
  <div class="b-layout">
    <div class="b-banner b-banner_pf">
        <a class="b-banner__link" href="<?= $baners['link'][$index] ?>" title="<?= $baners['title'][$index] ?>"><img class="b-banner__pf" src="/css/block/b-banner/<?= $baners['image'][$index] ?>" alt="<?= $baners['alt'][$index] ?>"/></a>
    </div>
  </div>
<?php endif; ?>
    
    
<?php } //if?>
<?php if (hasPermissions('projects') ) { ?>
<script type="text/javascript">

</script>
<?php } ?>

<div class="b-layout b-layout_padtop_20">
    <h2 class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_666 b-layout_top_100 b-layout__txt_padbot_10 b-layout__txt_weight_normal">
        <?= SeoTags::getInstance()->getFooterText() ?>
    </h2>
</div>    
 
      </div>

<? if($_GET['quickprj_ok'] && $_SESSION['quickprj_ok']) { require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_prj.php"); } ?>
<? if(isset($_SESSION['new_public']) && $_SESSION['new_public']) { require_once($_SERVER['DOCUMENT_ROOT'] . "/projects/tpl.popup_share.php"); } ?>