<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
$prfs = new professions();
$uid = $user->GetUid($err);
$error .= $err;
$profs = $prfs->GetSelProf($uid);
$portf = new portfolio();
$prjs = $portf->GetPortf($uid);
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

	function viewprj(num, profid, whom){
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
	
	function viewprof(profid){
		out = "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">";
	 	j = 0;
		for (i = 1; i <= prjnum; i++){
			if (prjprof_id[i-1] == profid || profid == -1){
			j++;
			out += "<td width=\"15\">"+j+".</td>\
			<td><a href=\"/users/<?=$user->login?>/viewproj.php?prjid="+prjid[i-1]+"\" target=\"_blank\" class=\"blue\">"+prjname[i-1]+"</a></td>\
			</tr>";
			}
			curprjnum = i;
		}
		out +="</table>";
		
		if (profid == -1) { proflinkall.style.fontWeight = 'bold'; profname.innerHTML = "Все портфолио";}
		else {proflinkall.style.fontWeight = 'normal';}
		for (i = 0; i<profnum; i++){
			a = document.getElementById('proflink'+prof_ids[i]);
			if (prof_ids[i] == profid){
				a.style.fontWeight = 'bold';
				profname.innerHTML = profnames[i];
			} else {
				a.style.fontWeight = 'normal';
			}
		}
		projects.innerHTML = out;
	}
	
//-->
</script>
<? if (($_SESSION['login'] == $user->login) && ($user->is_pro != 't')) { ?>
<?=view_error4('Внимание! Вы отображаетесь в каталоге только по своей специализации. Чтобы увеличить количество специализаций, необходимо перейти на аккаунт ' . view_pro()); ?>
<? } ?>
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
	<td>
		<div id="projects" style="visibility: visible;">
		</div>
	</td>
	<td width="16">&nbsp;</td>
</tr>
<tr><td colspan="3" height="20">&nbsp;</td></tr>
</table>

