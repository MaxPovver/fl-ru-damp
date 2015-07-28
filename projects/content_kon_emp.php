<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects_ci.common.php");
$xajax->printJavascript('/xajax/');

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
$project_exRates = project_exrates::GetAll();
$exch = array(1=>'FM', 'USD','Euro','Руб');

$translate_exRates = array
(
0 => 2,
1 => 3,
2 => 4,
3 => 1
);

$project['exec_po_id'] = 0;
if (isset($offers) && is_array($offers)) {
    foreach ($offers as $key => $value)
    {
        if ($value['user_id'] == $project['exec_id']) {
            $project['exec_po_id'] = $value['id'];
        }
    }
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/HTML/projects_lenta.php");

$user_obj = new users();
$category = professions::GetGroup($project['category'], $eeee);
if($category['name'] && $project['subcategory'])
   $category['name'] .= '&nbsp;/&nbsp;'.professions::GetProfName($project['subcategory']);
?>
<script type="text/javascript">
<!--
var old_num = 0;
var inner = false;
var dialogue_count = new Array(<?=count($offers)?>);

<?
if (isset($offers) && is_array($offers)) {
	foreach ($offers as $key => $value)
	{
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
  	xajax_ReadOfferDialogue(num, <?=$project['id']?>, '<?=$po_type?>');
	}
}

function answer(num, commentid) {
    setInterval("check_com_text()", 10);
    $('po_dialogue_talk_' + num).style.display = 'block';
    $('po_comments_' + num).className = 'po_comments';
    td = $('po_dialogue_answer_' + num);
    if ((old_num > 0) && (old_num != num)) {
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
    if ($('po_dialogue_talk_' + num)) {
        $('po_dialogue_talk_' + num).style.display = 'none';
        $('po_comments_' + num).className = 'po_comments_hide';
        td1 = $('po_dialogue_answer_' + num);
        innerHTML = '';
    	if (dialogue_count[num] > 1) {
            innerHTML = innerHTML + '<span style="float: right;"><a href="javascript:void(null)" onClick="dialogue_toggle(' + num + ');markRead(' + num + ');" class="internal" id="toggle_dialogue_' + num + '">Развернуть всю переписку</a> ' + dialogue_count[num] + '</span>';
        }
        innerHTML = innerHTML + '<span><a href="javascript:void(0);" onClick="answer(' + num + ');markRead(' + num + ');" class="internal">Написать ответ</a></span>';
	if (edit_block[num]) innerHTML = innerHTML + edit_block[num];

        td1.innerHTML = innerHTML;
    }
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
        if (e.style) {
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
		  el_div.className = 'po_comments_hide';
	}
	else
	{
		el_top.innerHTML = 'Свернуть переписку';
		if(el_div)
  		el_div.className = 'po_comments';
	}
}
function buyprj(num){
    document.getElementById('frm').prjid.value = num;
    document.getElementById('frm').action.value = 'prj_buy';
    document.getElementById('frm').submit();
}
function upprj(num){
    document.getElementById('frm').prjid.value = num;
    document.getElementById('frm').action.value = 'prj_up';
    document.getElementById('frm').submit();
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

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr valign="top">
		<td colspan="2">
                    <?php if ($project['ico_closed'] == "t")  $sBox1 .= "<img src=\"/images/ico_closed.gif\" alt=\"Проект закрыт\" style='vertical-align: middle;margin: 0px 8px 4px 0px;'/>"; ?>
            <h1 class="b-page__title" id="prj_name_<?=$project['id']?>"><?=$sBox1?><?=reformat($sTitle,30,0,1); ?></h1>
		</td>
	</tr>
	<tr valign="top">
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr valign="top">
					<td colspan="3" bgcolor="#FFFFFF" class="box" style="padding: 0 19px 19px 19px; color: #000000; font-size: 12px;">&nbsp;
	    				<div style="text-align: right; padding-bottom: 15px">
              <a href="<?=$back_href?>" class="blue"><b>[Назад]</b></a></div>
	    				<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr valign="top">
								<td width="60"><?=($show_info)?view_avatar($project['login'], $project['photo']):""?></td>
								<td class="bl_text">
<?
switch ($project['currency']) {
    case 0:
        $paysum_fm = intval($project['cost']);
        $paysum_r  = intval($_GET["paysum"]) * EXCH_WMR;
        $paysum_rb = intval($_GET["paysum"]) * EXCH_TR;
		$paysum_z  = intval($_GET["paysum"]) * EXCH_WMZ;
		break;
}
?>
    								<div class="prj_cost">Бюджет: <?=CurToChar($project['cost'], $project['currency'])?><?
if ($project['cost'] > 0) {
	                                    ?><br />
										<table class="small" style="margin-left: 52px; color: #999999;">
											<tr>
												<td style="text-align: right; vertical-align: top;">Это:</td>
												<td style="text-align: left; vertical-align: top; padding-left: 7px;"><?
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
										</table><?
}
                                    ?></div>
                                    
				    <?if ($show_info){?>
                                    <?= (view_mark_user($project));?><?=$session->view_online_status($project['login'])?>
                                    <font class="empname11"><a href="/users/<?=$project['login']?>" class="empname11" title="<?=($project['uname']." ".$project['usurname'])?>"><?=($project['uname']." ".$project['usurname'])?></a> [<a href="/users/<?=$project['login']?>" class="empname11" title="<?=$project['login']?>"><?=$project['login']?></a>]</font>
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
    if ($project['closed'] == "t")  $sBox1 .= "<a href=\"/about/prjrules/\" title=\"Проект закрыт\"><img src=\"/images/ico_closed.gif\" alt=\"Проект закрыт\" style='vertical-align: middle;margin: 0px 8px 4px 0px;' /></a>";
    ?><h2 class="b-page__title"><?=$sBox1?><?=reformat($project['name'],30,0,1); ?></h2><?
}
                                    ?><div class="prj_text" id="projectp<?=$project['id']?>"><?=reformat($project['descr'], 70, 0, 0, 1)?></div><?
$uid = get_uid();
if ($uid && ($project["user_id"] == $uid) && ($project['is_blocked'] != 't')) {
                                    ?><table cellpadding="2" cellspacing="0" border="0" style="margin: 12px 0px 16px 0px">
										<tr valign="middle">
											<td><img src="/images/ico_setup.gif" border="0"></td>
											<td><a class="public_blue" href="/public/?step=1&public=<?=$project["id"]?>&red=<?=rawurlencode("/users/".$project["login"]."/setup/projects/")?>">Редактировать</a></td>
											<td>&nbsp;&nbsp;&nbsp;</td>
            								<td><img src="/images/<?=($project["closed"] == 't' ? "ico_reopen.gif" : "ico_close_round.gif")?>" border="0"></td>
            								<td><?
												if ($project["closed"] == 't') {
                                            ?><a class="public_blue" href="/projects/?action=prj_close&pid=<?=$project["id"]?>">Публиковать еще раз</a><?
												} else {
                                             ?><a class="public_blue" href="/projects/?action=prj_close&pid=<?=$project["id"]?>">Снять с публикации</a><?
												}
                                            ?></td>										</tr>
									</table><?
		                            ?><table cellpadding="2" cellspacing="0" border="0">
										<tr>
	        								<td class="public_grey">Статистика:<br><?
    if ($project["closed"]=="t") { ?><? } else {
          $payed=(($project["payed_to"]>$project["now"] && $project["payed"]) ? 1 : 0 );
          $counte=$obj_project->CountProjectNew($project['post_date'], $project['kind'], $project['top_from'], $project['top_to'], $project['strong_top']);
          $page=floor($counte/$GLOBALS["prjspp"])+1;
          $counte_page=$counte % $GLOBALS["prjspp"];
	                                            ?>Ваш проект &ndash; <a class="public_blue" href="/projects/?kind=<?=$project['kind']?>&page=<?=$page?>#prj<?=$project['id']?>"><?=$counte_page?>-е по счету (<?=$page?>-я страница)</a><br>закладка "<?=GetKind($project["kind"])?>"<?
    }
    
    if (is_new_prj($project['post_date'])) {
                                                ?><br><?=((!$project["comm_count"] || $project["comm_count"] % 10==0 || $project["comm_count"] % 10 >4 || ($project["comm_count"] >4 &&  $project["comm_count"]<21)) ? $project["comm_count"].' предложений' : (($project["comm_count"] % 10 == 1 || $project["comm_count"]==1) ? $project["comm_count"].' предложение' : $project["comm_count"].' предложения'  )   )?></td><?
    }
    else {
                                                ?><br /><?=((!$project["offers_count"] || $project["offers_count"] % 10==0 || $project["offers_count"] % 10 >4 || ($project["offers_count"] >4 &&  $project["offers_count"]<21)) ?  $project["offers_count"].' предложений' : (($project["offers_count"] % 10 == 1 || $project["comm_count"]==1) ?  $project["offers_count"].' предложение' : $project["offers_count"].' предложения'  )   )?></td><?
    }
    if ($project['pro_only'] == 't') echo "</tr><tr><td><font  class=\"fl2_offer_meta2\" style=\"background-color:#fff7ee;\">Отвечать на проект могут только пользователи с аккаунтом ".view_pro()."</font></td>";
			                                    ?></tr>
			                                </table><?
    }
			                            ?>
			                            
			                            
			                            </td>
			                        </tr><?
    if ($project['attach']) {
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
                               ?></table>

                <div style="font-size:11px;color:#666666;text-align:right;padding:0 0 5px 0">Раздел: <?=projects::getSpecsStr($project['id'],'<strong> / </strong>');?></div>
                                <div id="project-reason-<?=$project['id']?>" style="margin-top: 10px;<?=($project['is_blocked']? 'display: block': 'display: none')?>"><? 
                                if ($project['is_blocked']) {
                                    $moder_login = (hasPermissions('projects'))? $project['admin_login']: '';
                                    print HTMLProjects::BlockedProject($project['blocked_reason'], $project['blocked_time'], $moder_login, "{$project['admin_name']} {$project['admin_uname']}");
                                } else {
                                    print '&nbsp;';
                                }
                                ?></div>


                            </td>
						</tr>
					</table>
            	</td>
        	</tr>
			<? if ($project['is_blocked'] != 't') { ?>
            <tr valign="top">
				<td colspan="2" style="padding: 18px 0 12px 0;"><a name="offers"></a>
				<h1>Предложения по проекту</h1>
				</td>
			</tr>
			<tr valign="top">
				<td><?
	if ($op_count_all > 0) {
                    ?><table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr valign="top">
							<td colspan="3" bgcolor="#FFFFFF" class="box" style="padding: 48px 16px 0px 16px; border-right: none;"><?
        if ($project['login'] == $_SESSION["login"]) {
                                ?><div id="po_selector">
									<table>
										<tr>
											<td style="padding: 6px 6px 6px 0px;"><img src="/images/ico_po_offers.gif" style="width: 51px; height: 39px;" /></td>
											<td id="po_offers"><?
            if ($po_type == 'o') {
                                                ?><a style="font-weight: bold;" href="?pid=<?=$prj_id?>&amp;type=o&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Не определился</a> <span id="po_offers_count"><?=$op_count_offers?></span><?
            } else {
                                                ?><a class="blue" style="font-weight: bold;" href="?pid=<?=$prj_id?>&amp;type=o&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Не определился</a> <span id="po_offers_count"><?=$op_count_offers?></span><?
            }
                                                ?><br />
                                                <span id="op_count_offers_new_msgs" class="op_count_new_msgs"><?
            if ($op_count_offers_new_msgs > 0) {
                                                ?><img src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0"> <?=$op_count_offers_new_msgs?> <?=ending($op_count_offers_new_msgs, 'новое сообщение', 'новых сообщения', 'новых сообщений')?><?
            }
                                                ?></span></td>
										</tr>
                                        <tr>
											<td style="padding: 6px 6px 6px 0px;"><img
												src="/images/ico_po_refuse.gif"
												style="width: 51px; height: 37px;" /></td>
											<td id="po_refuse"><?
            if ($po_type == 'r') {
                                            ?><a style="font-weight: bold;" href="?pid=<?=$prj_id?>&amp;type=r&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Отказал</a> <span id="po_refuse_count"><?=$op_count_refuse?></span><?
            } else {
                                            ?><a class="blue" style="font-weight: bold;" href="?pid=<?=$prj_id?>&amp;type=r&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Отказал</a> <span id="po_refuse_count"><?=$op_count_refuse?></span><?
            }
                                            ?><br />
                                            <span id="op_count_refuse_new_msgs" class="op_count_new_msgs"><?
            if ($op_count_refuse_new_msgs > 0) {
                                            ?><img src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0"> <?=$op_count_refuse_new_msgs?> <?=ending($op_count_refuse_new_msgs, 'новое сообщение', 'новых сообщения', 'новых сообщений')?><?
            }
                                            ?></span></td>
										</tr>
										<tr>
											<td style="padding: 6px 6px 6px 0px;"><img
												src="/images/ico_po_candidate.gif"
												style="width: 51px; height: 36px;" /></td>
											<td id="po_candidate"><?
            if ($po_type == 'c') {
                                                ?><a style="font-weight: bold;" href="?pid=<?=$prj_id?>&amp;type=c&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Кандидаты</a> <span id="po_candidate_count"><?=$op_count_candidate?></span><?
            } else {
                                                ?><a class="blue" style="font-weight: bold;" href="?pid=<?=$prj_id?>&amp;type=c&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Кандидаты</a> <span id="po_candidate_count"><?=$op_count_candidate?></span><?
            }
                                                ?><br />
                                                <span id="op_count_candidate_new_msgs" class="op_count_new_msgs"><?
            if ($op_count_candidate_new_msgs > 0) {
                                                ?><img src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0"> <?=$op_count_candidate_new_msgs?> <?=ending($op_count_candidate_new_msgs, 'новое сообщение', 'новых сообщения', 'новых сообщений')?><?
            }
                                                ?></span></td>
                                        </tr>
                                        <tr>
											<td style="padding: 6px 6px 6px 0px;"><img
												src="/images/ico_po_executor.gif"
												style="width: 51px; height: 36px;" /></td>
											<td id="po_executor"><?
            if ($po_type == 'i') {
                                                ?><a style="font-weight: bold;" href="?pid=<?=$prj_id?>&amp;type=i&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Победитель</a> <span id="po_executor_count"><?=$op_count_executor?></span><?
            } else {
                                                ?><a class="blue" style="font-weight: bold;" href="?pid=<?=$prj_id?>&amp;type=i&amp;sort=<?=$po_sort?><?=$from_prm_s?>#offers">Победитель</a> <span id="po_executor_count"><?=$op_count_executor?></span><?
            }
                                                ?><br />
                                                <span id="op_count_executor_new_msgs" class="op_count_new_msgs"><?
            if ($op_count_executor_new_msgs > 0) {
                                                ?><img src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0"> <?=$op_count_executor_new_msgs?> <?=ending($op_count_executor_new_msgs, 'новое сообщение', 'новых сообщения', 'новых сообщений')?><?
            }
                                                ?></span></td>
											</tr>
										</table>
                                    </div><?
            } else {
                                    ?>&nbsp;<?
            }
                                    ?></td>
				                    <td colspan="3" bgcolor="#FFFFFF" class="box" style="padding: 6px 16px 0px 0px; border-left: none;"><?
            if (isset($offers) && is_array($offers) && (count($offers) > 0)) {
                                        ?><table class="portfolio" style="width: 717px; border-bottom: 1px #c6c6c6 solid;">
											<tr><?
                if ($po_sort == 'date') {
                                                ?><td class="po_sort_el_a">Новизна предложения</td><?
                }
                                            ?></tr>
                                        </table><?
				$offers_count = count($offers) - 1;
				foreach ($offers as $key => $value) {
                    if ($value['user_id'] == $project['exec_id']) {
                            			?><a name="winner"></a><?
                    }
                                        ?><table id="po_<?=$value['id']?>" class="portfolio" style="margin-top:18px;<? if ($key < $offers_count) { ?>border-bottom:1px #c6c6c6 solid;<? } ?>"><?
                    if (hasPermissions('projects')) {
                        $sBox = "<a style=\"color:Red;\" href=\"/projects/?action=deloffer&pid=" . $value['project_id']."&oid=" . $value['id']."\" onClick=\"return warning(2)\">Удалить</a>";
                    }
                    if ($sBox != '') {
                                            ?><tr valign="top">
                                                <td colspan="2" style="text-align: right; padding-bottom: 12px;"><?=$sBox?></td>
                                            </tr><?
                    }
                    	                    ?><tr valign="top">
												<td width="60"><?=view_avatar($value['login'], $value['photo'])?></td>
												<td style="padding: 0px;">
    												<table border="0" style="width: 100%;" cellspacing="0" cellpadding="2">
                                                        <tr>
                                                            <td style="width: 100%; padding: 0px;">
                                                            <?= (view_mark_user($value, '', false));?><?=$session->view_online_status($value['login'])?>
                                                                <span class="frlname11"><a href="/users/<?=$value['login']?>" class="frlname11" title="<?=($value['uname']." ".$value['usurname'])?>"><?=($value['uname']." ".$value['usurname'])?></a> [<a href="/users/<?=$value['login']?>" class="frlname11" title="<?=$value['login']?>"><?=$value['login']?></a>]</span><br /><?
                    if ($value['spec_name'] != '') { ?>Специализация: <?=$value['spec_name']?><? } ?><br />
									                            Рекомендации работодателей: <strong class="r_positive"> + <?=(int) $value['sbr_opi_plus']?></strong>
									                            / <strong> <?=(int) $value['sbr_opi_null']?></strong> / <strong
	                                                            class="r_negative"> - <?=(int) $value['sbr_opi_minus']?></strong><br /><?
                    if ($value['country_name'] != 'Не определено') { ?><?=$value['country_name']?><? if ($value['city_name'] != 'Не определено') { ?><? if ($value['country_name'] && $value['city_name']) { ?>, <? } ?><?=$value['city_name']?><br /><? } } ?>
	                                                            <div style="font-size: 100%;"><img
																src="/images/ico_mail.gif" alt="" width="17" height="8"
																border="0"
																style="padding-bottom: 1px; margin: 0px 4px 0px 0px; vertical-align: bottom;"><a
																href="/contacts/?from=<?=$value['login']?>#form" class="blue">Обсудить проект</a></div>
															</td>
															<td style="text-align: center; vertical-align: top; white-space: nowrap;"><?
                    $txt_time = view_range_time($value['time_from'], $value['time_to'], $value['time_type']);
                                                                ?><h1 style="margin-bottom: 0px;"><?=$txt_time?></h1>
															</td>
															<td style="text-align: right; vertical-align: top; white-space: nowrap; padding-left: 32px;"><?
                    $txt_cost = view_range_cost($value['cost_from'], $value['cost_to'], '', '', false);
                    if ($txt_cost != '') {
                                                                ?><span style="text-align: left; padding: 0px; margin: 0px;">
                                                                    <h1 style="margin: 0px; text-align: right;"><?=$txt_cost?></h1>
                                                                </span><?
                    }
                                                            ?></td>
														</tr>
														<tr>
															<td colspan="3" style="width: 100%; padding: 8px 0px 0px 0px;"><?
                    if (isset($value['dialogue']) && is_array($value['dialogue']) && (count($value['dialogue']) > 0)) {
	                                                            ?><div
																class="po_comments_<? if (($value['emp_new_msg_count'] > 0) && ($project['login'] == $_SESSION["login"])) { ?>new_<? } ?>hide"
																id="po_comments_<?=$value['id']?>"><?
                        if ($project['login'] == $_SESSION["login"]) {
                                                                ?><span id="new_msgs_<?=$value['id']?>" style="float: right;"><?
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
              			$i = 0;
              			$dc = count($value['dialogue']);
              			if ($dc == 1) {
										                        ?><div style="margin-bottom: 8px; font-size: 100%;"><span
																class="<?=is_emp($value['dialogue'][0]['role'])?'emp':'frl'?>name11"><a
																href="/users/<?=$value['dialogue'][0]['login']?>"
																class="<?=is_emp($value['dialogue'][0]['role'])?'emp':'frl'?>name11"
																title="<?=($value['dialogue'][0]['uname']." ".$value['dialogue'][0]['usurname'])?>"><?=($value['dialogue'][0]['uname']." ".$value['dialogue'][0]['usurname'])?></a>
																[<a href="/users/<?=$value['dialogue'][0]['login']?>"
																class="<?=is_emp($value['dialogue'][0]['role'])?'emp':'frl'?>name11"
																title="<?=$value['dialogue'][0]['login']?>"><?=$value['dialogue'][0]['login']?></a>]</span> <span id="po_date_<?=$comment['id']?>"><?=dateFormat("[d.m.Y | H:i]", $value['dialogue'][0]['post_date'])?></span><br />
																    <div id="po_comment_<?=$comment['id']?>"><?=reformat(trim(strip_tags($value['dialogue'][0]['post_text'])), 100)?></div>
									                              			            <div id="po_comment_original_<?=$comment['id']?>" style="display:none;"><?=reformat(trim(strip_tags($value['dialogue'][0]['post_text'])), 1000, 0, 1)?></div>
                                                                </div>
																<div id="po_dialogue_talk_<?=$value['id']?>"
																style="font-size: 12px; visibility: visible; height: auto; overflow: visible; display: none;">
																</div><?
                            if ($project['login'] == $_SESSION["login"]) {
                                                                ?><div id="po_dialogue_answer_<?=$value['id']?>"
																style="font-size: 100%; margin: 16px 0px 6px 0px;"><?
                                if ($project['closed'] == 'f') {
                                    if (count($value['dialogue']) > 1) {
                                                                    ?><span style="float: right;"><a
																	href="javascript:void(null)"
																	onClick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');"
																	class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть
                                                                    всю переписку</a> <?=count($value['dialogue'])?></span><?
                                }
                                                                    ?><span><a href="javascript:void(0);"
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
																	<?
                            } else {
                                if (count($value['dialogue']) > 1) {
                                                                    ?><span style="float: right;"><a
																	href="javascript:void(null)"
																	onClick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');"
																	class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть
                                                                    всю переписку</a> <?=count($value['dialogue'])?></span><span>&nbsp;</span><?
                            }
                        }
                                                                    ?></div><?
                    }
                } elseif (($project['login'] == $_SESSION["login"]) || hasPermissions('projects')) {
                    foreach ($value['dialogue'] as $key => $comment) {
                        if ($i == 1) {
                                                                    ?><div id="po_dialogue_talk_<?=$value['id']?>" style="font-size: 12px; visibility: visible; height: auto; overflow: visible; display: none;"><?
                        }
              			$i++;
                                                                    ?><div style="margin-bottom: 8px; font-size: 100%;"><span
																	class="<?=is_emp($comment['role'])?'emp':'frl'?>name11"><a
																	href="/users/<?=$comment['login']?>"
																	class="<?=is_emp($comment['role'])?'emp':'frl'?>name11"
																	title="<?=($comment['uname']." ".$comment['usurname'])?>"><?=($comment['uname']." ".$comment['usurname'])?></a>
                                                                    [<a href="/users/<?=$comment['login']?>"
																	class="<?=is_emp($comment['role'])?'emp':'frl'?>name11"
																	title="<?=$comment['login']?>"><?=$comment['login']?></a>]</span> <span id="po_date_<?=$comment['id']?>"><?=dateFormat("[d.m.Y | H:i]", $comment['post_date'])?></span><br />
											                			<div id="po_comment_<?=$comment['id']?>"><?=reformat(trim(strip_tags($comment['post_text'])), 100)?></div>
									                              			        <div id="po_comment_original_<?=$comment['id']?>" style="display:none;"><?=reformat(trim(strip_tags($comment['post_text'])), 1000, 0, 1)?></div>
											              			</div><?
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
                                                                    всю переписку</a> <?=count($value['dialogue'])?></span><?
                            }
                                                                    ?><span><a href="javascript:void(0);"
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
																	<?
			/*
                        } else {
                            if (count($value['dialogue']) > 1) {
                                                                    ?><span style="float: right;"><a
																	href="javascript:void(null)"
																	onClick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');"
																	class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть
																	всю переписку</a> <?=count($value['dialogue'])?></span><span>&nbsp;</span><?
                            }
                        }
                        */
                                                                ?></div><?
                    } elseif (hasPermissions('projects') && (count($value['dialogue']) > 1)) {
                                        ?><div id="po_dialogue_answer_<?=$value['id']?>" style="font-size: 100%; margin: 16px 0px 6px 0px;"><?
                                            ?><span style="float: right;"><a
                                            href="javascript:void(null)"
                                            onClick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');"
                                            class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть
                                            всю переписку</a> <?=count($value['dialogue'])?></span><span>&nbsp;</span></div><?
                    }
                } else {
                                            ?><div style="margin-bottom: 8px; font-size: 100%;"><span
                                            class="<?=is_emp($value['dialogue'][0]['role']) ? 'emp' : 'frl'?>name11"><a
                                            href="/users/<?=$value['dialogue'][0]['login']?>"
                                            class="<?=is_emp($value['dialogue'][0]['role']) ? 'emp' : 'frl'?>name11"
                                            title="<?=($value['dialogue'][0]['uname'] . " " . $value['dialogue'][0]['usurname'])?>"><?=($value['dialogue'][0]['uname'] . " " . $value['dialogue'][0]['usurname'])?></a>
                                            [<a href="/users/<?=$value['dialogue'][0]['login']?>"
                                            class="<?=is_emp($value['dialogue'][0]['role']) ? 'emp' : 'frl'?>name11"
                                            title="<?=$value['dialogue'][0]['login']?>"><?=$value['dialogue'][0]['login']?></a>]</span> <?=dateFormat("[d.m.Y | H:i]", $value['dialogue'][0]['post_date'])?><br />
                                            <?=reformat(strip_tags($value['dialogue'][0]['post_text']), 100)?>
                                            </div>
                                            <div id="po_dialogue_talk_<?=$value['id']?>" style="font-size: 12px; visibility: visible; height: auto; overflow: visible; display: none;">
                                            </div><?
                }
                                                            ?></div><?
            }
                                                        ?></td>
													</tr>
												</table><?
            if (count($value['attach']) > 0) {
                                                ?><table width="100%" border="0" cellspacing="0" cellpadding="2" class="n_qpr" style="margin-bottom: 12px;"><?
                if (isset($value['attach']) && is_array($value['attach'])) {
                    foreach ($value['attach'] as $key => $attach) {
                                                    ?><tr valign="top" class="qpr">
														<td align="left" style="vertical-align: top; padding: 12px 12px 8px 0px;">
														<div style="width: 200px;"><?
                        if (in_array(CFile::getext($attach['pict']), $GLOBALS['graf_array']) || strtolower(CFile::getext($attach['pict'])) == "mp3") {
                            if ($attach['prev'] != '') {
                                                            ?><div style="text-align: left"><a style="text-decoration:none"
															href="/projects/viewwork.php?pid=<?=$value['project_id']?>&user=<?=$value['login']?>&wid=<?=$attach['id']?>"
															target="_blank" class="blue" title=""><?=view_preview($value['login'], $attach['prev'], "upload", $align)?></a></div><?
                            } elseif ($attach['pict'] != '') {
                                                            ?><div
															style="text-align: left; font-size: 11px;"><a
															href="/projects/viewwork.php?pid=<?=$value['project_id']?>&user=<?=$value['login']?>&wid=<?=$attach['id']?>"
															target="_blank" class="blue" title=""><?=$attach['pict']?></a></div><?
                            }
                        } else {
                        	if ($attach['prev'] != '') {
                                                            ?><div style="text-align: left"><a style="text-decoration:none"
															href="<?=WDCPREFIX?>/users/<?=$value['login']?>/upload/<?=$attach['pict']?>"
															target="_blank" class="blue" title=""><?=view_preview($value['login'], $attach['prev'], "upload", $align)?></a></div><?
                            } elseif ($attach['pict'] != '') {
                                                            ?><div
															style="text-align: left; font-size: 11px;"><a
															href="<?=WDCPREFIX?>/users/<?=$value['login']?>/upload/<?=$attach['pict']?>"
															target="_blank" class="blue" title=""><?=$attach['pict']?></a></div><?
                            }
                        }
                                                            ?></div>
														</td>
													</tr><?
                    }
                }
                                                ?></table><?
            }
            if ($value['user_id'] == $project['exec_id']) {
                                                ?><div class="po_exec2"><strong>Это победитель</strong><br />
													Заказчик определил этого фрилансера как победителя по этому
													проекту.</div><?
            }
                                                ?><div
												style="color: #000; font-size: 100%; padding: 16px 0px 38px 0px"><a
												href="/users/<?=$value['login']?>/" class="blue"><strong>Все работы <?=$value['uname']." ".$value['usurname']?> [<?=$value['login']?>]</strong></a></div><?
			if (($project['login'] == $_SESSION["login"]) && ($project['closed'] == 'f')) {
                                                ?><table width="100%" border="0" cellspacing="0" cellpadding="2" style="margin-bottom: 24px;">
                                                    <tr>
                                                        <td style="padding-right:12px;vertical-align:top">
	                                                        <div id="po_m_refuse_<?=$value['id']?>" class="po_m_refuse"
															onMouseOver="inner=true;"
															onMouseOut="inner=false;mouseout('po_m_refuse_<?=$value['id']?>');">
                								<a href="javascript:void(0);"	onclick="document.getElementById('po_m_refuse_<?=$value['id']?>').style.display='none';xajax_RefuseProjectOffer(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>', 1);">
                								  Не подходят работы</a>
                								<a href="javascript:void(0);"	onclick="document.getElementById('po_m_refuse_<?=$value['id']?>').style.display='none';xajax_RefuseProjectOffer(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>', 2);">
                								  Не подходит цена</a>
                								<a href="javascript:void(0);" onclick="document.getElementById('po_m_refuse_<?=$value['id']?>').style.display='none';xajax_RefuseProjectOffer(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>', 4);">
                								  Выбран другой исполнитель</a>
                								<a href="javascript:void(0);" onclick="document.getElementById('po_m_refuse_<?=$value['id']?>').style.display='none';xajax_RefuseProjectOffer(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>', 3);">
                								  Другая причина</a>
                								<a href="javascript:void(0);"	onclick="document.getElementById('po_m_refuse_<?=$value['id']?>').style.display='none';xajax_RefuseProjectOffer(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>', 0);">
                								  Некорректен</a>
															</div>

	                                                        <span id="po_b_refuse_<?=$value['id']?>"><?
                if ($value['refused'] == 't') {
                                                                ?><img src="/images/b_refuse_1.png" style="width: 158px; height: 28px;" /><?
                } else {
	                                                            ?><a href="javascript:void(0);"><img
																id="po_img_refuse_<?=$value['id']?>"
																src="/images/b_refuse_0.png"
																style="width: 158px; height: 28px;"
																onclick="show_fpopup('po_b_refuse_<?=$value['id']?>', 'po_m_refuse_<?=$value['id']?>');" /></a><?
                }
	                                                        ?></span>
	                                                        <div class="public_grey">Этот человек мне не подходит.<br />
	                                                        Может быть в следующий раз.</div>
                                                        </td>
														<td style="width: 164px; padding: 0px 12px 0px 0px; vertical-align: top;">
                            								<span id="po_b_select_<?=$value['id']?>"><?
                if ($value['selected'] == 't') {
                                                        ?><img id="po_img_select_<?=$value['id']?>"
														src="/images/b_select_1.png"
														style="width: 158px; height: 28px;" /><?
                } else {
                                                        ?><a href="javascript:void(0);"><img
														id="po_img_select_<?=$value['id']?>"
														src="/images/b_select_0.png"
														style="width: 158px; height: 28px;"
														onclick="xajax_SelectProjectOffer(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>');" /></a><?
                }
                                                        ?></span>
														<div class="public_grey">Прошел предварительный отбор.<br />
														Может быть победителем.</div>

													</td>
													<td style="width: 310px; text-align: right; padding: 0px; vertical-align: top;">
													<span id="po_b_exec_<?=$value['id']?>"><?
                if (($_GET['type'] == 'i') && ($value['user_id'] == $project['exec_id'])) {
                                                    ?><a
													href="/<?= sbr::NEW_TEMPLATE_SBR;?>/<?=($project['sbr_id'] ? "?id={$project['sbr_id']}" : "?site=create&pid={$value['project_id']}")?>"><img
													id="po_img_exec_<?=$value['id']?>" class="po_img_exec"
													src="/images/b_sbr_0.png" /></a><?
                } else {
                        if ($value['user_id'] == $project['exec_id']) {
                                                    ?><img id="po_img_exec_<?=$value['id']?>" class="po_img_exec" src="/images/b_executor_1.png" /><?
                        } else {
                                                    ?><a href="javascript:void(0);"><img
													id="po_b_exec_<?=$value['id']?>" src="/images/b_winner_0.png"
													style="width: 158px; height: 28px;"
													onclick="xajax_SelectProjectExecutor(<?=$value['id']?>, <?=$value['project_id']?>, <?=$value['user_id']?>, '<?=$po_type?>', <?=$project['exec_po_id']?>);" /></a><?
                        }
                                                    ?><div class="public_grey">Ему отдам приз.</div><?


























































































































































                }
                                                ?></span></td>
                                     </tr>
                                </table><?
            }
                            ?></td>
						</tr>
					</table><?
        }
    }
	else {
                    ?>&nbsp;<?
    }
                ?></td>
            </tr>
			<tr valign="top">
				<td colspan="3" bgcolor="#FFFFFF" class="box" style="border-top: none;"><?php
                // Страницы
                $pagesCount = ceil($num_offers / MAX_OFFERS_AT_PAGE);
                $href = '%s/projects/index.php?pid='. $prj_id;
                if (isset($po_sort)) $href .= '&sort=' . $po_sort;
                if (isset($po_type)) $href .= '&type=' . $po_type;
                $href .= '&page=%d%s';
                if(new_paginator2($item_page, $pagesCount)) {
                    echo new_paginator2($item_page, $pagesCount, 3, $href);
                }
                ?></td>
			</tr>
		</table><?
}
else {
        ?><table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr valign="top">
				<td colspan="3" bgcolor="#FFFFFF" class="box"
					style="padding: 48px 16px 48px 16px;">Ответов нет.</td>
			</tr>
		</table><?
}
    ?></td>
	</tr>
    <? } // if ($project['is_blocked'] != 't') ?>
</table>

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
    setInterval('scroll()', 50);
}
</script>
