<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if (!hasPermissions('projects'))
  {header ("Location: /404.php"); exit;}
	$action = trim($_GET['action']);
	if (!$action) $action = trim($_POST['action']);
	$can_modify = hasPermissions('projects');
	switch ($action){
		case "delete":
			$id = trim($_GET['prjid']);
      $projects = new projects();
      $projects->DeletePublicProject($id, 0, $can_modify);
		break;
		case "edit":
			$id = trim($_GET['prjid']);
			if ($id) $eprj = projects::GetPrj(0, $id, $can_modify);
		break;
		case "allow":
			$id = trim($_GET['aid']);
			if ($id) $error .= projects::Alow($id);
		break;
		case "prj_change":
			$admin_edit = 1;
			include($rpath."user/employer/setup/newproj.php");
		break;
	}
	
$kind = trim($_GET['kind']);
if (!$page) $page = 1;
$page = intval(trim($_GET['page']));
if (!$page) $page = 1;
$projects = new new_projects();
if (!$kind) $kind = 5;
$prjs = $projects->getProjects($num_prjs, $kind, $page);
?>
<strong>Проекты</strong><br><br>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
<tr valign="bottom" class="qpr">
	<td align="center" class="qph">Дата</td>
	<td class="qph">
	<a href=".?kind=1&page=<?=$page?>" style="margin-left:10px;<? if (!$kind||$kind==1) print " text-decoration: underline;"?>">Фри-ланс</a>
	<a href=".?kind=2&page=<?$page?>" style="margin-left:10px;<? if ($kind == 2) print " text-decoration: underline;"?>">Конкурсы</a>
	<a href=".?kind=4&page=<?$page?>" style="margin-left:10px;<? if ($kind == 4) print " text-decoration: underline;"?>">В офис</a>
	<td class="qph">&nbsp;</td>
</tr>
<?
if ($prjs) foreach ($prjs as $ikey=>$prj){ 
?>
<tr class="qpr">
	<td align="center" class="qp"><?=date("d.m",strtotimeEx($prj['post_date']))?></td>
	<td class="qp">
	<? if ($prj['closed'] != "t") { ?><a name="prj<?=$prj['id']?>" id="prj<?=$prj['id']?>" href="/users/<?=$prj['login']?>/project/?prjid=<?=$prj['id']?>" class="cntb" title="<?=$prj['name']?>"><?=$prj['name']?></a><? } else { ?><strong><?=$prj['name']?></strong><? } ?> [<a href="mailto:<?=$prj['email']?>"><?=$prj['email']?></a>]<br>
	<?=reformat($prj['descr'], 96)?><?=$prj['attach']? "<br><br>".viewattach($prj['login'], $prj['attach'], "upload", $tmp, 0) : ""?><br>
	<br/>
	id <?=$prj['id']?>
	</td>
	<td class="qpr" align="center"><a  href="/public/?step=1&public=<?=$prj['id']?>&red=<?=rawurlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'])?>" class="blue" title="<?=$prj['name']?>">Редактировать</a><br>
	<a href=".?action=delete&prjid=<?=$prj['id']?>&kind=<?=$kind?>" class="blue" title="<?=$prj['name']?>" onclick="return warning(2)">Удалить</a><br>
	<? if ($prj['anon_id']) { ?><a href=".?kind=<?=$kind?>&action=allow&aid=<?=$prj['anon_id']?>&prjid=<?=$prj['id']?>" class="blue" title="<?=$prj['name']?>"><?=($prj['visible'] == 'f')?"Разрешить":"Запретить"?></a><br><? } ?>
	</td>
</tr>
<? } ?>
</table>
<div align="right" style="height: 20px;"><table border="0" cellspacing="1" cellpadding="0" class="pgs">
<tr><? // Страницы
	$pages = ceil($num_prjs / $prjspp);
	if ($pages > 1){
		$maxpages = $pages;
		$i = 1;
		
		if ($pages > 40){
			$i = floor($msg_offset/10)*10 + 1;
			if ($i >= 10 ) $i = $i - 5;
			$maxpages = $i+34;
			if ($maxpages > $pages) $maxpages = $pages;
			if ($maxpages - $i < 39 && $maxpages - 39 > 0) $i = $maxpages - 39;
		}
		
	for ($i; $i <= $maxpages; $i++) {
		if ($i != $msg_offset){
	?>
	<td><a href=".?kind=<?=$kind?>&page=<?=$i?>" class="pages"><?=$i?></a></td>
	<? }
		else {?>
	<td class="box"><?=$i?></td>
	<?	}
	} if ($pages > 40 && $maxpages != $pages){?>
	<td width="5">...</td>
	<td width="5"><a href=".??kind=<?=$kind?>&page=<?=($pages - 1)?>" class="pages"><?=($pages - 1)?></a></td>
	<td width="5"><a href=".?kind=<?=$kind?>&page=<?=$pages?>" class="pages"><?=$pages?></a></td>
	<? }
	} // Страницы закончились?></tr>
</table></div>
<? if($action =="edit") { ?>
<hr width="100%">
<a name="frm" id="frm"></a>

<script type="text/javascript">
	$('checkbox1').checked=false;
	$('checkbox2').checked=false;
	<? if (!$alert[4] && $eprj['payed'] == 0) { ?> 
	showHidden('newOfferPayed1Full',3);
	showHidden('newOfferPayed2Full',3);
	<? } if ($eprj['payed'] == '1') {?>
	showHidden('newOfferPayed2Full',3);
	document.getElementById('checkbox1').checked = true;
	<? } if ($eprj['payed'] == '2') { ?>
	showHidden('newOfferPayed1Full',3);
	document.getElementById('checkbox2').checked = true;
	<? } ?>
</script>
<? } ?>
