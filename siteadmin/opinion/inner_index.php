<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!hasPermissions('adm') || !hasPermissions('users')) { exit; }

?>
<style type="text/css">
.plain {height:20px; vertical-align:middle;}
</style>

<form action="./" name="daterange">

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
    <td align="left" style="font-size: 14px; font-weight: bold">Отзывы за <?=((date('Ymd', $ds) == date('Ymd', $de))? date('d-m-Y', $ds): (date('d-m-Y', $ds).' &ndash; '.date('d-m-Y', $de)))?></td>
    <td align="right">
        Поиск: <input type="text" name="login" value="<?= htmlentities(stripslashes($login))?>" style="width: 200px; height: 21px">
    </td>
</tr>
</table>

<br/>

<? if ($_GET['filter']) { ?><input type="hidden" name="filter" value="<?=htmlspecialchars($_GET['filter'])?>"><? } ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 4px">
<tr>
    <td align="left"><a class="blue" href="./?filter=all<?=($ds? "&ds=".date('d-m-Y', $ds): "")?><?=($de? "&de=".date('d-m-Y', $de): "")?><?=($login? "&login=$login": "")?>">Все</a></td>
    <td width="95" align="left"><a class="blue" href="./?filter=pos<?=($ds? "&ds=".date('d-m-Y', $ds): "")?><?=($de? "&de=".date('d-m-Y', $de): "")?><?=($login? "&login=$login": "")?>">Положительные</a></td>
    <td width="95" align="left"><a class="blue" href="./?filter=neg<?=($ds? "&ds=".date('d-m-Y', $ds): "")?><?=($de? "&de=".date('d-m-Y', $de): "")?><?=($login? "&login=$login": "")?>">Отрицательные</a></td>
    <td align="left"><a class="blue" href="./?filter=zero<?=($ds? "&ds=".date('d-m-Y', $ds): "")?><?=($de? "&de=".date('d-m-Y', $de): "")?><?=($login? "&login=$login": "")?>">Нейтральные</a></td>
    <td align="right">
        с: <input class="plain" name="ds" value="<?=date('d-m-Y', $ds)?>" size="12" style="border: 1px solid #DFDFDF; height: 21px"><a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fStartPop(document.daterange.ds,document.daterange.de);return false;"><img class="PopcalTrigger" align="absmiddle" src="DateRange/calbtn.gif" width="34" height="22" border="0" alt=""></a>
        по: <input class="plain" name="de" value="<?=date('d-m-Y', $de)?>" size="12" style="border: 1px solid #DFDFDF; height: 21px"><a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fEndPop(document.daterange.ds,document.daterange.de);return false;"><img class="PopcalTrigger" align="absmiddle" src="DateRange/calbtn.gif" width="34" height="22" border="0" alt=""></a>
    </td>
    <td align="right"><input type="submit" value="OK" style="height: 21px"></td>
</tr>
</table>

</form>

<table width="100%" cellpadding="3" cellspacing="1" style="background:gray">
    <tr style="background:#e6fdb7;text-align:center;">
		<td style="width:190px;text-align:center;" ><strong>Кто ставил</strong></td>
		<td style="width:190px;text-align:center"><strong>Кому ставил</strong></td>
		<td><strong>Какой отзыв</strong></td>
		<td><strong>Отзыв</strong></td>
	</tr>
	<? if($data) { ?>
    <? foreach($data as $k=>$val) { ?>
    <tr style="background:white;">
        <td  style="width:190px;text-align:center;"><a href="/users/<?=$buser[$val['fromuser_id']]['login']?>/" target="_blank"  <?=(($buser[$val['fromuser_id']]['role'])=='00000'?'style="color:#666666"':(($buser[$val['fromuser_id']]['role']=='100000')?'style="color:#a8e156;font-weight:bold"':'style="color:#666666"'))?>><?=$buser[$val['fromuser_id']]['uname']?> <?=$buser[$val['fromuser_id']]['usurname']?> [<?=$buser[$val['fromuser_id']]['login']?>]</a></td>
		<td  style="width:190px;text-align:center;"><a href="/users/<?=$buser[$val['touser_id']]['login']?>/" target="_blank" <?=(($buser[$val['touser_id']]['role'])=='00000'?'style="color:#666666"':(($buser[$val['touser_id']]['role']=='100000')?'style="color:#a8e156;font-weight:bold"':'style="color:#666666"'))?>><?=$buser[$val['touser_id']]['uname']?> <?=$buser[$val['touser_id']]['usurname']?> [<?=$buser[$val['touser_id']]['login']?>]</td>
		<td  style="text-align:center;"><strong <?=($val['rating']>0?'style="color:#cc9900"':($val['rating']<0?'style="color:#6699cc"':'style="color:#666666"'))?>><?=($val['rating']>0?'+':($val['rating']<0?'-':'0'))?></strong></td>
		<td><span title="Оставлен: <?=date("d.m.Y в H:i", strtotime($val['post_time']));?>"><?=reformat($val['msgtext'], 50)?></span></td>
	</tr>
    <? } ?>
    <? } else { ?>
    <tr style="background:white;">
        <td colspan="4" align="center">Нет отзывов</td>
    </tr>
    <? } ?>
</table>

<iframe width=132 height=142 name="gToday:contrast" id="gToday:contrast" src="DateRange/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
