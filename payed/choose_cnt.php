<h1>Услуги</h1>
<table width="100%" border="0" cellspacing="0" cellpadding="29">
<tr valign="top">
	<td height="400" valign="top" bgcolor="#FFFFFF" class="box2">
	<div align="center"><h1>Аккаунт &ldquo;PRO&rdquo;</h1></div>
	Выберите разделы, в которых необходимо размещение вашего аккаунта в зоне &ldquo;PRO&rdquo;<br>
		<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
$prfs = new payed();
$profs = $prfs->GetPro(get_uid(), (__paramInit('int', 'd') . ' month'));
?>
<form action="/payed/" method="post">
<table border="0" cellspacing="0" cellpadding="1" style="margin: 10px 0 10px -5px;">
	<tr>
		<td valign="top"><input type="checkbox" id="gen" name="prof[]" value="0"></td>
		<td width="100%"><LABEL for="gen">Общий каталог фрилансеров</LABEL></td>
	</tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" style="margin-left: -5px;">
<? 
	$iter = 0;
	$size = sizeof($profs);
	$prof = $profs[$iter++];
	while ($iter <= $size){
		$i = 0;
		if (!$prof) break;
 ?>
<tr>
	<td width="14">&nbsp;</td>
	<? while ($i < 3){ 
		if ($prof['id'] == 0){
			$prof = $profs[$iter++];
		}
		$lastgrname = $prof['groupname'];
		if (!$lastgrname) break;
	?>
	<td width="35%" valign="top">
		<table border="0" cellspacing="0" cellpadding="1" style="margin-left: -2px;">
		<tr><td colspan="2">&nbsp;<strong><?=$prof['groupname']?></strong></td></tr>
		<? do {?>
		<tr><td valign="top"><input type="checkbox" id="prof<?=$prof['id']?>" name="prof[]" value="<?=$prof['id']?>" <? if ($prof['checked']) print "checked"?>></td>
			<td width="100%"><LABEL for="prof<?=$prof['id']?>"><?=$prof['profname']?></LABEL></td></tr>
		<? 
		$prof = $profs[$iter++];
		} while ($lastgrname == $prof['groupname']) ?>
		</table>
	</td>
	<? $i++; }?>
	<td width="14">&nbsp;</td>
</tr>
<tr><td colspan="2" height="20">&nbsp;</td></tr>
<? } ?>
</table>
<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
<tr>
	<td width="19">&nbsp;</td>
	<td height="40" align="right" valign="top">
		<input type="hidden" name="transaction_id" value="<?=__paramInit('int', 'trid')?>"><input type="hidden" name="time" value="<?=__paramInit('int', 'd')?>"><input type="hidden" name="tarif" value="<?=__paramInit('int', 't')?>"><input type="hidden" name="action" value="portf_choise"><input type="submit" name="btn" class="btn" value="Выбрать">
	</td>
	<td width="19">&nbsp;</td>
</tr>
</table></form>
	</td>
</tr>
</table>
