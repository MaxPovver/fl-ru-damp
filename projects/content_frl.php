<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects_ci.common.php");
$xajax->printJavascript('/xajax/');

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/HTML/projects_lenta.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");

// Подгружаем данные по мнения создалю проекта
$op_data = opinions::getCounts($project['user_id'], array('frl', 'norisk', 'all', 'total'));
$project_exRates = project_exrates::GetAll();
$exch = array(1=>'FM', 'USD','Euro','Руб');
$translate_exRates = array
(
0 => 2,
1 => 3,
2 => 4,
3 => 1
);

$foto_alt = $project['name']; 



$answer_button_href = (get_uid(FALSE)?(is_emp($_SESSION['role'])?"/frl_only.php" : (($project['pro_only'] == 't' && !$is_pro && $project['user_id']!=$_SESSION['uid'] && !hasPermissions('projects'))?"/payed/":"#new_offer") ) : "/registration/?from_prj=".$project['id']);


$category = professions::GetGroup($project['category'], $eeee);
if($category['name'] && $project['subcategory'])
   $category['name'] .= '&nbsp;/&nbsp;'.professions::GetProfName($project['subcategory']);

$can_edit = !!is_numeric(InGet('edit')) && $user_offer['refused']!='t';
$is_user_offer_exist = ((!$user_offer_exist || $can_edit) && $project['closed'] != 't' && $uid > 0);  

if(($project['pro_only'] == 't' && !$is_pro && $project['user_id']!=$_SESSION['uid'] && !hasPermissions('projects'))) {
	$offers = array();
	$is_user_offer_exist = false;
}

if ( $project['verify_only'] == 't' ) {
    $verify_check = $is_verify;
    if ( !$is_verify ) {
        //$answer_button_href = '/promo/verification/';
        if(!get_uid(false)) { 
            $_SESSION['ref_uri2'] = $project['id'];
        	$is_user_offer_exist = false;
        } else {
        	$answer_button_href = "javascript: quickVerShow();";
        	$quick_verification=1;
        	$is_user_offer_exist = false;
        }
    }
} else {
	if($project['pro_only'] == 't' && get_uid(false) && !$is_pro) {
		$answer_button_href = "/payed/";
		$quick_pro=1;
	}
    $verify_check = true;
}

//Если у фрилансера больше нет ответов
if(@$answers->offers < 1 && !$is_pro && get_uid(false) > 0)
{
    $answer_button_href = "/payed/";
}

?>
<? if($is_user_offer_exist) { ?>
    <script type="text/javascript">
        //window.addEvent("domready", function() {
        //    var textarea = new resizableTextarea($$("div.b-textarea"), { handler: ".handler", modifiers: {x: false, y: true}, size: {y:[130, 1000]}});
        //});
    </script>
<? } ?>

<?
if (!$no_answers || is_pro())
{
?>
<script type="text/javascript">
<!--

var in_office = '<?= (int)($project['kind'] == 4)?>';
var ajaxFlag = 1;
var old_num = 0;
var inner = false;
var cur_prof = <?=$cur_prof?>;
var dialogue_count = new Array(1);
<? if (isset($user_offer['id'])) { ?>
dialogue_count[<?=$user_offer['id']?>] = <?=count($user_offer['dialogue'])?>;
<? } else { ?>
dialogue_count[0] = 0;
<? } ?>

var works_ids = new Array();
var works_names = new Array();
var works_prevs = new Array();
var works_picts = new Array();
var works_links = new Array();

var show_types  = new Array('gif', 'png', 'jpg', 'jpeg', 'flv', 'mp3');

<? foreach ($portf_works as $key => $value) { $use[$value['id']] = 1; ?>
works_ids[<?=$value['id']?>] = '<?=$value['id']?>';
works_names[<?=$value['id']?>] = '<?=(addslashes(trim(preg_replace("/\r?\n/", " ", $value['name']))))?>';
works_prevs[<?=$value['id']?>] = '<?=trim($value['prev_pict'])?>';
works_picts[<?=$value['id']?>] = '<?=trim($value['pict'])?>';
works_links[<?=$value['id']?>] = '<?=trim($value['link'])?>';
<? } ?>
<?if(isset($_GET['edit'])) { ?>
<?for($i=1;$i<4;$i++) { if($user_offer['portf_id'.$i] > 0 && !isset($use[$user_offer['portf_id'.$i]])) { ?>
works_ids[<?=$user_offer['portf_id'.$i]?>] = '<?=$user_offer['portf_id'.$i]?>';
works_prevs[<?=$user_offer['portf_id'.$i]?>] = '<?=trim($user_offer['prev_pict'.$i])?>';
works_picts[<?=$user_offer['portf_id'.$i]?>] = '<?=trim($user_offer['pict'.$i])?>';
<?} } }//for?>


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
<input type=\"hidden\" id=\"from_\" name=\"from\" value=\"frl\">\
<input type=\"hidden\" id=\"po_id\" name=\"po_id\" value=\"" + num + "\">\
<input type=\"hidden\" id=\"po_commentid\" name=\"po_commentid\" value=\"" + commentid + "\">\
<input type=\"hidden\" id=\"prj_id\" name=\"prj_id\" value=\"<?=$prj_id?>\">\
<table width=\"96%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\
<tr>\
	<td colspan=\"2\" style=\"padding-bottom:4px;\">Сообщение:</td>\
<\/tr>\
<tr>\
	<td colspan=\"2\" style=\"padding-bottom:4px;\"><div class=\"b-textarea\"><textarea placeholder=\"Ваш ответ и переписка по нему видна только Заказчику\" class=\"b-textarea__textarea\" tabindex=\"1\" id=\"po_text\" name=\"po_text\" rows=\"4\" onkeydown=\"document.getElementById('po_text_msg').innerHTML = '';\"></textarea></div><div id=\"po_text_msg\"></div>\
    <?php /*if(is_pro()) { ?><div class=\"b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padtop_5 b-layout__txt_padbot_5\" id=\"confirm_messages_project\"><span class=\"b-icon b-icon_sbr_allow\"></span>Вы можете оставлять свои контакты, так как являетесь владельцем аккаунта <span class=\"b-icon b-icon__pro b-icon__pro_f\"></span></div><?php } elseif ( $project['is_pro'] != 't' ) { ?><div class=\"b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padtop_5 b-layout__txt_padbot_5\" id=\"confirm_messages_project\"><span class=\"b-icon b-icon_sbr_forb\"></span>Обмен контактами запрещен. Чтобы оставить свои контакты, <a class=\"b-layout__link\" href=\"/payed/\">купите</a> <span class=\"b-icon b-icon__pro b-icon__pro_f\"></span></div><?php } */ ?>\
    </td>\
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
	  xajax_ReadOfferDialogue(num, <?=$project['id']?>);
	}
}

function answer(num, commentid) {
	setInterval("check_com_text()", 10);
	$('po_dialogue_talk_' + num).style.display = 'block';
	$('po_comments_' + num).className = 'po_comments';
	td = $('po_dialogue_answer_'+num);
	if ((old_num > 0) && (old_num != num)) {
		resetfld(old_num)
	}
	td.innerHTML = GetForm(num, commentid);
//    if($('count_' + num)) {
//        if($('count_' + num).getProperty('need_change') >= 1) 
//            $('confirm_messages_project').dispose();
//    }
    
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
	$('po_dialogue_talk_' + num).style.display = 'none';
	$('po_comments_' + num).className = 'po_comments_hide';
	td1 = $('po_dialogue_answer_'+num);
    innerHTML = '';
	if (dialogue_count[num] > 1) {
		innerHTML = innerHTML + '<span style="float: right;"><a href="javascript:void(null)" onclick="dialogue_toggle(' + num + ');markRead(' + num + ');" class="internal" id="toggle_dialogue_' + num + '">Развернуть всю переписку</a> ' + dialogue_count[num] + '</span>';
	}
	innerHTML = innerHTML + '<span id="add_dialog_' + num + '" class="add_dialog_user"><a href="javascript:void(0);" onclick="answer(' + num + ');markRead(' + num + ');" class="internal">Написать ответ</a></span>';
	if (edit_block[num]) innerHTML =  innerHTML + '<span class="add_dialog_user">'+ edit_block[num] + '</span>';

	td1.innerHTML = innerHTML;
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
		if(el_div) {
		  if(el_div.get('class')) el_div.className = 'po_comments_hide';
        }
	}
	else
	{
		el_top.innerHTML = 'Свернуть переписку';
		if(el_div) {
  		  if(el_div.get('class')) el_div.className = 'po_comments';
        }
	}
}

function add_work(num, pict, prev)
{
	work1 = $('td_pic_1');
	work2 = $('td_pic_2');
	work3 = $('td_pic_3');

	work1_id = $('ps_work_1_id');
	work2_id = $('ps_work_2_id');
	work3_id = $('ps_work_3_id');

	work1_pict = $('ps_work_1_pict');
	work2_pict = $('ps_work_2_pict');
	work3_pict = $('ps_work_3_pict');

	work1_prev = $('ps_work_1_prev_pict');
	work2_prev = $('ps_work_2_prev_pict');
	work3_prev = $('ps_work_3_prev_pict');

	work1_link = $('ps_work_1_link');
	work2_link = $('ps_work_2_link');
	work3_link = $('ps_work_3_link');

	work1_name = $('ps_work_1_name');
	work2_name = $('ps_work_2_name');
	work3_name = $('ps_work_3_name');

	work_sort_1 = $('td_pic_sort_1');
	work_sort_2 = $('td_pic_sort_2');

	if (num == 0)
	{
		work_portf = null;
	}
	else
	{
		work_portf = $('portfolio_work_' + num);
	}

	obj_works_select = $('works_select');
	obj_works_add = $('works_add');
	obj_works_no_select = $('works_no_select');

	if (work1_id.value == '')
	{
		work1.className = 'pic';
		work1_id.value = num;
		if (num == 0) // загружен
		{
			if (prev != '') // есть превью
			{
				work1.innerHTML = '<a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + pict + '" target="_blank" class="blue" title=""  style="text-decoration:none"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + prev + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, 0);"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			else
			{
				work1.innerHTML = '<div align="left" style="font-size:100%;"><a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + pict + '" target="_blank" class="blue" title="">' + pict + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, 0);"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			work1_pict.value = pict;
			work1_prev.value = prev;
			work1_link.value = '';
			work1_name.value = '';
		}
		else // из портфолио
		{
			if (works_prevs[num] != '') // есть превью
			{
				if (works_prevs[num] == undefined) {works_prevs[num] = prev};



				work1.innerHTML = '<a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + num + '" target="_blank" class="blue" title="' + works_names[num] + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + works_prevs[num] + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + num + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			else // превью нема
			{
				work1.innerHTML = '<div align="left" style="font-size:100%;"><a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + num + '" target="_blank" class="blue" title="' + works_names[num] + '" class="blue">' + works_picts[num] + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + num + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			work1_pict.value = works_picts[num];
			work1_prev.value = works_prevs[num];
			work1_link.value = works_links[num];
			work1_name.value = works_names[num];
			if (work_portf != null)
			{
				work_portf.innerHTML = '<input type="checkbox" class="checkbox" id="ps_portfolio_work_' + num + '" name="ps_portfolio_work_' + num + '" value="' + num + '" onclick="javascript:clear_work(1, ' + num + ');" checked /><span style="margin-left:3px">' + works_names[num]+'</a>';
			}
		}
	}
	else
	{
		if (work2_id.value == '')
		{
			work2.className = 'pic';
			work2_id.value = num;
			work_sort_1.innerHTML = '<img id="ico_right_12" src="/images/ico_right.gif" alt="" width="9" height="9" border="0" onclick="change_work_pos(' + num + ", '12');\" /><br />" + '<img id="ico_left_12" src="/images/ico_left.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" onclick="change_work_pos(' + num + ", '12');\">";
			if (num == 0) // загружено
			{
				if (prev != '') // есть превью
				{
					work2.innerHTML = '<a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + pict + '" target="_blank" class="blue" title=""><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + prev + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, 0);"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
				else
				{
					work2.innerHTML = '<div align="left" style="font-size:100%;"><a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + pict + '" target="_blank" class="blue" title="">' + pict + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, 0);"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
				work2_pict.value = pict;
				work2_prev.value = prev;
				work2_link.value = '';
				work2_name.value = '';
			}
			else // из портфолио
			{
				if (works_prevs[num] != '') // превью есть
				{
					if (works_prevs[num] == undefined) {works_prevs[num] = prev};
					work2.innerHTML = '<a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + num + '" target="_blank" class="blue" title="' + works_names[num] + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + works_prevs[num] + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + num + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
				else // превью нема
				{
					work2.innerHTML = '<div align="left" style="font-size:100%;"><a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + num + '" target="_blank" class="blue" title="' + works_names[num] + '">' + works_picts[num] + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + num + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
				work2_pict.value = works_picts[num];
				work2_prev.value = works_prevs[num];
				work2_link.value = works_links[num];
				work2_name.value = works_names[num];
				if (work_portf != null)
				{
					work_portf.innerHTML = '<input type="checkbox" class="checkbox" id="ps_portfolio_work_' + num + '" name="ps_portfolio_work_' + num + '" value="' + num + '" onclick="javascript:clear_work(2, ' + num + ');" checked /><span style="margin-left:3px">' + works_names[num]+'</a>';
				}
			}
		}
		else
		{
			if (work3_id.value == '')
			{
				work3.className = 'pic';
				work3_id.value = num;
				work_sort_2.innerHTML = '<img id="ico_right_23" src="/images/ico_right.gif" alt="" width="9" height="9" border="0" onclick="change_work_pos(' + num + ", '23');\" /><br />" + '<img id="ico_left_23" src="/images/ico_left.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" onclick="change_work_pos(' + num + ", '23');\">";
				if (num == 0) // загружено
				{
					if (prev != '') // есть превью
					{
						work3.innerHTML = '<a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + pict + '" target="_blank" class="blue" title=""><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + prev + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(3, 0);"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
					}
					else
					{
						work3.innerHTML = '<div align="left" style="font-size:100%;"><a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + pict + '" target="_blank" class="blue" title="">' + pict + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(3, 0);"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
					}
					work3_pict.value = pict;
					work3_prev.value = prev;
					work3_link.value = '';
					work3_name.value = '';
				}
				else // из портфолио
				{
					if (works_prevs[num] != '') // превью есть
					{
						if (works_prevs[num] == undefined) {works_prevs[num] = prev};
						work3.innerHTML = '<a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + num + '" target="_blank" class="blue" title="' + works_names[num] + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + works_prevs[num] + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(3, ' + num + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
					}
					else // превью нема
					{
						work3.innerHTML = '<div align="left" style="font-size:100%;"><a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + num + '" target="_blank" class="blue" title="' + works_names[num] + '">' + works_picts[num] + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(3, ' + num + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
					}
					work3_pict.value = works_picts[num];
					work3_prev.value = works_prevs[num];
					work3_link.value = works_links[num];
					work3_name.value = works_names[num];
					if (work_portf != null)
					{
						work_portf.innerHTML = '<input type="checkbox" class="checkbox" id="ps_portfolio_work_' + num + '" name="ps_portfolio_work_' + num + '" value="' + num + '" onclick="javascript:clear_work(3, ' + num + ');" checked />' + works_names[num];
					}
				}
				obj_works_select.style.display='none';
				obj_works_add.style.display='none';
				obj_works_no_select.style.display='block';
			}
		}
	}
}

function clear_work(num, id)
{
	work1 = $('td_pic_1');
	work2 = $('td_pic_2');
	work3 = $('td_pic_3');

	work1_id = $('ps_work_1_id');
	work2_id = $('ps_work_2_id');
	work3_id = $('ps_work_3_id');

	work1_pict = $('ps_work_1_pict');
	work2_pict = $('ps_work_2_pict');
	work3_pict = $('ps_work_3_pict');

	work1_prev = $('ps_work_1_prev_pict');
	work2_prev = $('ps_work_2_prev_pict');
	work3_prev = $('ps_work_3_prev_pict');

	work1_link = $('ps_work_1_link');
	work2_link = $('ps_work_2_link');
	work3_link = $('ps_work_3_link');

	work1_name = $('ps_work_1_name');
	work2_name = $('ps_work_2_name');
	work3_name = $('ps_work_3_name');

	work_sort_1 = $('td_pic_sort_1');
	work_sort_2 = $('td_pic_sort_2');

	work_portf = $('portfolio_work_' + id);

	obj_works_select = $('works_select');
	obj_works_add = $('works_add');
	obj_works_no_select = $('works_no_select');

	if (num == 3)
	{
		work3.innerHTML  = '<div class="pic_blank_cnt">&nbsp;</div><div style="width:200px; margin-top:6px; font-size:100%;">&nbsp;</div>';
		work3.className  = 'pic_blank';
		work3_id.value   = '';
		work3_pict.value = '';
		work3_prev.value = '';
		work3_link.value = '';
		work3_name.value = '';

		work_sort_2.innerHTML = '<img id="ico_right_23" src="/images/ico_right0.gif" alt="" width="9" height="9" border="0" /><br /><img id="ico_left_23" src="/images/ico_left0.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" />';
	}
	else
	{
		if (num == 2)
		{
			if (work3_id.value == '')
			{
				work2.innerHTML  = '<div class="pic_blank_cnt">&nbsp;</div><div style="width:200px; margin-top:6px; font-size:100%;">&nbsp;</div>';
				work2.className  = 'pic_blank';
				work2_id.value   = '';
				work2_pict.value = '';
				work2_prev.value = '';
				work2_link.value = '';
				work2_name.value = '';

				work_sort_1.innerHTML = '<img id="ico_right_12" src="/images/ico_right0.gif" alt="" width="9" height="9" border="0" /><br /><img id="ico_left_12" src="/images/ico_left0.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" />';
			}
			else
			{
				if (work3_id.value == 0)
				{
					work2.innerHTML  = '<a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work3_pict.value + '" target="_blank" class="blue" title="' + work3_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work3_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work3_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
				else {
                    work2.innerHTML  = '<a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work3_id.value + '" target="_blank" class="blue" title="' + work3_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work3_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work3_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
					if (typeof document.getElementById('ps_portfolio_work_'+work3_id.value) != 'undefined' && document.getElementById('ps_portfolio_work_'+work3_id.value) != null) document.getElementById('ps_portfolio_work_'+work3_id.value).onclick = function() { clear_work(2, work2_id.value); }
                }
				work2_id.value   = work3_id.value;
				work2_pict.value = work3_pict.value;
				work2_prev.value = work3_prev.value;
				work2_link.value = work3_link.value;
				work2_name.value = work3_name.value;

				work3.innerHTML  = '<div class="pic_blank_cnt">&nbsp;</div><div style="width:200px; margin-top:6px; font-size:100%;">&nbsp;</div>';
				work3.className  = 'pic_blank';
				work3_id.value   = '';
				work3_pict.value = '';
				work3_prev.value = '';
				work3_link.value = '';
				work3_name.value = '';

				work_sort_2.innerHTML = '<img id="ico_right_23" src="/images/ico_right0.gif" alt="" width="9" height="9" border="0" /><br /><img id="ico_left_23" src="/images/ico_left0.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" />';
            }
		}
		else
		{
			if (num == 1)
			{
				if (work2_id.value == '')
				{
					work1.innerHTML  = '<div class="pic_blank_cnt">&nbsp;</div><div style="width:200px; margin-top:6px; font-size:100%;">&nbsp;</div>';
					work1.className  = 'pic_blank';
					work1_id.value   = '';
					work1_pict.value = '';
					work1_prev.value = '';
					work1_link.value = '';
					work1_name.value = '';
				}
				else
				{
					if (work3_id.value == '')
					{
						if (work2_id.value == 0)
						{
							work1.innerHTML  = '<a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work2_pict.value + '" target="_blank" class="blue" title="' + work2_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work2_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
						}
						else
						{
							work1.innerHTML  = '<a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work2_id.value + '" target="_blank" class="blue" title="' + work2_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work2_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
                            if (typeof document.getElementById('ps_portfolio_work_'+work2_id.value) != 'undefined' && document.getElementById('ps_portfolio_work_'+work2_id.value) != null) document.getElementById('ps_portfolio_work_'+work2_id.value).onclick = function() { clear_work(1, work1_id.value); }
                        }
						work1_id.value   = work2_id.value;
						work1_pict.value = work2_pict.value;
						work1_prev.value = work2_prev.value;
						work1_link.value = work2_link.value;
						work1_name.value = work2_name.value;

						work2.innerHTML  = '<div class="pic_blank_cnt">&nbsp;</div><div style="width:200px; margin-top:6px; font-size:100%;">&nbsp;</div>';
						work2.className  = 'pic_blank';
						work2_id.value   = '';
						work2_pict.value = '';
						work2_prev.value = '';
						work2_link.value = '';
						work2_name.value = '';

						work_sort_1.innerHTML = '<img id="ico_right_12" src="/images/ico_right0.gif" alt="" width="9" height="9" border="0" /><br /><img id="ico_left_12" src="/images/ico_left0.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" />';
					}
					else
					{
						if (work2_id.value == 0)
						{
							work1.innerHTML  = '<a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work2_pict.value + '" target="_blank" class="blue" title="' + work2_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work2_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
						}
						else
						{
							work1.innerHTML  = '<a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work2_id.value + '" target="_blank" class="blue" title="' + work2_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work2_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
                            //if (typeof document.getElementById('ps_portfolio_work_'+work1_id.value) != 'undefined' && document.getElementById('ps_portfolio_work_'+work1_id.value) != null) document.getElementById('ps_portfolio_work_'+work2_id.value).onclick = function() { clear_work(1, work1_id.value); }
                        }
						work1_id.value   = work2_id.value;
						work1_pict.value = work2_pict.value;
						work1_prev.value = work2_prev.value;
						work1_link.value = work2_link.value;
						work1_name.value = work2_name.value;

						if (work3_id.value == 0)
						{
							work2.innerHTML  = '<a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work3_pict.value + '" target="_blank" class="blue" title="' + work3_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work3_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work3_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
						}
						else {
							work2.innerHTML  = '<a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work3_id.value + '" target="_blank" class="blue" title="' + work3_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work3_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work3_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
						}
						work2_id.value   = work3_id.value;
						work2_pict.value = work3_pict.value;
						work2_prev.value = work3_prev.value;
						work2_link.value = work3_link.value;
						work2_name.value = work3_name.value;

						work3.innerHTML  = '<div class="pic_blank_cnt">&nbsp;</div><div style="width:200px; margin-top:6px; font-size:100%;">&nbsp;</div>';
						work3.className  = 'pic_blank';
						work3_id.value   = '';
						work3_pict.value = '';
						work3_prev.value = '';
						work3_link.value = '';
						work3_name.value = '';

						work_sort_2.innerHTML = '<img id="ico_right_23" src="/images/ico_right0.gif" alt="" width="9" height="9" border="0" /><br /><img id="ico_left_23" src="/images/ico_left0.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" />';
					}
				}
			}
		}
	}

	obj_works_select.style.display='block';
	obj_works_add.style.display='block';
	obj_works_no_select.style.display='none';

	if (work_portf != null)
	{
		work_portf.innerHTML = '<input type="checkbox" class="checkbox" id="ps_portfolio_work_' + id + ' name="ps_portfolio_work_' + id + '" value="' + id + '" onclick="add_work(' + id + ');" /> <a href="javascript:void(null);" onclick="add_work(' + id + ');" class="blue">' + works_names[id] + '</a>';
	}
}

function change_work_pos(id, num)
{
    var work_id_value;
	var work_pict_value;
	var work_prev_value;
	var work_link_value;
	var work_name_value;

	work1 = $('td_pic_1');
	work2 = $('td_pic_2');
	work3 = $('td_pic_3');

	work1_id = $('ps_work_1_id');
	work2_id = $('ps_work_2_id');
	work3_id = $('ps_work_3_id');

	work1_pict = $('ps_work_1_pict');
	work2_pict = $('ps_work_2_pict');
	work3_pict = $('ps_work_3_pict');

	work1_prev = $('ps_work_1_prev_pict');
	work2_prev = $('ps_work_2_prev_pict');
	work3_prev = $('ps_work_3_prev_pict');

	work1_link = $('ps_work_1_link');
	work2_link = $('ps_work_2_link');
	work3_link = $('ps_work_3_link');

	work1_name = $('ps_work_1_name');
	work2_name = $('ps_work_2_name');
	work3_name = $('ps_work_3_name');

	work_sort_1 = $('td_pic_sort_1');
	work_sort_2 = $('td_pic_sort_2');

	work_portf = $('portfolio_work_' + id);

	if (num == '12')
	{
		work_id_value = work1_id.value;
		work_pict_value = work1_pict.value;
		work_prev_value = work1_prev.value;
		work_link_value = work1_link.value;
		work_name_value = work1_name.value;

        if (typeof document.getElementById('ps_portfolio_work_'+work1_id.value) != 'undefined' && document.getElementById('ps_portfolio_work_'+work1_id.value) != null) {
            document.getElementById('ps_portfolio_work_'+work1_id.value).onclick = function() { clear_work(2, work2_id.value); }
        }
        if (typeof document.getElementById('ps_portfolio_work_'+work2_id.value) != 'undefined' && document.getElementById('ps_portfolio_work_'+work2_id.value) != null) {
            document.getElementById('ps_portfolio_work_'+work2_id.value).onclick = function() { clear_work(1, work1_id.value); }
        }
        
		if (work2_id.value == 0) // загружен
		{
			if (work2_prev.value != '') // превью есть
			{
				work1.innerHTML  = '<a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work2_pict.value + '" target="_blank" class="blue" title="' + work2_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work2_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			else
			{
				work1.innerHTML  = '<div align="left" style="font-size:100%;"><a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work2_pict.value + '" target="_blank" class="blue" title="' + work2_name.value + '">' + work2_pict.value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
		}
		else
		{
			if (work2_prev.value != '') // превью есть
			{
				work1.innerHTML  = '<a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work2_id.value + '" target="_blank" class="blue" title="' + work2_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work2_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			else
			{
				work1.innerHTML  = '<div align="left" style="font-size:100%;"><a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work2_id.value + '" target="_blank" class="blue" title="' + work2_name.value + '">' + work2_pict.value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
		}
		work1_id.value   = work2_id.value;
		work1_pict.value = work2_pict.value;
		work1_prev.value = work2_prev.value;
		work1_link.value = work2_link.value;
		work1_name.value = work2_name.value;

		if (work_id_value == 0) // загружен
		{
			if (work_prev_value != '') // превью есть
			{
				work2.innerHTML  = '<a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work_pict_value + '" target="_blank" class="blue" title="' + work_name_value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work_prev_value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			else
			{
				work2.innerHTML  = '<div align="left" style="font-size:100%;"><a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work_pict_value + '" target="_blank" class="blue" title="' + work_name_value + '">' + work_pict_value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
		}
		else
		{
			if (work_prev_value != '') // превью есть
			{
				work2.innerHTML  = '<a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work_id_value + '" target="_blank" class="blue" title="' + work_name_value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work_prev_value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			else
			{
				work2.innerHTML  = '<div align="left" style="font-size:100%;"><a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work_id_value + '" target="_blank" class="blue" title="' + work_name_value + '">' + work_pict_value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
		}
		work2_id.value   = work_id_value;
		work2_pict.value = work_pict_value;
		work2_prev.value = work_prev_value;
		work2_link.value = work_link_value;
		work2_name.value = work_name_value;
	}
	else
	{
		if (num == '23')
		{
			work_id_value = work2_id.value;
			work_pict_value = work2_pict.value;
			work_prev_value = work2_prev.value;
			work_link_value = work2_link.value;
			work_name_value = work2_name.value;

            if (typeof document.getElementById('ps_portfolio_work_'+work2_id.value) != 'undefined' && document.getElementById('ps_portfolio_work_'+work2_id.value) != null) {
                document.getElementById('ps_portfolio_work_'+work2_id.value).onclick = function() { clear_work(3, work3_id.value); }
            }
            if (typeof document.getElementById('ps_portfolio_work_'+work3_id.value) != 'undefined' && document.getElementById('ps_portfolio_work_'+work3_id.value) != null) {
                document.getElementById('ps_portfolio_work_'+work3_id.value).onclick = function() { clear_work(2, work2_id.value); }
            }
            
			if (work2_id.value == 0) // загружен
			{
				if (work3_prev.value != '') // превью есть
				{
					work2.innerHTML  = '<a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work3_pict.value + '" target="_blank" class="blue" title="' + work3_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work3_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work3_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
				else
				{
					work2.innerHTML  = '<div align="left" style="font-size:100%;"><a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work3_pict.value + '" target="_blank" class="blue" title="' + work3_name.value + '">' + work3_pict.value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work3_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
			}
			else
			{
				if (work3_prev.value != '') // превью есть
				{
					work2.innerHTML  = '<a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work3_id.value + '" target="_blank" class="blue" title="' + work3_name.value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work3_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work3_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
				else
				{
					work2.innerHTML  = '<div align="left" style="font-size:100%;"><a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work3_id.value + '" target="_blank" class="blue" title="' + work3_name.value + '">' + work3_pict.value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work3_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
			}
			work2_id.value   = work3_id.value;
			work2_pict.value = work3_pict.value;
			work2_prev.value = work3_prev.value;
			work2_link.value = work3_link.value;
			work2_name.value = work3_name.value;

			if (work_id_value == 0) // загружен
			{
				if (work_prev_value != '') // превью есть
				{
					work3.innerHTML  = '<a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work_pict_value + '" target="_blank" class="blue" title="' + work_name_value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work_prev_value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(3, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
				else
				{
					work3.innerHTML  = '<div align="left" style="font-size:100%;"><a href="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work_pict_value + '" target="_blank" class="blue" title="' + work_name_value + '">' + work_pict_value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(3, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
			}
			else
			{
				if (work_prev_value != '') // превью есть
				{
					work3.innerHTML  = '<a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work_id_value + '" target="_blank" class="blue" title="' + work_name_value + '"><div align="left"><img src="<?=WDCPREFIX?>/users/<?=get_login($uid)?>/upload/' + work_prev_value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(3, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
				else
				{
					work3.innerHTML  = '<div align="left" style="font-size:100%;"><a href="/users/<?=get_login($uid)?>/viewproj.php?prjid=' + work_id_value + '" target="_blank" class="blue" title="' + work_name_value + '">' + work_pict_value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(3, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
				}
			}
			work3_id.value   = work_id_value;
			work3_pict.value = work_pict_value;
			work3_prev.value = work_prev_value;
			work3_link.value = work_link_value;
			work3_name.value = work_name_value;
		}
	}
}

function dialogue_toggle(num) {
	el_top = $('toggle_dialogue_' + num);
	el_div = $('po_comments_' + num);
	el_tlk = $('po_dialogue_talk_' + num);
	if(el_div) {
  	if (el_top.innerHTML == 'Свернуть диалог') {
        if(el_div.get('class')) el_div.className = 'po_comments'; 
  	} else {
  		if(el_div.get('class')) el_div.className = 'po_comments_hide';
    }
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

<?php 
$answer_button_text = !get_uid()
        ? $project['kind']==7 
            ? "Разместить аналогичный конкурс"
            : $project['kind']==4 
                ? "Опубликовать аналогичную вакансию" 
                : "Опубликовать аналогичный проект"
        : "Ответить на проект";
?>

<?php

if(!isset($uid) || $uid <= 0): 
    $_SESSION['ref_uri2'] = $project['id'];
?>
        <div class="b-layout b-layout_padbot_30">
            <h3 class="b-layout__title b-layout__title_fs18 b-layout__title_lh_1 b-layout__title_padbot_20 b-layout__title_center">
                Бесплатно зарегистрируйся и получай уведомления о новых проектах по работе
            </h3>
            <div class="b-layout__txt b-layout__txt_center">
                <?php view_social_buttons() ?>
            </div>
        </div>
<?php

endif;

?>
        <?php if ($project['ico_closed'] == "t")  $sBox1 .= "<img src=\"/images/ico_closed.gif\" alt=\"Проект закрыт\" style='vertical-align: middle;margin: 0px 8px 4px 0px;'/>"; ?>
        <?php if (!($project['pro_only'] == 't' || $project['verify_only'] == 't')): ?>
		   <?php include(dirname(__FILE__).'/only_pro_verify.inc.php') ?>
        <?php endif; ?>
        <h1 class="b-page__title b-page__title_ellipsis" id="prj_name_<?=$project['id']?>"><?=$sBox1?><?=reformat($sTitle,30,0,1); ?></h1>
        <?php if ($project['pro_only'] == 't' || $project['verify_only'] == 't'): ?>
		   <?php include(dirname(__FILE__).'/only_pro_verify.inc.php') ?>
        <?php endif; ?>
<? // если проект платный или создатель проекта ПРО, то банер не показываем
if(!(isset($project) && isset($project['payed']) && $project['payed'] > 0) || ($project['is_pro'] === 't')) { ?>
        <div class="b-layout b-layout_float_right b-layout_width_240 b-page__desktop"><?= printBanner240(false) ?></div>
<? } ?>
        
        <div class="b-layout <? if(!(isset($project) && isset($project['payed']) && $project['payed'] > 0) || ($project['is_pro'] === 't')) { ?>b-layout_margright_270 b-layout_marg_null_ipad<? } ?>"><?php include "tpl.prj-main.php";?></div>
        
<?php

if(!isset($uid) || $uid <= 0): 

        //Выводим список схожих проектов/ваканси/конкурсов
        $prj = new new_projects();
        $prj_content = $prj->getSimilarProjects(
                $project['id'], 
                5, 
                $project_specs);
        
        if ($prj_content):
            //Список специализаций через запятую
            $str_specs = projects::getGroupLinks($project_specs);
?>
        <div class="b-layout b-layout_margright_270 b-layout_marg_null_ipad">
            <h3 class="b-layout__title b-layout__title_padbot_20">
                <?php if($str_specs): ?>
                    Другие проекты по <?= ending(count($project_specs), 'специализации', 'специализациям', 'специализациям') ?> 
                    &laquo;<?= projects::getGroupLinks($project_specs); ?>&raquo;
                <?php else: ?>
                    Возможно вас заинтересуют другие проекты
                <?php endif; ?>
            </h3>
            <?= $prj_content ?>
            <div class="b-pager">
                <ul class="b-pager__list">
                    <li class="b-pager__item">
                        <a class="b-pager__link" href="/projects/">Все проекты</a>
                    </li>
                </ul>
            </div>
        </div>
<?php
        endif;
        
        
        
        
    //Выходим, далее шаблон не выводим
    return;
endif;
    
?>
<?php

//$is_send_offers = projects_offers::offerSpecIsAllowed($project['id']);

if(!is_pro()) {
    $spec_modified = professions::getLastModifiedSpec($_SESSION['uid']);
}
$count_hidden_offers = 0;
?>


 
 <a name="offers"></a>

    <? 
    $notHiddenOffersCount = count($offers) + (int)$user_offer_exist; // сколько ответов будет показано 
    $count_hidden_offers = $real_offers_count - $notHiddenOffersCount;
    ?>

<?php if ($answers->offers < 1 && !$is_pro && get_uid() && !$user_offer_exist && $project['pro_only'] !== 't') {?>
	<div class="b-layout b-layout_margright_270 b-layout_marg_null_ipad" style="padding: 6px 0 0px 64px;">
	   <? require_once("content_no_answers.php"); ?>
	   </div>
<?php return; } //if ?>




<?php //if ($project['pro_only'] == 't' && !$is_pro && $project['user_id']!=$_SESSION['uid'] && !hasPermissions('projects')) { return; } ?>
<div class="b-layout <? if(!(isset($project) && isset($project['payed']) && $project['payed'] > 0) || ($project['is_pro'] === 't')) { ?>b-layout_margright_270 b-layout_marg_null_ipad<? } ?>">
<?php
$offers_count = count($offers) - 1; ?>
    <?php if (($uid > 0 || isset($offers) && is_array($offers) && $offers_count >= 0)) { ?>
    <?php } //if ?>
    
    <?php if ($is_user_offer_exist  && (int)$project['exec_id'] == 0) { // Нет предложений от данного юзера. Или пользователь редактирует свое предложение + проект не закрыт ?>
            
            <?php if ($can_edit && !InPost('f')) {
      				$ps = $user_offer;
                    if(!$error_offer) {
                        $contacts_freelancer = unserialize($user_offer['offer_contacts']) ? unserialize($user_offer['offer_contacts']) : ''; 
                    }
      				$ps['portfolio_work_1_id'] = $user_offer['portf_id1'];
      				$ps['portfolio_work_2_id'] = $user_offer['portf_id2'];
      				$ps['portfolio_work_3_id'] = $user_offer['portf_id3'];

      				$ps['portfolio_work_1'] = $user_offer['pict1'];
      				$ps['portfolio_work_2'] = $user_offer['pict2'];
      				$ps['portfolio_work_3'] = $user_offer['pict3'];

      				$ps['portfolio_work_1_prev_pict'] = $user_offer['prev_pict1'];
      				$ps['portfolio_work_2_prev_pict'] = $user_offer['prev_pict2'];
      				$ps['portfolio_work_3_prev_pict'] = $user_offer['prev_pict3'];

      				$ps['cost_from'] = round($ps['cost_from'] , 2);
      				$ps['cost_to'] = round($ps['cost_to'] , 2);
      				
                    $sPostText = ($project['kind'] != 4 && $user_offer['dialogue'][0]['moderator_status'] === '0') ? $stop_words->replace($user_offer['dialogue'][0]['post_text']) : $user_offer['dialogue'][0]['post_text'];
      				$ps['text'] = strip_tags( $sPostText );
      			}
         
									
      				echo '<div class="answ-bord"></div>';
      			if ($can_edit) {
      				echo '<a name="new_offer"></a><h1 class="b-layout__title">Редактирование ответа по проекту:</h1>';
      			} else {
      				echo '<a name="new_offer"></a><h1 class="b-layout__title">Ваш ответ по проекту</h1>';
      			}
      			
                $user_answers = $answers;
                $op_codes     = $user_answers->GetOpCodes();
                if(!is_pro()) {
                    $isShowFreeAnswersTxt = true;
                    include(TPL_ANSWERS_DIR."/tpl.answers-item.php");
                }
      			?>
      			<?= view_hint_access_action('Чтобы ответить на проект');?>
                 <div class="b-layout b-layout_padbot_30 b-layout_2bordbot_dfdfdf0 b-layout_margbot_30">
                    <form id="form_add_offer" name="form_add_offer" action="<?=$_SERVER['REQUEST_URI']?>" method="POST" onKeyPress="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {this.submit()}">
                        <input name="hash" type="hidden" value="<?=$hash?>" />
                        <input id="ps_action" name="action" type="hidden" value="<? echo ($can_edit)?'change':'add'; ?>" />
                        <input id="ps_pid" name="pid" type="hidden" value="<?=$prj_id?>" />
                        <input name="f" type="hidden" value="<?=$from_prm?>" />
                        <input name="u" type="hidden" value="<?=$from_usr?>" />
                        <input name="edit" type="hidden" value="<?=InGet('edit',0)?>" />
                    
                <div class="b-layout <?php if( !(!(isset($project) && isset($project['payed']) && $project['payed'] > 0) || ($project['is_pro'] === 't') ) ) {?>b-layout_margright_270 b-layout_marg_null_ipad<? } ?>">
                    <table class="b-layout__table b-layout__table_width_full">
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_width_110 b-layout__td_padbot_20" ><div class="b-layout__txt b-layout__txt_fontsize_11  b-layout__txt_bold">Стоимость</div></td>
                        <td class="b-layout__td b-layout__td_padbot_20" >
                        <input style="width:60px" name="ps_cost_from" id="ps_cost_from" value="<?=$ps['cost_from']?>" maxlength="8" />
                   <?php /* <input name="ps_cost_to" id="ps_cost_to" value="<?=$ps['cost_to']?>" maxlength="8" style="width:60px;" /> */ ?>
                      <select name="ps_cost_type" id="prj_cost_type" style="position:relative; top:-1px">
                          <option value="0" <?=($ps['cost_type'] == 0 ? "selected" : "")?>>USD</option>
                          <option value="2" <?=($ps['cost_type'] == 2 || !$can_edit ? "selected" : "")?>>Руб</option>
                          <option value="1" <?=($ps['cost_type'] == 1 ? "selected" : "")?>>Euro</option>
                      </select>
                  &#160;&#160;&#160;&#160;&#160;
                  <span class="this-little-block-wiil-act-like-a-divider"></span>
               <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold">Срок</span> 
                         <input style="width:60px;" name="ps_time_from" id="ps_time_from" value="<?=$ps['time_from']?>" maxlength="3" /> 
                  <?php /*<input name="ps_time_to" id="ps_time_to" value="<?=$ps['time_to']?>" maxlength="3" style="width:60px;" />*/ ?>
                        <select name="ps_time_type" id="prj_time_type" style="position:relative; top:-1px">
                            <option value="0"<? if ($ps['time_type'] == 0) { ?> selected<? } ?>>в часах</option>
                            <option value="1"<? if ($ps['time_type'] == 1) { ?> selected<? } ?>>в днях</option>
                            <option value="2"<? if ($ps['time_type'] == 2) { ?> selected<? } ?>>в месяцах</option>
                        </select>
               </td>
                    </tr>
                    <?
                    $u = new freelancer();
                    ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_width_110 b-layout__td_padbot_10"><div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold b-layout__txt_nowrap">Текст ответа</div></td>
                        <td class="b-layout__td b-layout__td_padbot_10">
                        
                        <div class="b-textarea">
                        <textarea class="b-textarea__textarea" cols="60" rows="7" id="ps_text" name="ps_text" 
                              style="resize: none;min-height:130px" 
                              placeholder="Кратко опишите суть вашего предложения, условия сотрудничества, вопросы и необходимые требования к заказчику перед началом работы. Ваш ответ и переписка по нему видна только Заказчику."><?=rtrim(input_ref($ps['text']))?></textarea>
                        </div>
                        
                        <?php /*
                        <?php if($project['kind'] == 4) { ?>
                            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padtop_5 b-layout__txt_padbot_5"><span class="b-icon b-icon_sbr_allow"></span>Вы можете оставить контактные данные для связи с этим заказчиком.</div>
                        <?php } else { ?>
                            <?php if(!is_pro(true, $project['user_id']) && !is_pro()) { ?>
                                <div class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padtop_5 b-layout__txt_padbot_5"><span class="b-icon b-icon_sbr_forb"></span>Обмен контактами запрещен. Чтобы оставить свои контакты, <a class="b-layout__link" href="#">купите</a> <span class="b-icon b-icon__pro b-icon__pro_f"></span></div>
                            <?php } elseif(is_pro()) { ?>
                                <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padtop_5 b-layout__txt_padbot_5"><span class="b-icon b-icon_sbr_allow"></span>Вы можете оставить контактные данные для связи с этим заказчиком.</div>
                            <?php } ?>
                        <?php } ?>
                        <div id="ps_text_msg"></div>
                                                                                    */ ?>
                        </td>
                    </tr>
                  </table>
                </div>
                  
                  <table class="b-layout__table b-layout__table_width_full">
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_width_110 b-layout__td_padbot_15"></td>
                        <td class="b-layout__td b-layout__td_padbot_15">
                            
                            <?php if(false): ?>
                            <div class="b-check b-check_padbot_10">
                                <input id="ps_for_customer_only" type="checkbox" class="b-check__input" name="ps_for_customer_only" value="1" <? if ($ps['only_4_cust'] == 't') print "checked"?>> 
                                <label class="b-check__label" for="ps_for_customer_only">Скрыть ответ, сделав его видимым только заказчику (автору проекта)</label>
                            </div>
                            <?php endif; ?>
                            
                            <?  if ($project['kind'] != 4) { ?>
                            <div class="b-check">
                                <input id="prefer_sbr" name="prefer_sbr" class="b-check__input" type="checkbox" value="1" <?= $ps['prefer_sbr'] === 't' ? 'checked' : '' ?> />
                                <label for="prefer_sbr" class="b-check__label">Предпочитаю оплату работы через <a href="/promo/bezopasnaya-sdelka/" target="_blank" class="b-layout__link">Безопасную Сделку</a> <?= view_sbr_shield('', 'relative top_1 margleft_5') ?></label>
                            </div>
                            <? } ?>
    
                        </td>
                    </tr>
            <? /*if($contacts_freelancer) { ?>  
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_110 b-project-answer-editor-contacts-title">
                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5  b-layout__txt_fontsize_11">Контакты</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_20">
                   <table class="b-layout__table b-project-answer-editor-contacts-collection">
                       <?php foreach($contacts_freelancer as $name => $contact) { $is_error = ($error_offer["contact_{$name}"] != '');?>
                       <tr class="b-layout__tr">
                            <td class="b-layout__one b-layout__one_width_80"><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5 b-layout__txt_fontsize_11" ><?= $contact['name']?></div></td>
                            <td class="b-layout__one b-layout__one_width_220 <?= ($is_error ? "layout_error" : ""); ?>">
                                <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_fontsize_11 toggler-view <?= ($is_error ? "b-layout__txt_hide" : ""); ?>" ><?= stripslashes( $contact['value'] ); ?></div>
                                <div class="b-combo <?= ($is_error ? "" : "b-combo_hide"); ?> toggler-edit">
                                    <div class="b-combo__input">
                                        <input class="b-combo__input-text contacts-input" name="contacts[<?= $name?>]" type="text" size="80" value="<?= stripslashes( $contact['value'] ); ?>" maxlength="100"/>
                                    </div>
                                </div>
                                <? if($is_error) { ?>
                                <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_color_c10600 contacts-error">
                                    <span class="b-icon b-icon_sbr_rattent"></span><span><?= $error_offer["contact_{$name}"]?></span>
                                </div>
                                <? if(!$scroll_init) { $scroll_init = true;?><script> window.addEvent("domready", function() { JSScroll($$('.layout_error')[0]); });</script><? }//if?>
                                <? }//if?>
                            </td>
                            <td class="b-layout__one b-layout__one_padleft_20"><div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_fontsize_11" ><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 toggler-contacts" href="javascript:void(0)"><?= ($is_error ? "сохранить" : "изменить"); ?></a></div></td>
                        </tr>
                       <?php }//foreach?>
                   </table>
                </td>
            </tr>
            <? } */ ?>
              
              
              
              
              
                    </table>
                    <?php if ($is_pro2): ?>                
                        <table class="b-layout__table b-layout__table_width_full">
                            <tr class="b-layout__tr">
                                <td class="b-layout__td b-layout__td_width_110"><div class="b-layout__txt b-layout__txt_fontsize_11  b-layout__txt_bold">Примеры работ,<br>не более трех</div></td>
                                <td class="b-layout__td">
        
                                    <input id="ps_work_1_id" name="ps_work_1_id" type="hidden" value="" />
                                    <input id="ps_is_color" name="ps_is_color" type="hidden" value="<?= $ps['is_color']?>" />
                                    <input id="ps_payed_items" name="ps_payed_items" type="hidden" value="<?= $ps['payed_items']?>" />
                                    <input id="ps_work_2_id" name="ps_work_2_id" type="hidden" value="" />
                                    <input id="ps_work_3_id" name="ps_work_3_id" type="hidden" value="" />
                                    <input id="ps_work_1_pict" name="ps_work_1_pict" type="hidden" value="<?=$ps['portfolio_work_1']?>" />
                                    <input id="ps_work_2_pict" name="ps_work_2_pict" type="hidden" value="<?=$ps['portfolio_work_2']?>" />
                                    <input id="ps_work_3_pict" name="ps_work_3_pict" type="hidden" value="<?=$ps['portfolio_work_3']?>" />
                                    <input id="ps_work_1_prev_pict" name="ps_work_1_prev_pict" type="hidden" value="<?=$ps['portfolio_work_1_prev_pict']?>" />
                                    <input id="ps_work_2_prev_pict" name="ps_work_2_prev_pict" type="hidden" value="<?=$ps['portfolio_work_2_prev_pict']?>" />
                                    <input id="ps_work_3_prev_pict" name="ps_work_3_prev_pict" type="hidden" value="<?=$ps['portfolio_work_3_prev_pict']?>" />
                                    <input id="ps_work_1_link" name="ps_work_1_link" type="hidden" value="<?=$ps['portfolio_work_1_link']?>" />
                                    <input id="ps_work_2_link" name="ps_work_2_link" type="hidden" value="<?=$ps['portfolio_work_2_link']?>" />
                                    <input id="ps_work_3_link" name="ps_work_3_link" type="hidden" value="<?=$ps['portfolio_work_3_link']?>" />
                                    <input id="ps_work_1_name" name="ps_work_1_name" type="hidden" value="<?=$ps['portfolio_work_1_name']?>" />
                                    <input id="ps_work_2_name" name="ps_work_2_name" type="hidden" value="<?=$ps['portfolio_work_2_name']?>" />
                                    <input id="ps_work_3_name" name="ps_work_3_name" type="hidden" value="<?=$ps['portfolio_work_3_name']?>" />
                                    <table class="works" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td id="td_pic_1" class="pic_blank">
                                            <div class="pic_blank_cnt">&nbsp;</div>
                                        </td>
                                        <td id="td_pic_sort_1" class="pic_sort"><?
                                        if (($ps['portfolio_work_1'] == '') && ($ps['portfolio_work_2'] == '')) { ?><img id="ico_right<?=$curprof?>" src="/images/ico_right0.gif" alt="" width="9" height="9" border="0"><?} else { ?><img id="ico_right<?=$curprof?>" src="/images/ico_right.gif" alt="" width="9" height="9" border="0" /><? } ?><br /><?
                                        if (($ps['portfolio_work_1'] == '') && ($ps['portfolio_work_2'] == '')) { ?><img id="ico_left<?=$curprof?>" src="/images/ico_left0.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" /><? } else { ?><img id="ico_left<?=$curprof?>" src="/images/ico_left.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" /><? }
                                        ?></td>
                                        <td id="td_pic_2" class="pic_blank">
                                            <div class="pic_blank_cnt">&nbsp;</div>
                                        </td>
                                        <td id="td_pic_sort_2" class="pic_sort"><?
                                        if (($ps['portfolio_work_2'] == '') && ($ps['portfolio_work_3'] == '')) { ?><img id="ico_right<?=$curprof?>" src="/images/ico_right0.gif" alt="" width="9" height="9" border="0" /><?} else { ?><img id="ico_right<?=$curprof?>" src="/images/ico_right.gif" alt="" width="9" height="9" border="0" /><? } ?><br /><?
                                        if (($ps['portfolio_work_2'] == '') && ($ps['portfolio_work_3'] == '')) { ?><img id="ico_left<?=$curprof?>" src="/images/ico_left0.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" /><? } else { ?><img id="ico_left<?=$curprof?>" src="/images/ico_left.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" /><? }
                                        ?></td>
                                        <td id="td_pic_3" class="pic_blank">
                                            <div class="pic_blank_cnt">&nbsp;</div>
                                        </td>
                                    </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div class="b-layout b-layout_padleft_110 b-layout_padtop_15 b-layout__txt_padleft_null_ipad">
                            <div id="works_no_select" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold b-layout__txt_padbot_10" style="display:none;">Чтобы добавить другие работы, удалите одну из выбранных</div>
                            <?php if ($professions){ ?>
                                <div id="works_select" class="b-layout b-layout_padbot_10">
                                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10 b-layout__txt_fontsize_11">Выбрать и показать примеры работ из своего портфолио:</div>
                                    <table class="portfolio" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="professions" style="padding:0px 7px 0px 0px;">
                                            <?php foreach ($professions as $key => $value): ?>
                                              <div id="profession_<?=$value['id']?>" style="font-size:100%;"><? if ($value['id'] == $cur_prof) { ?><?=$value['name']?><? } else { ?><a href="javascript:void(null);" onclick="if (ajaxFlag){ ajaxFlag=0; xajax_ChangePortfByProf(cur_prof, <?=$value['id']?>, $('ps_work_1_id').value, $('ps_work_2_id').value, $('ps_work_3_id').value);}" class="blue"><?=$value['name']?></a><? } ?></div>
                                            <?php endforeach; ?>
                                        </td>
                                        <td class="portfolio_works" id="portfolio_works">
                                            <?
                                            $i = 0;
                                            foreach ($portf_works as $key => $value)
                                            {
                                                $i++;
                                                if ($i == MAX_WORKS_IN_LIST + 1)
                                                {
                                            ?>
                                              <div id="more_works" style="font-size:11px;display:none;">
                                            <?
                                                }
                                            ?>
                                            <?php
                                                $chk = in_array(($value['pict']=='' && $value['prev_pict']!='' ? $value['prev_pict'] : $value['pict'] ),
                                                            array($ps['portfolio_work_1'], $ps['portfolio_work_2'], $ps['portfolio_work_3']));
                                            ?>
                                            <div id="portfolio_work_<?=$value['id']?>" style="font-size:100%">
                                            <input type="checkbox" class="checkbox" id="ps_portfolio_work_<?=$value['id']?>" name="ps_portfolio_work_<?=$value['id']?>" value="0" <? if ($chk) { print "checked"; }?> onclick="add_work(<?=$value['id']?>);"> <a href="javascript:void(null);" onclick="add_work(<?=$value['id']?>);" class="blue"><?=$value['name']?></a>
                                            </div>
                                            <?
                                            }
                                            if ($i >= MAX_WORKS_IN_LIST + 1)
                                            {
                                            ?>
                                            </div>
                                            <div id="show_more_works" style=" margin-top:12px; font-size:100%;"><a  href="javascript:void(null)" onclick="$('show_more_works').style.display='none';$('more_works').style.display='block';" class="blue" style="font-weight:bold;"><img src="/images/triangle_grey.gif" alt="" width="4" height="11" border="0" style="margin-right:4px;" />Остальные работы</a></div>
                                            <?
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    </table>
                                </div>
                            <?php } ?>
                            <div id="works_add">
                                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10 b-layout__txt_fontsize_11"><? if ($cur_prof > 0) { ?>Или загрузить и показать другие работы<? } else { ?>Загрузить и показать примеры работ<? } ?>:</div>
                                <iframe style="width:100%;height:25px;" scrolling="no" id="fupload" name="fupload" src="/projects/upload.php?pid=<?=$prj_id?>" frameborder="0"></iframe>
                                <div class="b-layout__txt b-layout__txt_fontsize_11">Максимальный размер файла 2 Мб.</div>
                            </div>
                        </div>
                    <?php endif; // if ($is_pro2) ?>
                    
                    <?php
                    $op_sum = $answers->color_fm_sum;
                    $payed_color = true; //($ps['payed_items'][0] == '1');
                    ?>
                    <?php /*script type="text/javascript">
                    var ac_sum = <?= _bill($_SESSION['ac_sum']);?>;
                    function check_sum() {
                        var val = <?= round($op_sum, 2)?>;
                        var is_color = $('select-color').checked;
                        if(val > ac_sum && is_color) {
                            var sum = Number(val - ac_sum).toPrecision(2);
                            alert('На вашем счету не хватает ' + sum + ' ' + ending(sum, 'рубль', 'рубля', 'рублей'));
                            return false;
                        }
                        return true;
                    }
                    </script */?>
                    <a name="is_color"></a>
                    <?php  /*<div class="detach">
                        div class="form fs-o">
                            <b class="b1"></b>
                            <b class="b2"></b>
                            <div class="form-in c">
                                <div class="form-item <?= ($_SESSION['ac_sum'] < $op_sum && !$payed_color ? 'form-disabled':'')?>">
                                    <input name="is_color" type="checkbox" value="1" class="form-checkbox" id="select-color" <?= ($_SESSION['ac_sum'] < $op_sum && !$payed_color? 'disabled="disabled"':'')?> <?= $ps['is_color']=='t'?'checked="checked"':''?>/>
                                    <label for="select-color">Выделить предложение цветом <?php if(!$payed_color) {?><em><?= _bill($op_sum)?> рублей</em><?php }//if?></label>
                                </div>
                                <?php if(!$payed_color) {?>
                                <div class="no-money">
                                    <?php if ($_SESSION['ac_sum'] < $op_sum) {?>
                                    <p><img src="/images/warning.png" alt="" /><strong>Недостаточно средств на счету!</strong> В данный момент на счету <?= _bill($_SESSION['ac_sum'])?> рублей. <a href="/bill/">Пополнить счет</a></p>
                                    <?php } //if?>
                                    <span><b>Обратите внимание!</b> С вашего счета будет списана сумма в момент добавления предложения без дополнительного подтверждения.<br />Более подробную информацию о платных услугах можете <a href="#">прочитать в разделе помощи</a>.</span>
                                </div>
                                <?php }//if?>
                            </div>
                            <b class="b2"></b>
                            <b class="b1"></b>
                         </div>
                    </div>*/ ?>
                    
                    
                    <div id="works_submit" class="b-buttons b-buttons_padleft_110 b-buttons_padtop_20 b-layout__txt_padleft_null_ipad">
                       <?=$user_phone_projects?>
						<a href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" <?php if(!$payed_color) {?>onclick="if(check_sum()) {this.disabled=true;  _gaq.push(['_trackEvent', 'User', 'Freelance', 'button_project_create_offer']); ga('send', 'event', 'Freelance', 'button_project_create_offer'); $('form_add_offer').submit();}"<?php } else { ?>onclick="this.disabled=true;  _gaq.push(['_trackEvent', 'User', 'Freelance', 'button_project_create_offer']); ga('send', 'event', 'Freelance', 'button_project_create_offer'); $('form_add_offer').submit();"<?php }//else?>  ><? echo (is_numeric(InGet('edit')))?'Сохранить ответ':'Опубликовать ответ'; ?></a>
                       <span class="b-buttons__txt">&#160;&#160;&#160;<a class="b-buttons__link" href="#">Отменить публикацию</a></span>
                    </div>
                    </form>
                 </div>
                 
			<?php } else if ($user_offer_exist) { $is_user_offer = true;// Предложение от данного юзера уже есть. {?>

			<!-- answer-item -->
			     <a name="new_offer"></a>
                 <? $is_end = count($offers) == 0?true:false; ?>
    			<?php $value = $user_offer; include $_SERVER['DOCUMENT_ROOT']."/projects/tpl.prj-answer-item.php"; ?>    
    			<!--/answer-item -->
			<?php } //else if ?>
			
			<?php $offers_count = count($offers) - 1; ?>
			<?php if (isset($offers) && is_array($offers) && ($offers_count >= 0)) { $is_user_offer = false;  ?>
                <?php foreach ($offers as $key => $value) { unset($is_end); if($key == $offers_count) $is_end = true;?>
                    <?php if( ( ($value['refused'] == 't' && $value['refuse_reason'] == 0) || $value['is_deleted'] == 't') && !hasPermissions('projects')) continue; ?>
					<?php if (!$user_offer_exist || ($value['id'] != $user_offer['id'])) {?>
					<!-- answer-item -->
        			<?php include $_SERVER['DOCUMENT_ROOT']."/projects/tpl.prj-answer-item.php"; ?>    
        			<!--/answer-item -->
					<?php } //if?>
                <?php } //foreach?>
                <? if($count_hidden_offers!=0) { ?>
        				<h2 class="b-layout__title">Остальные ответы фрилансеров скрыты и видны только заказчику</h2>
                <? } ?>
                <?php // Страницы
                $pagesCount = ceil($num_offers / MAX_OFFERS_AT_PAGE);
                $href = '%s/projects/index.php?pid='. $prj_id;
                if (isset($po_sort)) $href .= '&sort=' . $po_sort;
                if (isset($po_type)) $href .= '&type=' . $po_type;
                $href .= '&page=%d%s';
                if(new_paginator2($item_page, $pagesCount)) {
                    echo new_paginator2($item_page, $pagesCount, 3, $href);
                }
                ?>
		    <?php } else { $offers_count = count($offers) - 1; //if?>
				<?php if ($uid > 0 || isset($offers) && is_array($offers) && $offers_count >= 0) { ?>
                <?php } //if?>
            <?php } //else ?>

</div>
 
 <div class="b-layout b-layout_padtop_20">
    <h2 class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_666 b-layout_top_100 b-layout__txt_padbot_10 b-layout__txt_weight_normal">
        <?= SeoTags::getInstance()->getFooterText() ?>
    </h2>
</div>  

<script  type="text/javascript">
    var offer_works = [];
    <?php for ($i = 1; $i <= 3; $i++): ?>
        <?php if ($ps['portfolio_work_' . $i] != ''): ?>
            offer_works.push([<?=$ps['portfolio_work_' . $i . '_id']?>, '<?=$ps['portfolio_work_' . $i]?>', '<?=$ps['portfolio_work_' . $i . '_prev_pict']?>']);
        <?php endif; ?>
    <?php endfor; ?>

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

<?php

//показываем попап успешной покупки ПРО после редиректа
$quickPRO_type = 'project'; 
require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_pro_win.php"); 

?>

<? if($quick_verification==1 || $_GET['vok'] || $_GET['verror']) { $quick_verification_type = 'project'; require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_verification.php"); } ?>