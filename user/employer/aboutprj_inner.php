<? 

if ($prj) {?>
<table width="100%" cellspacing="0" cellpadding="19" border="0">
	<tr>
	<td>
	<? if ($prj['closed'] == "t") print("<div align=\"center\">".view_error("Проект закрыт")."</div>")  ?>
	Бюджет: <?=CurToChar($prj['cost'], $prj['currency'])?><br><br>
	<? if ($prj['name']) { ?><strong><?=$prj['name']?></strong><? } ?><br>
	<?=reformat($prj['descr'], 96)?><br><br>
	<? 
	$projects=new projects();
	$attach=$projects->GetAllAttach($prj['id']);
        for ($j=0;$j<count($attach);$j++) {
            if ($attach[$j]['name']) { print '<div class="flw_offer_attach"><a href="/users/'.$prj['login'].'/upload/'.$attach[$j]['name'].'" target="_blank">Загрузить</a> ('.$attach[$j]['ftype'].'; '.ConvertBtoMB($attach[$j]['size']).' )</div>'; }
        }
		 ?>
	<? if ($uid != $_SESSION['uid'] && $prj['closed'] == "f") {?><form action="/contacts/?from=<?=$user->login?>#form" method="post"><input type="hidden" name="prjname" value="<?=($prj['name'] ? $prj['name'] : "заголовок отсутствует") ?>"><input type="submit" name="btn" class="btn" value="Оговорить детали"></form><? } ?>
	<div align="right" style="margin-top: 5px;"><b>[<a href="<?/* Апдейт проектов /blogs/view.php?tr=<?=$prj['thread_id']?>*/?>/projects/?pid=<?=$prj['id']?>" class="blue"><b>Комментарии (<?=zin($prj['comm_count'])?>)</b></a>]</b></div>
	</td>
	</tr>
</table>
<? } else {?>
<table width="100%" border="0" cellspacing="0" cellpadding="19">
<tr valign="top">
	<td height="400" valign="top" bgcolor="#FFFFFF">
	<h1>Проект удален</h1>
К сожалению, этот проект был удален.<br> 
<br>
Электронная почта: <a href="mailto:info@free-lance.ru">info@free-lance.ru</a><br>
	</td>
</tr>
</table>
<? } ?>
