<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects_ci.common.php");
if (!$no_answers) {$xajax->printJavascript('/xajax/');}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/HTML/projects_lenta.php");
$project_exRates = project_exrates::GetAll();
$exch = array(1=>'FM', 'USD','Euro','Руб');

$translate_exRates = array
(
0 => 2,
1 => 3,
2 => 4,
3 => 1
);
$category = professions::GetGroup($project['category'], $eeee);
if($category['name'] && $project['subcategory'])
   $category['name'] .= '&nbsp;/&nbsp;'.professions::GetProfName($project['subcategory']);
?>
<? if (hasPermissions('projects')) { ?>
<script type="text/javascript">
    var PROJECT_BANNED_PID = 'p<?= $project['id']?>';
    var PROJECT_BANNED_URI = '<?=$GLOBALS['host']?><?=getFriendlyURL("project", $project['id'])?>';
    var PROJECT_BANNED_NAME = "<?=htmlspecialchars($project['name'])?>";
</script>
<script type="text/javascript" ></script>
<? } ?>
<?
if (!$no_answers)
{
?>
<script type="text/javascript">
<!--
var old_num = 0;
var inner = false;
var cur_prof = <?=$cur_prof?>;
var dialogue_count = new Array(1);
<? if (isset($user_offer['id'])) { ?>
dialogue_count[<?=$user_offer['id']?>] = <?=count($user_offer['dialogue'])?>;
<? } else { ?>
dialogue_count[0] = 0;
<? } ?>
var last_work = <?=($user_offer_exist)?count($user_offer['attach']):0?>;

var works_prevs = new Array();
var works_picts = new Array();

<? foreach ($portf_works as $key => $value) { ?>
works_prevs[<?=$value['id']?>] = '<?=$value['prev_pict']?>';
works_picts[<?=$value['id']?>] = '<?=$value['pict']?>';
<? } ?>

function submitAddFileForm() {
    xajax.$("ps_pict_add").disabled=true;
    xajax.$("ps_pict_add").value="Файл загружается";
    xajax_submitAddFileForm(xajax.getFormValues("form_add_pict"));
    return false;
}

function GetForm(num, commentid){
	if (!commentid) {commentid=0}

    out = "<form action=\"javascript:void(null);\" method=\"post\" name=\"amfrm\" id=\"amfrm\" onKeyPress=\"if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {submitDialogueForm()}\" onsubmit=\"submitDialogueForm()\">\
<input type=\"hidden\" id=\"action\" name=\"action\" value=\"answer\">\
<input type=\"hidden\" id=\"po_id\" name=\"po_id\" value=\"" + num + "\">\
<input type=\"hidden\" id=\"po_commentid\" name=\"po_commentid\" value=\"" + commentid + "\">\
<input type=\"hidden\" id=\"prj_id\" name=\"prj_id\" value=\"<?=$prj_id?>\">\
<table width=\"96%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\
<tr>\
	<td colspan=\"2\" style=\"padding-bottom:4px;\">Сообщение:</td>\
<\/tr>\
<tr>\
	<td colspan=\"2\" style=\"padding-bottom:4px;\"><textarea tabindex=\"1\" id=\"po_text\" name=\"po_text\" rows=\"4\" style=\"width: 100%;padding-left:2px;\" onkeydown=\"document.getElementById('po_text_msg').innerHTML = '';\"></textarea><div id=\"po_text_msg\"></div></td>\
<\/tr>\
<tr>\
	<td><input type=\"button\" name=\"resetbtn\" id=\"resetbtn\" value=\"Отменить\" onClick=\"resetfld('"+num+"');\" tabindex=\"3\"></td>\
	<td align=\"right\"><input type=\"submit\" name=\"savebtn\" id=\"savebtn\" value=\"Публиковать\" tabindex=\"2\"></td>\
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
	  xajax_ReadOfferDialogue(num, <?=$project['id']?>);
	}
}

function answer(num, commentid) {
    setInterval("check_com_text()", 10);
    $('po_dialogue_talk_' + num).style.display = 'block';
    $('po_comments_' + num).className = 'po_comments';
    td = $('po_dialogue_answer_'+num);
    if ((old_num > 0) && (old_num != num)){
        resetfld(old_num)
    }
    td.innerHTML = GetForm(num, commentid);
    old_num = num;
    if (commentid) {
	$('po_text').value = $('po_comment_original_' + commentid).innerHTML.replace(/&amp;/gi, '&').replace(/<br>/gi, '\n');
	$('savebtn').value = "Сохранить";
    }
    $('po_text').focus();
}

var edit_block = new Array();
var last_commentid = 0;

function resetfld(num){
    $('po_dialogue_talk_' + num).style.display = 'none';
    $('po_comments_' + num).className = 'po_comments_hide';
    td1 = $('po_dialogue_answer_'+num);
    innerHTML = '';
	if (dialogue_count['num'] > 1) {
        innerHTML = innerHTML + '<span style="float: right;"><a href="javascript:void(null)" onClick="dialogue_toggle(' + num + ');markRead(' + num + ');" class="internal" id="toggle_dialogue_' + num + '">Развернуть всю переписку</a> ' + dialogue_count[num] + '</span>';
	}
    innerHTML = innerHTML + '<span><a href="javascript:void(0);" onClick="answer(' + num + ');markRead(' + num + ');" class="internal">Написать ответ</a></span>';
    if (edit_block[num]) innerHTML = innerHTML + edit_block[num];

    td1.innerHTML = innerHTML;
}

function submitDialogueForm()
{
    if ($('po_text').value == '') {
        return false;
    }
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
        e.style.display = 'none';
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
		  el_div.className = 'po_comments_hide';
	}
	else
	{
		el_top.innerHTML = 'Свернуть переписку';
		if(el_div)
  		el_div.className = 'po_comments';
	}
}

function add_work(num, pict, prev)
{
    last_work = last_work + 1;

    work = $('td_pic[' + last_work + ']');
    work_pict = $('ps_work_pict[' + last_work + ']');
    work_prev = $('ps_work_prev_pict[' + last_work + ']');

    obj_works_add = $('works_add[' + last_work + ']');
    //  obj_works_no_select = $('works_no_select_' + last_work);

    if (work_pict.value == '')
    {
        work.className = 'pic';
        if (prev != '') // есть превью
        {
            work.innerHTML = '<div align="left"><a href="<?=WDCPREFIX?>/users/<?=get_login(get_uid())?>/upload/' + pict + '" target="_blank" class="blue" title=""><img src="<?=WDCPREFIX?>/users/<?=get_login(get_uid())?>/upload/' + prev + '" alt="" border="0"></a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(' + last_work + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
        }
        else
        {
            work.innerHTML = '<div align="left" style="font-size:100%;"><a href="<?=WDCPREFIX?>/users/<?=get_login(get_uid())?>/upload/' + pict + '" target="_blank" class="blue" title="">' + pict + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(' + last_work + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
        }
        work_pict.value = pict;
        work_prev.value = prev;
    }
    add_work_place(last_work);
}

function clear_work(num)
{
    //  $('td_pic[' + num + ']').remove();
    $('work_place[' + num + ']').remove();
}

function add_work_place(num)
{
    new_num = num + 1;
    var html = "";
    html += "<input id=\"ps_work_pict[" + new_num + "]\" name=\"ps_work_pict[" + new_num + "]\" type=\"hidden\" value=\"\" />";
    html += "<input id=\"ps_work_prev_pict[" + new_num + "]\" name=\"ps_work_prev_pict[" + new_num + "]\" type=\"hidden\" value=\"\" />";
    html += "<table class=\"works\" width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
    html += "<tr>";
    html += "<td id=\"td_pic[" + new_num + "]\">&nbsp;</td>";
    html += "</tr>";
    html += "</table>";

    var ne = new Element('div');
    ne.id = 'work_place[' + new_num + ']';
    ne.setHTML(html);
    ne.injectAfter('work_place[' + num + ']');
}

function dialogue_toggle(num) {
	el_top = $('toggle_dialogue_' + num);
	el_div = $('po_comments_' + num);
	el_tlk = $('po_dialogue_talk_' + num);
	if(el_div) {
  	if (el_top.innerHTML == 'Свернуть диалог')
  		el_div.className = 'po_comments';
  	else
  		el_div.className = 'po_comments_hide';
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

//-->
</script>
<?
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
	<td colspan="2">
        <h1 class="b-page__title" id="prj_name_<?=$project['id']?>"><?=$sBox1?><?=reformat($sTitle,30,0,1); ?></h1>
        <?php include(dirname(__FILE__).'/only_pro_verify.inc.php') ?>
	</td>
</tr>
<tr valign="top">
	<td>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr valign="top">
			<td colspan="3" bgcolor="#FFFFFF" class="box" style="padding: 0 19px 19px 19px; color: #000000; font-size:12px;">&nbsp;
      <div style="text-align:right;padding-bottom:15px">
        <a href="<?=$back_href?>" class="blue"><b>[Назад]</b></a>
      </div>
  		<table width="100%" border="0" cellspacing="0" cellpadding="0">
  		<tr valign="top">
  			<td width="60"><?=($show_info)?view_avatar($project['login'], $project['photo']):"<img src=\"/images/user-default-small.png\" alt=\"\" width=\"50\" height=\"50\" class=\"lpl-avatar\">"?></td>
  			<td class="bl_text">
  			<?
  			/*
  			if ($project->name && !strcmp($blog->login,"Anonymous") && $gr_id == 3){
  			preg_match("/^([^(@@@:)]*)@@@: ([^\s]*)/", $project->name, $matches);
  			$blog->uname = $matches[1];
  			$blog->msgtext = "<a href=\"mailto:".$blog->msgtext."\">".$matches[2]."</a><br>".$blog->msgtext;
  			$blog->title = "";
  			}
  			*/
          ?>
    			<div class="prj_cost">Бюджет: <?=CurToChar($project['cost'], $project['currency'])?><?
    			if ($project['cost'] > 0) {
    			?><br />
    			<table class="small" style="margin-left:52px;color:#999999;">
    			<tr>
    			<td style="text-align:right;vertical-align:top;">Это:</td>
    			<td style="text-align:left;vertical-align:top;padding-left:7px;"><?
    			if ($project['currency'] != 0) {
    			    ?><?=CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '2'], 2))), 0)?><br><?
    			}
    			if ($project['currency'] != 1) {
    			    ?><?=CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '3'], 2))), 1)?><br><?
    			}
    			if ($project['currency'] != 2) {
    			    ?><?=CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '4'], 2))), 2)?><br><?
    			}
    			if ($project['currency'] != 3) {
    			    ?><?=CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '1'], 2))), 3)?><br><?
    			}
    			?></td>
    			</tr>
    			</table>
    			<? } ?>
    			</div>
		        <?if ($show_info){?>
    			<?= view_mark_user($project, '', false);?><?=$session->view_online_status($project['login'])?>
    			<font class="empname11"><a href="/users/<?=$project['login']?>" class="empname11" title="<?=($project['uname']." ".$project['usurname'])?>"><?=($project['uname']." ".$project['usurname'])?></a> [<a href="/users/<?=$project['login']?>" class="empname11" title="<?=$project['login']?>"><?=$project['login']?></a>]</font>
                        <?}else{?>
                        <?=$session->view_online_status($project['login'])?> Автор: <? if ($project['is_pro'] == 't') { ?><?=(is_emp($project['role'])?view_pro_emp():view_pro())?><? } ?>
                        <?
                              $emp_user = new users;
		              $emp_user->GetUser($project['login']);
                              echo "на сайте ".ElapsedMnths(strtotime($emp_user->reg_date)).", ";
                              echo "<span class=\"r_positive\">+&nbsp;".(int)$emp_user->ops_plus."</span>&nbsp;/&nbsp;<span class=\"r_neutral\">".(int)$emp_user->ops_null."</span>&nbsp;/&nbsp;<span class=\"r_negative\">-&nbsp;".(int)$emp_user->ops_minus."</span>, ";
                              require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
                              $sbr_info = sbr_meta::getUserInfo($emp_user->uid);
                              echo (int)$sbr_info['completed_cnt']." СБР";
                        ?>
                        <?}?>
          <?=dateFormat("[d.m.Y | H:i]", $project['create_date'])?>
          <?=(($project['edit_date'])?dateFormat("[внесены изменения: d.m.Y | H:i]", $project['edit_date']):"")?>
          <? 
          if(hasPermissions('projects') && ($project['ico_payed']=='t' || $project['is_upped'] == 't')) { ?>
             <b style="color:#ff0000"><nobr>Внимание! Это платный проект!</nobr></b>
    			<? }
    			if ($project['name']) {
    			    $sBox1 = '';
    			    if (intval($project['sbr_id'])) $sBox1 .= "<a href=\"/".sbr::NEW_TEMPLATE_SBR."/".($project['sbr_emp_id']==$uid||$project['sbr_frl_id']==$uid ? "?id={$project['sbr_id']}" : '')."\" title=\"Безопасная Сделка\"><img src=\"/images/shield_sm.gif\" alt=\"Безопасная Сделка\" style='vertical-align: middle;margin: 0px 8px 4px 0px;' /></a>";
    			    if ($project['ico_closed'] == "t")  $sBox1 .= "<a href=\"/about/prjrules/\" title=\"Проект закрыт\"><img src=\"/images/ico_closed.gif\" alt=\"Проект закрыт\" style='vertical-align: middle;margin: 0px 8px 4px 0px;' /></a>";
    			    ?><h2 class="b-page__title"><?=$sBox1?><?=reformat($project['name'],30,0,1); ?></h2><?
    			}
    			?>
    			<div class="prj_text" id="projectp<?=$project['id']?>"><?=reformat($project['descr'], 70, 0, 0, 1)?></div>
    			</td>
    		</tr>
            <?
            if ($project['attach'])
            {
                $str = viewattachLeft($project['login'], $project['attach'], "upload", $file, 1000, 600, 307200, $project['attach'], 0, 0);
                print("<tr><td>&nbsp;</td><td><br>".$str."<br></td></tr>");
            }
            elseif ( isset($project_attach) && is_array($project_attach) ) {
                ?>
                <tr>
                    <td>&nbsp;</td>
                    <td style="font-size:11px;padding-top:8px;vertical-align:middle;">
                        <div class="attachments attachments-p">
                <?php
                $nn = 1;
            	foreach ( $project_attach as $attach )
            	{
            		$str = viewattachLeft( NULL, $attach["name"], $attach['path'], $file, 0, 0, 0, 0, 0, 0, $nn );
            		echo '<div class = "flw_offer_attach">', $str, '</div>';
                    $nn++;
            	}
            	?>
            	       </div>
                    </td>
            	</tr>
            	<?php
            }
		    ?>
    		</table>
            <div style="font-size:11px;color:#666666;text-align:right;padding:0 0 5px 0">Раздел: <?=projects::getSpecsStr($project['id'],'<strong> / </strong>');?></div>
            <div id="warnreason-p<?=$project['id']?>" style="display:none; margin-bottom: 5px;">&nbsp;</div>
            <div id="project-reason-<?=$project['id']?>" style="margin-top: 10px;<?=($project['is_blocked']? 'display: block': 'display: none')?>"><? 
                if ($project['is_blocked']) {
					$moder_login = (hasPermissions('projects'))? $project['admin_login']: '';
                    print HTMLProjects::BlockedProject($project['blocked_reason'], $project['blocked_time'], $moder_login, "{$project['admin_name']} {$project['admin_uname']}");
				} else {
					print '&nbsp;';
				}
            ?></div>
<?
$sBox = '';
if (hasPermissions('projects') && $project['login']!=$_SESSION["login"]) {
    if ( $project['warn']<=3 && !$project['is_banned'] && !$project['ban_where'] ) {
        $sBox .= "<span class='warnlink-{$project['user_id']}'><a style='color: red;' href='javascript: void(0);' onclick='banned.warnUser({$project['user_id']}, 0, \"projects\", \"p{$project['id']}\", 0); return false;'>Сделать предупреждение (<span class='warncount-{$project['user_id']}'>".($project['warn'] ? $project['warn'] : 0)."</span>)</a></span> | ";
    }
    else {
        $sBanTitle = (!$project['is_banned'] && !$project['ban_where']) ? 'Забанить!' : 'Разбанить';
        $sBox .= '<span class="warnlink-'.$project['user_id'].'"><a style="color:Red;" href="javascript:void(0);" onclick="banned.userBan('.$project['user_id'].', \'p'.$project['id'].'\',0)">'.$sBanTitle.'</a></span> | ';
    }
}
if (hasPermissions('projects') && $project['login']!=$_SESSION["login"]) {
    $sBox.="<a href=\"/public/?step=1&public=".$project['id']."&red=".rawurlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'])."\">Редактировать</a> | ";
    $sBox.='<span id="project-button-'.$project['id'].'"><a style="color:Red;" href="javascript: void(0);" onClick="banned.'.($project['is_blocked']? 'unblockedProject': 'blockedProject').'('.$project['id'].')">'.($project['is_blocked']? 'Разблокировать': 'Заблокировать')."</a></span>";
}
if ($sBox != '') {
        ?>
            <div style="text-align:right;font-size:11px;"><?=$sBox?></div>
        <? }
?>
			</td>
		</tr>
		</table>
	</td>
</tr>

<tr valign="top">
	<td colspan="2" style="padding:18px 0 12px 0;">
		 <a name="offers"></a><h2 class="offer_project">Предложения по проекту</h2>
	</td>
</tr>
<tr valign="top">
	<td>

        <?
            $offers_count = count($offers) - 1;
            if ((get_uid() > 0) || (isset($offers) && is_array($offers) && ($offers_count >= 0))) {
        ?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 16px;">
		<tr valign="top">
			<td colspan="3" bgcolor="#FFFFFF" class="box" style="padding: 6px 114px 0px 64px;">
		<? } ?>
			<? if (!$user_offer_exist) { // Нет предложений от данного юзера. ?>
			  <? if ((get_uid() > 0) && (($project['closed'] == 'f'))) { // Юзер авторизован и проект открыт. ?>
  			<h1>Сделайте предложение по проекту:</h1>
  			<form id="form_add_offer" name="form_add_offer" action="/projects/?pid=<?=$prj_id?>" method="POST" onKeyPress="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {this.submit()}">
            <input name="hash" type="hidden" value="<?=$hash?>" />
            <input id="ps_action" name="action" type="hidden" value="add" />
            <input id="ps_pid" name="pid" type="hidden" value="<?=$prj_id?>" />
            <input name="f" type="hidden" value="<?=$from_prm?>" />
            <input name="u" type="hidden" value="<?=$from_usr?>" />
    		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:8px;">
    		<tr valign="top">
    			<td style="padding-right:16px;padding-bottom:6px;font-weight:bold;">Комментарий</td>
    			<td style="padding-right:6px;padding-bottom:6px;"></td>
    			<td style="width:100%;padding-right:6px;padding-bottom:6px;" colspan="4">
                <textarea cols="60" rows="7" id="ps_text" name="ps_text" style="width:99%;height:100px;" onkeydown="msg.innerHTML = '';"><?=input_ref($ps['text'])?></textarea>
                <div id="ps_text_msg"></div>
    			</td>
    		</tr>
    		<tr valign="top">
    			<td style="padding-right:16px;padding-bottom:6px;font-weight:bold;"></td>
    			<td style="padding-right:6px;padding-bottom:6px;"></td>
    			<td style="width:100%;padding-right:6px;padding-bottom:6px;" colspan="4">
            <div id="work_place[1]">
              <input id="ps_work_pict[1]" name="ps_work_pict[1]" type="hidden" value="<?=$ps['portfolio_work']?>" />
              <input id="ps_work_prev_pict[1]" name="ps_work_prev_pict[1]" type="hidden" value="<?=$ps['portfolio_work_prev_pict']?>" />
          		<table class="works" width="100%" border="0" cellspacing="0" cellpadding="0">
          		<tr>
          			<td id="td_pic[1]">&nbsp;
                  
          			</td>
          		</tr>
          		</table>
        		</div>
    			</td>
    		</tr>
    		</table>
        <?/* <div id="works_no_select" style="font-size:11px;font-weight:bold;padding-top:16px;padding-left:100px;display:none;color:#666666;">Чтобы добавить другую работу удалите загруженную</div> */?>
    		<table id="works_add" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:8px;">
    		<tr valign="top">
    			<td style="padding-right:16px;padding-bottom:6px;font-weight:bold;">Присоедините конкурсную работу</td>
    			<td style="padding-right:6px;padding-bottom:6px;"></td>
    			<td style="width:100%;padding-right:6px;padding-bottom:6px;" colspan="4">
                <iframe style="width:626px;height:40px;" scrolling="no" id="fupload" name="fupload" src="/projects/upload.php?pid=<?=$prj_id?>" frameborder="0"></iframe><br />
                С помощью этого поля возможно загрузить файл.<br />
                Максимальный размер файла: 2 Мб.<br />
                Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
    			</td>
    		</tr>
    		</table>
    		<table id="works_submit" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:8px;">
    		<tr valign="top">
    			<td style="padding-top:16px;padding-right:6px;padding-bottom:24px;font-weight:bold;">
            <a id="ps_add" class="b-button  b-button_flat b-button_flat_green" href="javascript: void(0)" onclick="this.disabled=true;$('form_add_offer').submit();">Добавить предложение</a>
    			</td>
    		</tr>
    		</table>
    		</form>
    		<?
                }
            }
			else // Предложение от данного юзера уже есть.
			{
	            if ($value['user_id'] == $project['exec_id']) {
			?><a name="winner"></a><?
	            }
            ?>
      		<table class="portfolio" style="margin-top:18px;<? if ((!$user_offer_exist && count($offers) > 0) || ($user_offer_exist && count($offers) > 1)) { ?>border-bottom:1px #c6c6c6 solid;<? } ?>">
        		<tr valign="top">
              <td width="60"><?=view_avatar($user_offer['login'], $user_offer['photo'])?></td>
        			<td>
                <table style="width:100%;border:0px">
                <tr>
                <td>
          			<?= view_mark_user($user_offer);?><?=$session->view_online_status($user_offer['login'])?>
          			<span class="frlname11"><a href="/users/<?=$user_offer['login']?>" class="frlname11" title="<?=($user_offer['uname']." ".$user_offer['usurname'])?>"><?=($user_offer['uname']." ".$user_offer['usurname'])?></a> [<a href="/users/<?=$user_offer['login']?>" class="frlname11" title="<?=$user_offer['login']?>"><?=$user_offer['login']?></a>]</span><br />
                <? if ($user_offer['spec_name'] != '') { ?>Специализация: <?=$user_offer['spec_name']?><? } ?><br />
                Рекомендации работодателей: <strong class="r_positive"> + <?=(int) $user_offer['sbr_opi_plus']?></strong> / <strong> <?=(int) $user_offer['sbr_opi_null']?></strong> / <strong class="r_negative"> - <?=(int) $user_offer['sbr_opi_minus']?></strong><br />
                <? if ($user_offer['country_name'] != 'Не определено') { ?><?=$user_offer['country_name']?><? if ($$user_offer['city_name'] != 'Не определено') { ?><? if ($user_offer['country_name'] && $user_offer['city_name']) { ?>, <? } ?><?=$user_offer['city_name']?><br /><? } } ?>
                </td>
                <td style="text-align:center;vertical-align:top;">
                <?
                $txt_time = view_range_time($user_offer['time_from'], $user_offer['time_to'], $user_offer['time_type']);
                ?>
                <h1 style="margin-bottom:0px;"><?=$txt_time?></h1>
                </td>
                <td style="text-align:right;vertical-align:top;"><?
                $txt_cost = view_range_cost($user_offer['cost_from'], $user_offer['cost_to'], '', '', false);
                if ($txt_cost != '') { ?><span style="text-align:left;padding:0px;margin:0px;">
                <h1 style="margin:0px;text-align:right;"><?=$txt_cost?></h1>
                </span>
                <? } ?>
                </td>
                </tr>
                </table>
                <? if ($project['exec_id'] == get_uid()) { ?>
                <div class="po_exec">
                    <strong>Вы победитель</strong><br />
                    Заказчик Вас определил как победителя по этому проекту.<br /><br />
                </div>
                <?  } elseif ($user_offer['selected'] == 't') { ?>
          			<div class="po_selected">
                  <strong>Вы кандидат</strong><br />
                  Заказчик Вас определил как кандидата по этому проекту.<br />
                  Это значит, что Вы прошли предварительный отбор. Может быть, Вы будете выбраны исполнителем проекта.
          			</div>
          			<? } elseif ($user_offer['refused'] == 't') { ?>
          			<div class="po_refused">
                  <strong>Вы получили отказ</strong><br />
                  Ваше предложение не подошло заказчику. Причина &mdash; <?
                  switch ($user_offer['refuse_reason'])
                  {
                      default:
                      case 0:
                          ?>некорректность<?
                          break;
                      case 1:
                          ?>не подходят работы<?
                          break;
                      case 2:
                          ?>не подходит цена<?
                          break;
                      case 3:
                          ?>не указана<?
                          break;
                      case 4:
                          ?>выбран другой исполнитель<?
                          break;
                  }
                  ?>.<br /><br />
          			</div>
          			<? } ?>

          			<? if (isset($user_offer['dialogue']) && is_array($user_offer['dialogue']) && (count($user_offer['dialogue']) > 0)) { ?>
          			<div class="po_comments_<? if ($user_offer['frl_new_msg_count'] > 0) { ?>new_<? } ?>hide" id="po_comments_<?=$user_offer['id']?>"><?
          			if ($user_offer['frl_new_msg_count'] > 0) {
          			    ?><span id="new_msgs_<?=$user_offer['id']?>" style="float: right;"><img src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0"> <?=$user_offer['frl_new_msg_count']?> <?=ending($user_offer['frl_new_msg_count'], 'новое сообщение', 'новых сообщения', 'новых сообщений')?></span><?
          			}
          			$i = 0;
          			$dc = count($user_offer['dialogue']);
          			if ($dc == 1)
          			{
                ?>
            			<div style="margin-bottom:8px;font-size:100%;">
              			<span class="<?=is_emp($user_offer['dialogue'][0]['role'])?'emp':'frl'?>name11"><a href="/users/<?=$user_offer['dialogue'][0]['login']?>" class="<?=is_emp($user_offer['dialogue'][0]['role'])?'emp':'frl'?>name11" title="<?=($user_offer['dialogue'][0]['uname']." ".$user_offer['dialogue'][0]['usurname'])?>"><?=($user_offer['dialogue'][0]['uname']." ".$user_offer['dialogue'][0]['usurname'])?></a> [<a href="/users/<?=$user_offer['dialogue'][0]['login']?>" class="<?=is_emp($user_offer['dialogue'][0]['role'])?'emp':'frl'?>name11" title="<?=$user_offer['dialogue'][0]['login']?>"><?=$user_offer['dialogue'][0]['login']?></a>]</span> <span id="po_date_<?=$comment['id']?>"><?=dateFormat("[d.m.Y | H:i]", $user_offer['dialogue'][0]['post_date'])?></span><br />
              			<div id="po_comment_<?=$comment['id']?>"><?=reformat(trim(strip_tags($user_offer['dialogue'][0]['post_text'])), 100)?></div>
       			        <div id="po_comment_original_<?=$comment['id']?>" style="display:none;"><?=reformat(trim(strip_tags($user_offer['dialogue'][0]['post_text'])), 1000, 0, 1)?></div>
            			</div>
            			<div id="po_dialogue_talk_<?=$user_offer['id']?>" style="font-size:12px;visibility:visible;height:auto;overflow:visible;display:none;">
            			</div>
<?/*
                <script type="text/javascript">
                <!--
                var podSlider<?=$user_offer['id']?> = new Fx.Slide('po_dialogue_talk_<?=$user_offer['id']?>', {duration: 500, transition: Fx.Transitions.quadOut});
                $('po_dialogue_talk_<?=$user_offer['id']?>').style.visibility = "visible";
                $('po_dialogue_talk_<?=$user_offer['id']?>').style.height = "auto";
                podSlider<?=$user_offer['id']?>.addEvent('onStart',function() {toggle_link_text(<?=$user_offer['id']?>);});
                podSlider<?=$user_offer['id']?>.hide();
                //-->
                </script>
*/?>
            			<div id="po_dialogue_answer_<?=$user_offer['id']?>" style="font-size:100%;margin:16px 0px 6px 0px;">
                    	<? if (($user_offer['refused'] == 'f') && ($project['closed'] == 'f')) { ?>
                        	<? if (count($user_offer['dialogue']) > 1) { ?>
            			<span style="float: right;"><a href="javascript:void(null)" onClick="dialogue_toggle(<?=$user_offer['id']?>);markRead('<?=$user_offer['id']?>');" class="internal" id="toggle_dialogue_<?=$user_offer['id']?>">Развернуть всю переписку</a> <?=count($user_offer['dialogue'])?></span>
                			<? } ?>
            			<span><a href="javascript:void(0);" onClick="answer(<?=$user_offer['id']?>);markRead('<?=$user_offer['id']?>');" class="internal">Написать ответ</a></span>

                          			<?
                          			if ($comment['user_id'] == get_uid() && count($user_offer['dialogue']) > 1){
                          			?>

                          			&nbsp;<span><a href="javascript:void(null)" onClick="answer(<?=$user_offer['id']?>, <?=$comment['id']?>);markRead('<?=$user_offer['id']?>');" class="internal">Редактировать</a></span>
                          			<SCRIPT language="javascript">
						last_commentid = <?=$comment['id']?>;
                          			edit_block[<?=$user_offer['id']?>] = '&nbsp;&nbsp;<span><a href="javascript:void(null)" onClick="answer(<?=$user_offer['id']?>, last_commentid);markRead(\'<?=$user_offer['id']?>\');" class="internal">Редактировать</a></span>';
                          			</SCRIPT>

                          			<?
                          			}
                          			?>


              			<? } else { ?>
                        	<? if (count($user_offer['dialogue']) > 1) { ?>
            			<span style="float: right;"><a href="javascript:void(null)" onClick="dialogue_toggle(<?=$user_offer['id']?>);markRead('<?=$user_offer['id']?>');" class="internal" id="toggle_dialogue_<?=$user_offer['id']?>">Развернуть всю переписку</a> <?=count($user_offer['dialogue'])?></span>
                			<? } ?>
            			<? } ?>
            			</div>
            	  <?
          			}
          			else
          			{
          			    foreach ($user_offer['dialogue'] as $key => $comment)
          			    {
          			        if ($i == 1)
          			        {
            			?>
            			<div id="po_dialogue_talk_<?=$user_offer['id']?>" style="font-size:12px;visibility:visible;height:auto;overflow:visible;display:none;">
            			<?
          			        }
          			        $i++;
            			?>
            			<div style="margin-bottom:8px;font-size:100%;">
              			<span class="<?=is_emp($comment['role'])?'emp':'frl'?>name11"><a href="/users/<?=$comment['login']?>" class="<?=is_emp($comment['role'])?'emp':'frl'?>name11" title="<?=($comment['uname']." ".$comment['usurname'])?>"><?=($comment['uname']." ".$comment['usurname'])?></a> [<a href="/users/<?=$comment['login']?>" class="<?=is_emp($comment['role'])?'emp':'frl'?>name11" title="<?=$comment['login']?>"><?=$comment['login']?></a>]</span> <span id="po_date_<?=$comment['id']?>"><?=dateFormat("[d.m.Y | H:i]", $comment['post_date'])?></span><br />
              			<div id="po_comment_<?=$comment['id']?>"><?=reformat(trim(strip_tags($comment['post_text'])), 100)?></div>
       			        <div id="po_comment_original_<?=$comment['id']?>" style="display:none;"><?=reformat(trim(strip_tags($comment['post_text'])), 1000, 0, 1)?></div>
            			</div>
                  <?
                  if ($i == $dc)
                  {
            			?>
            			</div>
<?/*
            			<script type="text/javascript">
            			<!--
            			var podSlider<?=$user_offer['id']?> = new Fx.Slide('po_dialogue_talk_<?=$user_offer['id']?>', {duration: 500, transition: Fx.Transitions.quadOut});
            			$('po_dialogue_talk_<?=$user_offer['id']?>').style.visibility = "visible";
            			$('po_dialogue_talk_<?=$user_offer['id']?>').style.height = "auto";
            			podSlider<?=$user_offer['id']?>.addEvent('onStart',function() {toggle_link_text(<?=$user_offer['id']?>);});
            			podSlider<?=$user_offer['id']?>.hide();
            			//-->
            			</script>
*/?>
                  <?
                  }
          			    }
                        ?>
            			<div id="po_dialogue_answer_<?=$user_offer['id']?>" style="font-size:100%;margin:16px 0px 6px 0px;">
                    	<? if (($user_offer['refused'] == 'f') && ($project['closed'] == 'f')) { ?>
                        	<? if (count($user_offer['dialogue']) > 1) { ?>
            			<span style="float: right;"><a href="javascript:void(null)" onClick="dialogue_toggle(<?=$user_offer['id']?>);markRead('<?=$user_offer['id']?>');" class="internal" id="toggle_dialogue_<?=$user_offer['id']?>">Развернуть всю переписку</a> <?=count($user_offer['dialogue'])?></span>
                			<? } ?>
            			<span><a href="javascript:void(0);" onClick="answer(<?=$user_offer['id']?>);markRead('<?=$user_offer['id']?>');" class="internal">Написать ответ</a></span>

                          			<?
                          			if ($comment['user_id'] == get_uid() && count($user_offer['dialogue']) > 1){
                          			?>

                          			&nbsp;<span><a href="javascript:void(null)" onClick="answer(<?=$user_offer['id']?>, <?=$comment['id']?>);markRead('<?=$user_offer['id']?>');" class="internal">Редактировать</a></span>
                          			<SCRIPT language="javascript">
                          			edit_block[<?=$user_offer['id']?>] = '&nbsp;&nbsp;<span><a href="javascript:void(null)" onClick="answer(<?=$user_offer['id']?>, <?=$comment['id']?>);markRead(\'<?=$user_offer['id']?>\');" class="internal">Редактировать</a></span>';
                          			</SCRIPT>

                          			<?
                          			}
                          			?>


              			<? } else { ?>
                        	<? if (count($user_offer['dialogue']) > 1) { ?>
            			<span style="float: right;"><a href="javascript:void(null)" onClick="dialogue_toggle(<?=$user_offer['id']?>);markRead('<?=$user_offer['id']?>');" class="internal" id="toggle_dialogue_<?=$user_offer['id']?>">Развернуть всю переписку</a> <?=count($user_offer['dialogue'])?></span>
                			<? } ?>
             			<span>&nbsp;</span>
            			<? } ?>
				</div>
          			<? } ?>
            			</div>
                <? } ?>

                <? if (count($user_offer['attach']) > 0) { ?>
          			<table width="100%" border="0" cellspacing="0" cellpadding="2" class="n_qpr" style="margin-bottom:12px;">
                  <?
                  if (isset($user_offer['attach']) && is_array($user_offer['attach'])) {
                      foreach ($user_offer['attach'] as $key => $value) { ?>
                    <tr valign="top" class="qpr">
                      <td align="left" style="vertical-align:top;padding:12px 12px 8px 0px;">
                        <div style="width:200px;">
                          <? if (in_array(CFile::getext($value['pict']), $GLOBALS['graf_array']) || strtolower(CFile::getext($value['pict'])) == "mp3") { ?>
                              <? if ($value['prev'] != '') { ?>
                          <div style="text-align:left"><a href="/projects/viewwork.php?pid=<?=$user_offer['project_id']?>&user=<?=$user_offer['login']?>&wid=<?=$value['id']?>" target="_blank" class="blue" title="" style="text-decoration:none"><?=view_preview($user_offer['login'], $value['prev'], "upload", $align)?></a></div>
                              <? } elseif ($value['pict'] != '') { ?>
                          <div style="text-align:left;font-size:11px;">
                            <? if (strtolower(CFile::getext($value['pict'])) == "mp3") { ?>
                            <img src="/images/ico_mp3.gif" alt="загрузить" width="32" height="32" border="0" style="vertical-align: middle;padding-right: 8px;" />
                            <? } ?>
                            <a href="/projects/viewwork.php?pid=<?=$user_offer['project_id']?>&user=<?=$user_offer['login']?>&wid=<?=$value['id']?>" target="_blank" class="blue" title="">открыть</a></div>
                              <? } ?>
                          <? } else { ?>
                              <? if ($value['prev'] != '') { ?>
                          <div style="text-align:left"><a href="<?=WDCPREFIX?>/users/<?=$user_offer['login']?>/upload/<?=$value['pict']?>" target="_blank" class="blue" title="" style="text-decoration:none"><?=view_preview($user_offer['login'], $value['prev'], "upload", $align)?></a></div>
                              <? } elseif ($value['pict'] != '') { ?>
                          <div style="text-align:left;font-size:11px;"><a href="<?=WDCPREFIX?>/users/<?=$user_offer['login']?>/upload/<?=$value['pict']?>" target="_blank" class="blue" title=""><?=$value['pict']?></a></div>
                              <? } ?>
                          <? } ?>
                        </div>
                      </td>
                  	</tr>
                  <?
                      }
                  }
                  ?>
            		</table>
                <? } ?>

    			  <? if ((get_uid() > 0) && (($project['closed'] == 'f')) && ($user_offer['refused'] != 't')) { // Юзер авторизован, проект открыт и ему не отказано. ?>
      			<h1>Добавьте работы к вашему предложению:</h1>
      			<form id="form_add_offer" name="form_add_offer" action="/projects/?pid=<?=$prj_id?>" method="POST">
                    <input name="hash" type="hidden" value="<?=$hash?>" />
                    <input id="ps_action" name="action" type="hidden" value="change" />
                    <input id="ps_pid" name="pid" type="hidden" value="<?=$prj_id?>" />

        			<div id="work_place[<?=count($user_offer['attach']) + 1?>]">
                <input id="ps_work_pict[<?=count($user_offer['attach']) + 1?>]" name="ps_work_pict[<?=count($user_offer['attach']) + 1?>]" type="hidden" value="" />
                <input id="ps_work_prev_pict[<?=count($user_offer['attach']) + 1?>]" name="ps_work_prev_pict[<?=count($user_offer['attach']) + 1?>]" type="hidden" value="" />
            		<table class="works" width="100%" border="0" cellspacing="0" cellpadding="0">
            		<tr>
            			<td id="td_pic[<?=count($user_offer['attach']) + 1?>]"></td>
            		</tr>
            		</table>
          		</div>
              		
              <table id="works_add" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:8px;">
            		<tr valign="top">
            			<td style="padding-right:16px;padding-bottom:6px;font-weight:bold;">Присоедините конкурсную работу</td>
            			<td style="padding-right:6px;padding-bottom:6px;"></td>
            			<td style="width:100%;padding-right:6px;padding-bottom:6px;" colspan="4">
                        <iframe style="width:626px;height:40px;" scrolling="no" id="fupload" name="fupload" src="/projects/upload.php?pid=<?=$prj_id?>" frameborder="0"></iframe><br />
                        С помощью этого поля возможно загрузить файл.<br />
                        Максимальный размер файла: 2 Мб.<br />
                        Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
            			</td>
            		</tr>
           		</table>
          		<table id="works_submit" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:8px;">
          		<tr valign="top">
          			<td style="text-align:right;padding-top:16px;padding-right:6px;padding-bottom:24px;font-weight:bold;">
                  <input type="button" id="ps_add" name="ps_add" value="Добавить" onclick="this.disabled=true;$('form_add_offer').submit();" />
          			</td>
          		</tr>
          		</table>
        		</form>
        		<? } ?>
            		
          			<div style="color:#000;font-size:100%;padding:0px 0px 38px 0px"><a href="/users/<?=$user_offer['login']?>/" class="blue"><strong>Все работы <?=$user_offer['uname']." ".$user_offer['usurname']?> [<?=$user_offer['login']?>]</strong></a></div>
					
					<div id="warnreason-o<?=$user_offer['id']?>" style="display:none; margin-bottom: 5px;">&nbsp;</div>	
					
          		</td>
          	</tr>
            <?
                if (hasPermissions('projects')) {
                    if ( $user_offer['warn']<3 && !$project['is_banned'] && !$project['ban_where'] ) {
                        $sBox = "<span class='warnlink-{$user_offer['user_id']}'><a style='color: red;' href='javascript: void(0);' onclick='banned.warnUser({$user_offer['user_id']}, 0, \"projects\", \"p{$project['id']}\", 0); return false;'>Сделать предупреждение (<span class='warncount-{$user_offer['user_id']}'>".($user_offer['warn'] ? $user_offer['warn'] : 0)."</span>)</a></span> | ";
                    } else {
                        $sBanTitle = (!$project['is_banned'] && !$project['ban_where']) ? 'Забанить!' : 'Разбанить';
                        $sBox = '<span class="warnlink-'.$user_offer['user_id'].'"><a style="color:Red;" href="javascript:void(0);" onclick="banned.userBan('.$project['user_id'].', \'p'.$project['id'].'\',0)">'.$sBanTitle.'</a></span> | ';
                    }
                    $sBox .= "<a style=\"color:Red;\" href=\"/projects/?action=deloffer&pid=" . $user_offer['project_id']."&oid=" . $user_offer['id']."\" onclick=\"return warning(2)\">Удалить</a>";
                }
                if ($sBox != '') {
            ?>
           		<tr valign="top">
                    <td colspan="2" style="text-align:right;padding-bottom:12px;"><?=$sBox?></td>
             	</tr>
            <? } ?>
			</table>
        <?
			}
			#        $offers_count = count($offers) - (($user_offer_exist) ? 2 : 1);
			$offers_count = count($offers) - 1;
			if (isset($offers) && is_array($offers) && ($offers_count >= 0))
			{
			    foreach ($offers as $key => $value)
			    {
			        if (!$user_offer_exist || ($value['id'] != $user_offer['id']))
			        {
			            if ($value['user_id'] == $project['exec_id']) {
			?><a name="winner"></a><?
			            }
        ?>
      		<table class="portfolio" style="margin-top:18px;<? if ($key < $offers_count) { ?>border-bottom:1px #c6c6c6 solid;<? } ?>">
        		<tr valign="top">
              <td width="60"><?=view_avatar($value['login'], $value['photo'])?></td>
        			<td style="padding:0px;">
          			<?= view_mark_user($value);?><?=$session->view_online_status($value['login'])?>
          			<span class="frlname11"><a href="/users/<?=$value['login']?>" class="frlname11" title="<?=($value['uname']." ".$value['usurname'])?>"><?=($value['uname']." ".$value['usurname'])?></a> [<a href="/users/<?=$value['login']?>" class="frlname11" title="<?=$value['login']?>"><?=$value['login']?></a>]</span><br />
                <? if ($value['spec_name'] != '') { ?>Специализация: <?=$value['spec_name']?><? } ?><br />
                Рекомендации работодателей: <strong class="r_positive"> + <?=(int) $value['sbr_opi_plus']?></strong> / <strong> <?=(int) $value['sbr_opi_null']?></strong> / <strong class="r_negative"> - <?=(int) $value['sbr_opi_minus']?></strong><br />
                <? if ($value['country_name'] != 'Не определено') { ?><?=$value['country_name']?><? if ($value['city_name'] != 'Не определено') { ?><? if ($value['country_name'] && $value['city_name']) { ?>, <? } ?><?=$value['city_name']?><br /><? } } ?>
          			<div style="color:#000;font-size:115%;padding:12px 0px 8px 0px">
          			<?
                    $dc = count($value['dialogue']);
                    if (hasPermissions('projects') && ($dc > 1)) {
                        $i = 0;
                        foreach ($value['dialogue'] as $key => $comment) {
                            if ($i == 1) {
                                                ?><div id="po_dialogue_talk_<?=$value['id']?>" style="font-size: 12px; visibility: visible; height: auto; overflow: visible; display: none; "><?
                            }
                            $i ++;
                                                    ?><div style="margin-bottom: 8px; font-size: 100%;"><span
                                                    class="<?=is_emp($comment['role']) ? 'emp' : 'frl'?>name11"><a
                                                    href="/users/<?=$comment['login']?>"
                                                    class="<?=is_emp($comment['role']) ? 'emp' : 'frl'?>name11"
                                                    title="<?=($comment['uname'] . " " . $comment['usurname'])?>"><?=($comment['uname'] . " " . $comment['usurname'])?></a>
                                                    [<a href="/users/<?=$comment['login']?>"
                                                    class="<?=is_emp($comment['role']) ? 'emp' : 'frl'?>name11"
                                                    title="<?=$comment['login']?>"><?=$comment['login']?></a>]</span> <?=dateFormat("[d.m.Y | H:i]", $comment['post_date'])?><br />
                                                    <?=reformat(strip_tags($comment['post_text']), 100)?>
                                                </div><?
                            if ($i == $dc) {
                                            ?></div><?
                            }
                        }
                        ?>
                        <span style="float: right;"><a
                        href="javascript:void(null)"
                        onClick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');"
                        class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть
                        всю переписку</a> <?=count($value['dialogue'])?></span><?
                    } else {
                    ?>
                        <?=reformat(strip_tags($value['dialogue'][0]['post_text']), 100)?>
                    <?
                    }
                    ?></div>
          			
                <? if (count($value['attach']) > 0) { ?>
          			<table width="100%" border="0" cellspacing="0" cellpadding="2" class="n_qpr" style="margin-bottom:12px;">
                  <?
                  if (isset($value['attach']) && is_array($value['attach'])) {
                      foreach ($value['attach'] as $key => $work) { ?>
                    <tr valign="top" class="qpr">
                      <td align="left" style="vertical-align:top;padding:12px 12px 8px 0px;">
                        <div style="width:200px;">
                          <? if (in_array(CFile::getext($work['pict']), $GLOBALS['graf_array']) || strtolower(CFile::getext($work['pict'])) == "mp3") { ?>
                              <? if ($work['prev'] != '') { ?>
                          <div style="text-align:left"><a href="/projects/viewwork.php?pid=<?=$value['project_id']?>&user=<?=$value['login']?>&wid=<?=$work['id']?>" target="_blank" class="blue" title="" style="text-decoration:none"><?=view_preview($value['login'], $work['prev'], "upload", $align)?></a></div>
                              <? } elseif ($work['pict'] != '') { ?>
                          <div style="text-align:left;font-size:11px;">
                            <? if (strtolower(CFile::getext($work['pict'])) == "mp3") { ?>
                            <img src="/images/ico_mp3.gif" alt="загрузить" width="32" height="32" border="0" style="vertical-align: middle;padding-right: 8px;" />
                            <? } ?>
                            <a href="/projects/viewwork.php?pid=<?=$value['project_id']?>&user=<?=$value['login']?>&wid=<?=$work['id']?>" target="_blank" class="blue" title="">открыть</a></div>
                              <? } ?>
                          <? } else { ?>
                              <? if ($work['prev'] != '') { ?>
                          <div style="text-align:left"><a href="<?=WDCPREFIX?>/users/<?=$value['login']?>/upload/<?=$work['pict']?>" target="_blank" class="blue" title="" style="text-decoration:none"><?=view_preview($value['login'], $work['prev'], "upload", $align)?></a></div>
                              <? } elseif ($work['pict'] != '') { ?>
                          <div style="text-align:left;font-size:11px;"><a href="<?=WDCPREFIX?>/users/<?=$value['login']?>/upload/<?=$work['pict']?>" target="_blank" class="blue" title=""><?=$work['pict']?></a></div>
                              <? } ?>
                          <? } ?>
                        </div>
                      </td>
                  	</tr>
                  <?
                      }
                  }
                  ?>
            		</table>
                <? } ?>
          			<?
			            if ($value['user_id'] == $project['exec_id']) {
                    /*?><img id="po_img_exec_<?=$value['id']?>" class="po_img_exec" src="/images/b_winner_0.png" style="float: right;" /><?*/
                    ?><div class="po_exec2">
                        <strong>Это победитель</strong><br />
                        Заказчик определил этого фрилансера как победителя по этому проекту.<br /><br />
                    </div><?
			            }
          			?>
          			<div style="color:#000;font-size:100%;padding:0px 0px 38px 0px"><a href="/users/<?=$value['login']?>/" class="blue"><strong>Все работы <?=$value['uname']." ".$value['usurname']?> [<?=$value['login']?>]</strong></a></div>
                    <div id="warnreason-o<?=$value['id']?>" style="display:none; margin-bottom: 5px;">&nbsp;</div>
                </td>
          	</tr>
            <?
            if (hasPermissions('projects')) {
                if ( $value['warn']<3 && !$project['is_banned'] && !$project['ban_where'] ) {
                    $sBox = "<span class='warnlink-{$value['user_id']}'><a style='color: red;' href='javascript: void(0);' onclick='banned.warnUser({$value['user_id']}, 0, \"projects\", \"p{$project['id']}\", 0); return false;'>Сделать предупреждение (<span class='warncount-{$value['user_id']}'>".($value['warn'] ? $value['warn'] : 0)."</span>)</a></span> | ";
                } else {
                    $sBanTitle = (!$project['is_banned'] && !$project['ban_where']) ? 'Забанить!' : 'Разбанить';
                    $sBox = '<span class="warnlink-'.$user_offer['user_id'].'"><a style="color:Red;" href="javascript:void(0);" onclick="banned.userBan('.$project['user_id'].', \'p'.$project['id'].'\',0)">'.$sBanTitle.'</a></span> | ';
                }
                $sBox .= "<a style=\"color:Red;\" href=\"/projects/?action=deloffer&pid=" . $value['project_id']."&oid=" . $value['id']."\" onClick=\"return warning(2)\">Удалить</a>";
            }
            if ($sBox != '') {
            ?>
           		<tr valign="top">
                  <td colspan="2" style="text-align:right;padding-bottom:12px;"><?=$sBox?></td>
             	</tr>
            <? } ?>
        	</table>
            <?
			        }
			    }
    		?>
          		</td>
          	</tr>
        	<tr valign="top">
                <td colspan="3" bgcolor="#FFFFFF" class="box" style="border-top:none;">
            <?php
            // Страницы
            $pagesCount = ceil($num_offers / MAX_OFFERS_AT_PAGE);
            $href = '%s/projects/index.php?pid='. $prj_id;
            if (isset($po_sort)) $href .= '&sort=' . $po_sort;
            if (isset($po_type)) $href .= '&type=' . $po_type;
            $href .= '&page=%d%s';
            if(new_paginator2($item_page, $pagesCount)) {
                echo new_paginator2($item_page, $pagesCount, 3, $href);
            }
            ?>
      		</td>
      	</tr>
		</table>
	</td>
</tr>
</table>
<?
			}
			else {
            $offers_count = count($offers) - 1;
            if ((get_uid() > 0) || (isset($offers) && is_array($offers) && ($offers_count >= 0))) {
?>
      		</td>
      	</tr>
		</table>
<? } ?>
	</td>
</tr>
</table>
    <? if (!$user_offer_exist) { // Нет предложений от данного юзера. ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
	<td colspan="3" bgcolor="#FFFFFF" class="box" style="padding: 48px 64px 48px 64px;">
            Ответов нет.
	</td>
</tr>
</table>
    <? } ?>
<? } ?>
<script language="JavaScript" type="text/javascript">
<!--
if (document.getElementById('ps_text') != null) {
	var msg  = document.getElementById('ps_text_msg');
    var area = document.getElementById('ps_text');
    setInterval("check_text()", 10);
}

function check_text()
{
  	if(area.value.length > 1000)
  	{
  		area.value = area.value.substr(0, 1000);
    	msg.innerHTML = '<? print(ref_scr(view_error('Исчерпан лимит символов для поля (1000 символов)'))); ?>';
  	}
}

function check_com_text() {
    if((document.getElementById('po_text') != undefined) && document.getElementById('po_text').value.length > 1000) {
		document.getElementById('po_text').value = document.getElementById('po_text').value.substr(0, 1000);
        document.getElementById('po_text_msg').innerHTML = '<? print(ref_scr(view_error('Исчерпан лимит символов для поля (1000 символов)'))); ?>';
    }
}
//-->
</script>
<div id="debug"></div>
