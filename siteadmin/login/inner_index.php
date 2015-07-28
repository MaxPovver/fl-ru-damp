<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!hasPermissions('changelogin')) { exit; }

?>
<style type="text/css">
.plain {height:20px; vertical-align:middle;}
</style>

<form action="./" name="daterange" id="goaction" method="POST">
<? if ($error) print view_error($error); ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
    <td align="left" style="font-size: 14px; font-weight: bold">Изменения логинов за <?=(($ds == $de)? $ds: $ds.' &ndash; '.$de)?></td>
        <td align="right" style="text-align:right; padding-right:20px">
        с: <input class="plain" name="ds" value="<?=$ds?>" size="12" style="border: 1px solid #DFDFDF; height: 21px"><a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fStartPop(document.daterange.ds,document.daterange.de);return false;"><img class="PopcalTrigger" align="absmiddle" src="/scripts/DateRange/calbtn.gif" width="34" height="22" border="0" alt=""></a>
        по: <input class="plain" name="de" value="<?=$de?>" size="12" style="border: 1px solid #DFDFDF; height: 21px"><a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fEndPop(document.daterange.ds,document.daterange.de);return false;"><img class="PopcalTrigger" align="absmiddle" src="/scripts/DateRange/calbtn.gif" width="34" height="22" border="0" alt=""></a>
    </td>
    <td align="right"><input type="submit" value="OK" style="height: 21px"></td>
</tr>
</table>

<br/>

<? if ($_GET['filter']) { ?><input type="hidden" name="filter" value="<?=htmlspecialchars($_GET['filter'])?>"><? } ?>
<table width="50%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 4px">
<tr>
    <td align="left" style="padding-bottom:10px">
    	старый логин:
    </td>
    <td align="left" style="padding-bottom:10px">
    	<input type="text" name="old_login" value="<?=$old_login?>" />
    </td>
</tr>
<tr>
    <td align="left" style="padding-bottom:10px">
    	новый логин:
    </td>
    <td align="left" style="padding-bottom:10px">
		<input type="text" name="new_login" maxlength="15" value="<?=$new_login?>" />
    </td>
</tr>
<tr>
    <td align="left">&nbsp;
    	
    </td>
    <td align="left">
		<input type="checkbox" name="save" value="1" /> Сохранить логин
    </td>
</tr>
<tr>
    <td align="left" rowspan="2">
    	<input type="hidden" value="<?=$_SESSION["rand"] ?>" name="u_token_key">
    	<input type="submit" value="Изменить" style="height: 21px">
    </td>
</tr>
</table>
</form>

<table width="100%" cellpadding="3" cellspacing="1" style="background:gray"  class="brd-tbl">
    <tr>
		<td style="background:#e6fdb7;text-align:center;"><strong>Дата</strong></td>
		<td style="background:#e6fdb7;text-align:center;"><strong>Старый логин</strong></td>
		<td style="background:#e6fdb7;text-align:center;"><strong>Новый логин</strong></td>
		<td style="background:#e6fdb7;text-align:center;"><strong>Сохранение логина</strong></td>
	</tr>
	<? if($data) { ?>
    <? foreach($data as $k=>$val) { ?>
    <tr style="background:white;text-align:center;">
        <td><? if ($val['operation_id']) { ?><a href="/siteadmin/bill/?login=<?=$val['new_login']?>#<?=$val['operation_id']?>" class="blue"><? } ?><?=date("d.m.Y H:i:s", strtotime($val['cdate']))?><? if ($val['save_old']) { ?></a><? } ?></td>
		<td><?=$val['old_login']?></td>
		<td><a href="/users/<?=$val['new_login']?>" class="blue"><?=$val['new_login']?></a> [<a href="./?login=<?=$val['new_login']?>&date=<?=strtotime($val['cdate'])?>">?</a>]</td>
		<td><? if ($val['save_old'] == 't') { ?><input type="checkbox" disabled checked/><? } ?></td>
	</tr>
    <? } ?>
    <? } else { ?>
    <tr style="background:white;">
        <td colspan="4" align="center" style="text-align:center">Нет изменений</td>
    </tr>
    <? } ?>
</table>

<iframe width=132 height=142 name="gToday:contrast" id="gToday:contrast" src="/scripts/DateRange/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
