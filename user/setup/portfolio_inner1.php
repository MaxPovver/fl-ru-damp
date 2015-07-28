<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
$prfs = new professions();
$profs = $prfs->GetSelProf(get_uid());
if (!$profs) include("portfolio_in_setup.php");
	else {
		$portf = new portfolio();
		$prjs = $portf->GetPortf(get_uid());
?>
<script language="JavaScript1.2" type="text/javascript">
<!--
prjprof_id = new Array();
prjname = new Array();
prjlink = new Array();
prjdescr = new Array();
prjid = new Array();

prof_ids = new Array();
profnames = new Array();

<?
	$i = 0;
	if ($prjs) foreach ($prjs as $prj){
		print ("prjprof_id[$i] = '".$prj['prof_id']."';\nprjid[$i] = '".$prj['id']."';\nprjname[$i] = '".$prj['name']."';\nprjlink[$i] = '".$prj['link']."';\nprjdescr[$i] = '".$prj['descr']."';\n\n");
		$i++;
	}
	$j = 0;
	if ($profs) foreach($profs as $prof){
		print ("prof_ids[$j] = '".$prof['prof_id']."';\nprofnames[$j] = '".$prof['name']."';\n");
		$j++;
	}
		?>

var prjnum = <?=$i?>;
var profnum = <?=$j?>;
var pmin = -1; var pmax = -1;

	function editprj(num, profid, whom){
		ff.innerHTML = "<strong>Изменить работу</strong> <a href=\"#\" onclick=\"delprj();\">Удалить</a>";
		tedit.style.backgroundColor="#FFE5D5";
		editform.style.visibility='visible';
		j = 0;
		for (i = 1; i<=prjnum; i++){
			if (prjprof_id[i-1] == profid || profid == -1){
				j++;
				img=document.getElementById('pi'+j);
				a1=document.getElementById('ap1'+j);
				a2=document.getElementById('ap2'+j);
				if (i == num) {
				a1.style.color = '#909090';
				img.src='/images/ico_setup_d.gif';
				a2.style.fontWeight = 'bold';
				frm.pname.value = prjname[i-1];
				frm.link.value = prjlink[i-1];
				frm.descr.value = prjdescr[i-1];
				frm.prjid.value = prjid[i-1];
				}
				else {
				a1.style.color = '#000000';
				img.src='/images/ico_setup.gif';
				a2.style.fontWeight = 'normal';
				}
			}
		}
	}
	
	function addprj(profid){
		ff.innerHTML = "<strong>Добавить работу</strong>";
		tedit.style.backgroundColor="#FFF7DD";
		editform.style.visibility='visible';
		viewprof(profid);
	}
	
	function viewprof(profid){
		frm.pname.value = "";
		frm.link.value = "";
		frm.descr.value = "";
		frm.prjid.value = "";
		out = "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">";
	 	j = 0;
		for (i = 1; i <= prjnum; i++){
			if (prjprof_id[i-1] == profid || profid == -1){
			if (pmin = -1) pmin = i-1;
			pmax = i-1;
			j++;
			out += "<tr><td width=\"6\" height=\"20\"><img src=\"/images/ico_setup.gif\" alt=\"\" name=\"pi"+j+"\" id=\"pi"+j+"\" width=\"6\" height=\"9\" border=\"0\"></td>\
			<td width=\"50\"><a href=\"#edit\" name=\"ap1"+j+"\" id=\"ap1"+j+"\" onClick=\"editprj("+i+","+profid+",this);\">Изменить</a></td>";
			if (profid != -1){
				out +="<td align=\"right\"><img src=\"/images/ico_up.gif\" alt=\"\" width=\"9\" height=\"9\" border=\"0\" onClick=\"changepos("+i+", 'up', "+profid+");\"></td>\
				<td><img src=\"/images/ico_down1.gif\" alt=\"\" width=\"9\" height=\"9\" border=\"0\" onClick=\"changepos("+i+", 'down', "+profid+");\"></td>";
			}
			out += "<td align=\"right\">"+j+".<input type=\"hidden\" name=\"pos[]\" value=\""+j+"\"><input type=\"hidden\" name=\"profid[]\" value=\""+prjid[i-1]+"\"></td>\
			<td colspan=\"2\"><a name=\"ap2"+j+"\" id=\"ap2"+j+"\" href=\"#edit\" class=\"blue\" onClick=\"editprj("+i+","+profid+",this);\">"+prjname[i-1]+"</a></td>\
			</tr>";
			}
			curprjnum = i;
		}
		
		if (profid != -1){
			out +="<tr>\
				<td colspan=\"2\">&nbsp;</td>\
				<td align=\"right\" colspan=\"3\"><a href=\"#\" id=\"possave\" onClick=\"preposchange();\" class=\"small\">Сохранить</a></td>\
				<td colspan=\"2\">&nbsp;</td>\
			</tr>\
			<tr>\
				<td align=\"right\" colspan=\"7\" height=\"20\"><img src=\"/images/ico_plus.gif\" alt=\"\" width=\"9\" height=\"9\" border=\"0\">&nbsp;&nbsp;<a href=\"#edit\" onClick=\"addprj('"+profid+"');\">Добавить работу</a></td>\
			</tr>";
		}
		out += "</table>";
		
		if (profid == -1) { proflinkall.style.fontWeight = 'bold'; profname.innerHTML = "Все портфолио";}
		else {proflinkall.style.fontWeight = 'normal';}
		for (i = 0; i<profnum; i++){
			a = document.getElementById('proflink'+prof_ids[i]);
			if (prof_ids[i] == profid){
				a.style.fontWeight = 'bold';
				frm.prof.value = profid;
				profname.innerHTML = profnames[i];
			} else {
				a.style.fontWeight = 'normal';
			}
		}
		projects.innerHTML = out;
	}
	
	function changepos(num, dest, profid){
		if (dest == 'down') {
			if (num > pmax) return;
			var temp = prjprof_id[num-1];
			prjprof_id[num-1] = prjprof_id[num];
			prjprof_id[num] = temp;
			temp = prjname[num-1];
			prjname[num-1] = prjname[num];
			prjname[num] = temp;
			temp = prjlink[num-1];
			prjlink[num-1] = prjlink[num];
			prjlink[num] = temp;
			temp = prjdescr[num-1];
			prjdescr[num-1] = prjdescr[num];
			prjdescr[num] = temp;
			temp = prjid[num-1];
			prjid[num-1] = prjid[num];
			prjid[num] = temp;
		}
		if (dest == 'up') {
			if (num < pmin-2) return;
			var temp = prjprof_id[num-2];
			prjprof_id[num-2] = prjprof_id[num-1];
			prjprof_id[num-1] = temp;
			temp = prjname[num-2];
			prjname[num-2] = prjname[num-1];
			prjname[num-1] = temp;
			temp = prjlink[num-2];
			prjlink[num-2] = prjlink[num-1];
			prjlink[num-1] = temp;
			temp = prjdescr[num-2];
			prjdescr[num-2] = prjdescr[num-1];
			prjdescr[num-1] = temp;
			temp = prjid[num-2];
			prjid[num-2] = prjid[num-1];
			prjid[num-1] = temp;
		}
		viewprof(profid);
		possave.style.visibility='visible';
	}
	
	function preposchange(){
		objnum = posctrl.elements.length;
		var j = 1;
		for (i = 0; i<objnum; i++){
			if (posctrl.elements[i].name == 'pos[]'){
				posctrl.elements[i].value = j;
				j++;
			}
		}
		posctrl.submit();
	}
	
	function delprj(){
		frm.action.value="portf_del";
		frm.submit();
	}
	
//-->
</script>
<table width="100%" cellspacing="0" cellpadding="0" bgcolor="FFFFFF">
<tr><td height="20" colspan="3">&nbsp;</td></tr>
<tr>
	<td width="14">&nbsp;</td>
	<td>
		<table width="100%" border="0" cellspacing="0" cellpadding="4">
		<?
			if ($profs) foreach($profs as $prof){
		?>
		<tr>
			<td width="11"><img src="/images/ico_down.gif" alt="" width="11" height="11" border="0"></td>
			<td><a href="#" class="blue" id="proflink<?=$prof['prof_id']?>" onClick="viewprof(<?=$prof['prof_id']?>);"><?=$prof['name']?></a></td>
		</tr>
		<? } ?>
		<tr>
			<td width="11"><img src="/images/ico_down.gif" alt="" width="11" height="11" border="0"></td>
			<td><a href="#" class="blue" id="proflinkall" onClick="viewprof(-1);">Все портфолио</a></td>
		</tr>
		</table>
	</td>
	<td align="right" valign="top" style="padding-top: 4px;"><img src="/images/ico_setup.gif" alt="" width="6" height="9" border="0">&nbsp;&nbsp;<a href="../portfsetup/">Редактировать</a></td>
	<td width="14">&nbsp;</td>
</tr>
<tr><td colspan="3" height="20">&nbsp;</td></tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="19" height="20" class="brdtop">&nbsp;</td>
	<td class="brdtop" id="profname">&nbsp;</td>
	<td width="19" height="20" class="brdtop">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" bgcolor="FFFFFF">
<tr><td height="20" colspan="3">&nbsp;</td></tr>
<tr>
	<td width="16">&nbsp;</td>
	<td><form action="." method="post" name="posctrl" id="posctrl">
		<input type="hidden" name="action" value="poschange">
		<div id="projects" style="visibility: visible;">
		
		</div>
		</form>
	</td>
	<td width="16">&nbsp;</td>
</tr>
<tr><td colspan="3" height="20">&nbsp;</td></tr>
</table>
<a name="edit" id="edit"></a>
<div id="editform" style="visibility: hidden;"><form action="." method="post" enctype="multipart/form-data" name="frm" id="frm">
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFE5D5" class="edit" id="tedit">
<tr>
	<td rowspan="9" width="19">&nbsp;</td>
	<td height="25" colspan="2" id="ff"><strong>Изменить работу</strong></td>
	<td rowspan="9" width="19">&nbsp;</td>
</tr>
<tr>
	<td height="20" valign="bottom" colspan="2">Название</td>
</tr>
<tr>
	<td width="390" height="25"><input type="text" name="pname" class="wdh100"></td>
	<td width="80" align="right">54 символа</td>
</tr>
<tr>
	<td height="20" valign="bottom" colspan="2">Картинка</td>
</tr>
<tr>
	<td height="25" colspan="2"><input type="hidden" name="MAX_FILE_SIZE" value="3145728"><input type="file" name="img" size="74"></td>
</tr>
<tr>
	<td height="20" valign="bottom" colspan="2">Ссылка</td>
</tr>
<tr>
	<td height="25">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr><td width="40">http://&nbsp;</td>
		<td><input type="text" name="link" class="wdh100"></td></tr>
		</table>
	</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td height="20" valign="bottom" colspan="2">Описание</td>
</tr>
<tr>
	<td height="130"><textarea cols="74" rows="7" name="descr" class="wdh100"></textarea></td>
	<td align="right">300 символов</td>
</tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="19" height="60">&nbsp;</td>
	<td align="right"><input type="hidden" name="prof" value=""><input type="hidden" name="prjid" value=""><input type="hidden" name="action" value="portf_change"><input type="submit" name="btn" value="Сохранить"></td>
	<td width="19">&nbsp;</td>
</tr>
</table>
</div></form>
<? } ?>