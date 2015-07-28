<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
$DB = new DB('master');
$sql = "SELECT * FROM stat_login ORDER BY date DESC";
$stats = $DB->rows($sql);
$monthName = array(1=>"Январь", 2=>"Февраль", 3=>"Март", 4=>"Апрель", 5=>"Май", 6=>"Июнь", 7=>"Июль", 8=>"Август", 9=>"Сентябрь", 10=>"Октябрь", 11=>"Ноябрь", 12=>"Декабрь")
?>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left"><strong>Статистика</strong></td>
	<td align="right"><a href="/siteadmin/stats/charts.php">График</a></td>
</tr>
</table>


<br><br>
<?php $mUser = true; require_once ("top_menu.php"); ?>
<br><br>
<?if(count($stats) == 0) { ?>
<strong>Статистика пустая</strong>
<?} else {?>
<table width="100%" cellpadding="3" cellspacing="0" style="border-bottom:1px black solid" class="brd-tbl">
<?php foreach($stats as $stat) { ?>
<?if($year != date('Y', strtotime($stat['date']))) { ?>
<tr>
    <td colspan="2" style="border-bottom:1px black solid; border-top:1px black solid;font-size:13pt;"><?=date('Y', strtotime($stat['date']))?></>
</tr>
<?}//if?>
<tr>
<td width="15%"><strong><?=$monthName[date('m', strtotime($stat['date']))]?></strong></td><td>&nbsp;</td>
</tr>
<tr >
<td width="15%">Фрилансеров:</td><td><strong><?=$stat['frl']?></strong></td></tr>
<tr>
<td width="15%">Работодателей:</td><td><strong><?=$stat['emp']?></strong></td>
</tr>
<?php $year = date('Y', strtotime($stat['date']));}//foreach?>
</table>
<?}?>