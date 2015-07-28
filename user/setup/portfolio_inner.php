<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
$prfs = new professions();
$profs = $prfs->GetSpecs($user->login);
$size = sizeof($profs);
$portf = new portfolio();
$prjs = $portf->GetPortf($user->uid, "NULL", true);
if (!$prjs) include("portfolio_in_setup.php");
	else {
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/portfoliopos.common.php");
?>
<? $xajax->printJavascript('/xajax/'); ?>
<script language="JavaScript1.2" type="text/javascript">
<!--

errmsg1 = errmsg2 = errmsg3 = errmsg4 = errmsg5 = errmsg6 = '';
can_move = 1;

<? if ($error_flag) {
	if ($alert[1]) print("errmsg1=\"".ref_scr(view_error($alert[1]))."\";");
	if ($alert[2]) print("errmsg3=errmsg2=\"".ref_scr(view_error($alert[2]))."\";");
	if ($alert[3]) print("errmsg3=\"".ref_scr(view_error($alert[3]))."\";");
	if ($alert[4]) print("errmsg4=\"".ref_scr(view_error($alert[4]))."\";");
	if ($alert[5]) print("errmsg5=\"".ref_scr(view_error($alert[5]))."\";");
	if ($alert[6]) print("errmsg5=\"".ref_scr(view_error($alert[6]))."\";");
	if ($alert[7]) print("errmsg6=\"".ref_scr(view_error($alert[7]))."\";");
?>
window.addEvent('domready', function() {
    if($$('div.errorBox')) {
        new Fx.Scroll(window).toElement($$('div.errorBox')[0].getPrevious());
    }
});
<? } ?>

function setform(){
errmsg3 = errmsg3;
edfrm = "<table width='100%' border='0' cellspacing='0' cellpadding='0' class='edit'>\
<tr>\
	<td rowspan='10' width='19'>&nbsp;<\/td>\
	<td height='25' colspan='2' id='ff'><strong>Изменить работу<\/strong><\/td>\
	<td rowspan='9' width='19'>&nbsp;<\/td>\
<\/tr>\
<tr>\
	<td height='20' valign='bottom' colspan='2'>Название:<\/td>\
<\/tr>\
<tr>\
	<td width='390' height='25'><input type='text' id='pname' name='pname' maxlength='80' class='wdh100'>"+errmsg1+"<\/td>\
	<td width='80' align='right'>Максимум<br> 80 символов<\/td>\
<\/tr>\
<tr>\
	<td style='height:25px; vertical-align:middle;padding-top:20px;padding-bottom:12px;'>Укажите стоимость разработки\
	<select name='pcosttype' id='pcosttype'><option value='0'>USD<\/option><option value='1'>Euro<\/option><option value='2'>Руб<\/option><\/select>\
	<input type='text' id='pcost' name='pcost' maxlength='9' style='width:65px;margin-right:16px;'> и временные затраты <input type='text' id='ptime' name='ptime' maxlength='6' style='width:50px;'> <select id='ptimeei' name='ptimeei'><option value='1'>в часах</option><option value='1'>в днях</option></select>"+errmsg4+errmsg5+"<\/td>\
	<td width='80' align='right'><\/td>\
<\/tr>\
<tr>\
	<td height='20' valign='bottom' colspan='2'>Загрузить:<\/td>\
<\/tr>\
<tr>\
	<td height='65' colspan='2'><input type='hidden' name='MAX_FILE_SIZE' value='10485760'><input type='file' name='img' size='74'><br>"+errmsg3+"\
	С помощью этого поля возможно загрузить:<br>\
	Файл размером до 10 Мб. Флеш-файлы и картинки весом более 1 Мб открываются в новом окне.<br>\
	Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
	<\/td>\
<\/tr>\
<tr>\
	<td height='20' valign='bottom' colspan='2'>Ссылка:<\/td>\
<\/tr>\
<tr>\
	<td height='25'>\
		<table width='100%' border='0' cellspacing='0' cellpadding='0'>\
		<tr><td width='40'>http://&nbsp;<\/td>\
		<td><input type='text' id='link' name='link' class='wdh100' style='position:relative'><\/td><\/tr>\
		<\/table>\
	"+errmsg2+errmsg6+"<\/td>\
	<td>&nbsp;<\/td>\
<\/tr>\
<tr>\
	<td height='20' valign='bottom' colspan='2'>Описание:<\/td>\
<\/tr>\
<tr>\
	<td height='110' valign='top'><textarea cols='74' rows='7' name='descr' class='wdh100' onkeydown='if (this.value.length > 1500) this.value=this.value.slice(0, 1500)'><\/textarea>"+errmsg2+"<\/td>\
	<td align='right'>Максимум<br> 1500 символов<\/td>\
<\/tr>\
<tr>\
	<td colspan='2'><INPUT class='radio' checked name='prev_type' type='radio' value='0' id='prev_type1'><LABEL for='prev_type1'>Графическое превью</LABEL> &nbsp; &nbsp; <INPUT class='radio' name='prev_type' type='radio' value='1' id='prev_type2'><LABEL for='prev_type2'>Текстовое превью</LABEL><\/td>\
<\/tr>\
<\/table>\
<table width='100%' border='0' cellspacing='0' cellpadding='0' bgcolor='#FFFFFF'>\
<tr>\
	<td width='19' height='40'>&nbsp;<\/td>\
	<td align='right'>\
		<input type='hidden' name='prof' value=''><input type='hidden' name='prjid' value=''>\
		<input type='hidden' name='u_token_key' value='<?=$_SESSION['rand']?>'>\
		<input type='button' name='btn' class='btn' value='Удалить' onClick='if (warning(5)) {frm.action.value=\"portf_del\"; frm.Submit();} else return(false);'>\
		<input type='submit' name='btn1' class='btn' value='Сохранить'><\/td>\
	<td width='19'>&nbsp;<\/td>\
<\/tr>\
<\/table>";
}

setform();

prjn = new Array();
prjname = new Array();
prjlink = new Array();
prjdescr = new Array();
prjid = new Array();
prjprevtype = new Array();

prof_ids = new Array();
profnames = new Array();
prjinprof = new Array();


<?
	$ilast = $i = 0;
	$lastprof = -1;
	$j = 0;
	if ($prjs) foreach($prjs as $ikey=>$prj){
	 if ($prj['id']){
		print ("prjn[".$prj['id']."] = '".$i."';\nprjid[$i] = '".$prj['id']."';\nprjname[$i] = '".input_ref_scr($prj['name'])."';\nprjlink[$i] = '".input_ref_scr($prj['link'])."';\nprjdescr[$i] = '".input_ref_scr($prj['descr'])."';\nprjprevtype[$i] = '".$prj['prj_prev_type']."';\n\n");
		$i++;
	 }
	 $curprof = $prj['prof_id'];
	 if ($lastprof != $curprof) {
	 		if ($lastprof != -1 && $i-$ilast > 1) print("prjinprof[".($j-1)."] = '".($i-$ilast)."';\n");
			print ("prof_ids[$j] = '".$prof['prof_id']."';\nprofnames[$j] = '".$prof['name']."';\n\n");
			$j++;
			$ilast = $i;
			$lastprof = $curprof;
		}
	}
	if ($i-$ilast > 0) print("prjinprof[".($j-1)."] = '".($i-$ilast+1)."';\n");
		?>

var prjnum = <?=$i?>;
var profnum = <?=$j?>;
var lastobj = 0;
<?
	if ($action == "portf_change" || $error_flag || $error){
 ?>
	//window.navigate("#prof<?=$prof?>");
<? } ?>

	function editprj(num, profid)
	{
    errmsg1 = errmsg2 = errmsg3 = errmsg4 = errmsg5 = errmsg6 = '';
		if (lastobj != 0) {document.getElementById(lastobj).innerHTML = ""; document.getElementById[lastobj].style.backgroundColor="#FFFFFF";}
		ed=document.getElementById('editform'+profid);
		ed.innerHTML = edfrm;
		ed.style.visibility='visible';
		ed.style.backgroundColor="#FFE5D5";
		j = 0;
		if (document.getElementById('ff'))
			document.getElementById('ff').innerHTML = "<strong>Изменить работу<\/strong>";
		lastobj = 'editform'+profid;
		for (i = 0; i<prjnum; i++){
				img=document.getElementById('pi'+i);
				a1=document.getElementById('ap1'+i);
				a2=document.getElementById('ap2'+i);
				if (i == num) {
					a1.style.fontWeight = 'bold';
					img.src='/images/ico_setup_d.gif';
					a2.style.fontWeight = 'bold';
					objnum = document.getElementById('frm').elements.length;
					document.getElementById('frm').pname.value = prjname[i];
					document.getElementById('frm').link.value = prjlink[i];
					document.getElementById('frm').descr.value = prjdescr[i];
					document.getElementById('frm').prjid.value = prjid[i];
					document.getElementById('frm').prof.value = profid;
					document.getElementById('frm').pcosttype.selectedIndex=prjcosttype[i];

					if (prjprevtype[i] == 0) {
						document.getElementById('frm').prev_type1.checked = true;
					} else{
						document.getElementById('frm').prev_type2.checked = true;
					}
				}
				else {
				a1.style.fontWeight = 'normal';
				img.src='\/images\/ico_setup.gif';
				a2.style.fontWeight = 'normal';
				}
		}
	}
	
	function viewprof(profid){
		document.getElementById('frm').pname.value = "";
		document.getElementById('frm').link.value = "";
		document.getElementById('frm').descr.value = "";
		document.getElementById('frm').prjid.value = "";

		for (i = 0; i<prjnum; i++){
				img=document.getElementById('pi'+i);
				a1=document.getElementById('ap1'+i);
				a2=document.getElementById('ap2'+i);
				a1.style.fontWeight = 'normal';
				img.src='\/images\/ico_setup.gif';
				a2.style.fontWeight = 'normal';
				}
	}
	
	function addprj(profid){
		if (lastobj != 0) {document.getElementById(lastobj).innerHTML = ""; document.getElementById[lastobj].style.backgroundColor="#FFFFFF";}
		lastobj = 'editform'+profid;
		ed=document.getElementById('editform'+profid);
		ed.innerHTML = edfrm;
		ed.style.backgroundColor="#FFF7DD";
		document.getElementById('ff').innerHTML = "<strong>Добавить работу<\/strong>";
		ed.style.visibility='visible';
		document.getElementById('frm').btn.style.visibility='hidden';
		document.getElementById('frm').prof.value = profid;
		viewprof(profid);
	}
	
	
	function delprj(){
		document.getElementById('frm').action.value="portf_del";
		document.getElementById('frm').submit();
	}
	
	function changeProfPos(profid, direc){
		if (can_move){
			can_move = 0;
			xajax_ChangeProfPos(profid, direc);
		}
	}
	
	function changePos(pid, direc){
		if (can_move){
			can_move = 0;
			xajax_ChangePos(pid, direc);
		}
	}
	
	function submit_ch(val){
		document.getElementById('frm').action.value="cancomm_ch";
		document.getElementById('frm').submit();
	}
	
//-->
</script>
<form action="." method="post" enctype="multipart/form-data" name="frm" id="frm" onSubmit="if(allowedExt(this['logo'].value)) {this.btn.value='Подождите'; this.btn.disabled=true; this.btn1.value='Подождите'; this.btn1.disabled=true;} return false;">
<input type="hidden" name="action" value="portf_change">
<? if ($error) print(view_error($error));?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="19" height="40">&nbsp;</td>
	<td><img src="/images/ico_setup.gif" alt="" width="6" height="9" border="0">&nbsp;&nbsp;<a href="/users/<?=$user->login?>/setup/portfsetup/">Изменить разделы</a></td>
	<td width="19" rowspan="2">&nbsp;</td>
	<td valign="bottom"><? if ($info) { ?><br><?=view_info($info)?><br><? } ?><strong>Специализация:</strong>&nbsp;&nbsp;<?=professions::GetProfNameWP($user->spec, ' / ',  'Нет специализации')?>&nbsp;&nbsp;&nbsp;<a href="/users/<?=$user->login?>/setup/specsetup/" id="ap11"><img src="/images/ico_setup.gif" alt="" width="6" height="9" border="0"> Изменить</a></td>
<? /*
	<td align="right"><input type="checkbox" name="show" value="1" onClick="submit_ch(this.value);" <? if ($user->portf_comments == 't') print "checked"?>> Разрешить оставлять комментарии к работам</td>
*/ ?>
	<td width="19">&nbsp;</td>
</tr>
</table>
<?
			$lastprof = -1;
			$j = 0;
			$k = -1;
			if ($prjs) foreach($prjs as $ikey=>$prj){
			$curprof = $prj['prof_id'];
			if ($lastprof != $curprof) {
				$i = 1;
				$k++;
				if ($lastprof != -1) {
				?>
		</table>
	</td>
	<td width="14">&nbsp;</td>
</tr>
<tr><td height="10" colspan="3">&nbsp;</td></tr>
</table></div>
				<? } ?>
<a name="prof<?=$curprof?>" id="prof<?=$curprof?>"></a>
<div class="sprof<?=$prj['prof_id']?>">
<table width="100%" border="0" cellspacing="0" cellpadding="0" >
<tr>
	<td width="19" height="20" class="brdtop">&nbsp;</td>
	<td class="brdtop"><img src="/images/ico_up.gif" alt="" width="9" height="9" border="0" onClick="changeProfPos(<?=$prj['prof_id']?>, '1');">
	 <img src="/images/ico_down1.gif" alt="" width="9" height="9" border="0" onClick="changeProfPos(<?=$prj['prof_id']?>, '-1');"> <?=$prj['profname']?></td>
	<td align="right" class="brdtop"><a href="#prof<?=$curprof?>" onClick="addprj('<?=$curprof?>');"><img src="/images/ico_plus.gif" alt="" width="9" height="9" border="0"></a>&nbsp;&nbsp;<a href="#prof<?=$curprof?>" onClick="addprj('<?=$curprof?>');">Добавить работу</a></td>
	<td width="19" height="20" class="brdtop">&nbsp;</td>
</tr>
</table>
<div id="editform<?=$curprof?>" style="visibility: hidden;">&nbsp;</div>
<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
<tr>
	<td width="14">&nbsp;</td>
	<td>
		<table width="100%" border="0" cellspacing="0" cellpadding="3">
		
<?		$lastprof = $curprof;
			}
			if ($prj['id']) {
				if ($error_flag && $prj_id == $prj['id']) $errprjnum = $j;
		?>
		<tr>
			<td id="sproj<?=$prj['id']?>a" width="6" height="20"><img src="/images/ico_setup.gif" alt="" name="pi<?=$j?>" id="pi<?=$j?>" width="6" height="9" border="0"></td>
			<td id="sproj<?=$prj['id']?>b" width="50"><a href="#prof<?=$curprof?>" name="ap1<?=$j?>" id="ap1<?=$j?>" onClick="editprj(<?=$j?>,<?=$curprof?>);">Изменить</a></td>
			<td id="sproj<?=$prj['id']?>c" width="15" align="right"><img src="/images/ico_up.gif" alt="" width="9" height="9" border="0" onClick="changePos(<?=$prj['id']?>, '1');"></td>
			<td id="sproj<?=$prj['id']?>d" width="15"><img src="/images/ico_down1.gif" alt="" width="9" height="9" border="0" onClick="changePos(<?=$prj['id']?>, '-1');"></td>
			<td id="sproj<?=$prj['id']?>e" width="15" align="right" id="num"><?=$i?>.</td>
			<td id="sproj<?=$prj['id']?>f"><a href="/users/<?=$user->login?>/viewproj.php?prjid=<?=$prj['id']?>" target="_blank" class="blue"><?=hyphen_words($prj['name'])?></a>
<? /*
				<? if ($prj['show_comms'] == 't') {?> | <a href="/users/<?=$user->login?>/comments/?tr=<?=$prj['id']?>" style="color: #666666;">Комментарии (<?=zin($prj['comms'])?>)</a><? } ?>
*/ ?>
				<input type="hidden" name="pos[]" value="<?=$prj['id']?>"><input type="hidden" name="profid[]" value="<?=$prj['id']?>">
			</td>
		</tr>
		<? $i++; $j++;}
		 else { ?>
		<tr>
			<td height="20" colspan="6" align="center">Нет работ в этом разделе</td>
		</tr>
		<?
		}
		 } ?>
		</table>
	</td>
	<td width="14">&nbsp;</td>
</tr>
</table>
</form>
<?
if ($error_flag) { ?>

	<script type="text/javascript">
<!--
	<? if ($prj_id) { ?>
	editprj(<?=$errprjnum?>,<?=$prof?>);
	<? } else { ?>
	addprj(<?=$prof?>);
	<? } ?>
	document.getElementById('frm').pname.value = "<?=$name?>";
	document.getElementById('frm').pcost.value = "<?=$pcost?>";
	document.getElementById('frm').ptime.value = "<?=$ptime?>";
	document.getElementById('frm').ptimeei.value = "<?=$ptimeei?>";
	document.getElementById('frm').link.value = "<?=$link?>";
	document.getElementById('frm').descr.value = "<?=$descr?>";
	//window.navigate('#prof<?=$prof?>');
	errmsg1 = errmsg2 = errmsg3 = errmsg4 = errmsg5 = errmsg6 = '';
	setform();
//-->
</script>
<? } ?>
	<script language="JavaScript" type="text/javascript">
<!--

  setInterval("check_111()", 10);
  var msg=document.getElementById('ab_text_msg');
  var area=document.getElementById('ab_text');

  function check_111()
  {
  	if(area.value.length > 300)
  	{
  		area.value = area.value.substr(0, 300);
    	msg.innerHTML = '<? print(ref_scr(view_error('Исчерпан лимит символов для поля (300 символов)'))); ?>';
  	}
  }
//-->
</script>
<? } ?>
