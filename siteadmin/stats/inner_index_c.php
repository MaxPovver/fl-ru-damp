<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left"><strong>Статистика</strong></td>
	<td align="right"><a href="/siteadmin/stats/charts.php">График</a></td>
</tr>
</table>


<br><br>
<?php $mCountry = true; require_once ("top_menu.php"); ?>
<br><br>



<table width="100%" cellspacing="2" cellpadding="2" border="0">
<tr>
	<td style="padding: 5px;"><b>Страны</b></td>
	<td style="padding: 5px;"><b>Города</b></td>
    <td style="padding: 5px;"><b>Возраст</b></td>
</tr>
<tr>
	<td valign="top" style="padding: 5px;">
		<table width="100%" cellspacing="2" cellpadding="2" border="0" class="brd-tbl">
		<?
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
		$countr = country::CountAll(10);
		if ($countr)
			foreach($countr as $ikey=>$cntr){
		?>
		<tr>
			<td width="130"><?=$cntr['country_name']?></td>
			<td><?=$cntr['cnt']?></td>
		</tr>
		<? } ?>
		</table>
	</td>
	<td valign="top" style="padding: 5px;">
		<table width="100%" cellspacing="2" cellpadding="2" border="0" class="brd-tbl">
	<?
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
		$citys = city::CountAll(10);
		if ($citys)
			foreach($citys as $ikey=>$city){
		?>
		<tr>
			<td width="130"><?=$city['city_name']?></td>
			<td><?=$city['cnt']?></td>
		</tr>
		<? } ?>
		</table>
	</td>
	<td valign="top" style="padding: 5px;">
		<table width="100%" cellspacing="2" cellpadding="2" border="0" class="brd-tbl">
	<?
        $sql = "select count(*) as cnt, to_char(birthday,'YYYY') as _year from freelancer GROUP BY to_char(birthday,'YYYY') order BY cnt desc limit 10";
        $ages = $DB->rows($sql);
			foreach($ages as $ikey=>$age){
                if($age['_year']=='') {
                    $tage = 'Не указано';
                } else {
                    $tage = date('Y')-$age['_year'];
                }
		?>
		<tr>
			<td width="130"><?=$tage?></td>
			<td><?=$age['cnt']?></td>
		</tr>
		<? } ?>
		</table>
	</td>
</tr>
</table>

<a href="geo.php">Все города, страны и возраст</a>

