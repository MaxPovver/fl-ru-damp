<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
$cnt = users::CountAll();
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
$pro = payed::CountPro();
$DB = new DB('master');
?>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left"><strong>Статистика</strong></td>
	<td align="right"><a href="/siteadmin/stats/charts.php">График</a></td>
</tr>
</table>


<br><br>
<?php $mFull = true; require_once ("top_menu.php"); ?>
<br><br>

<table width="100%" border="1" cellspacing="2" cellpadding="2" class="brd-tbl">
<tr>
	<td>Активно PRO (Всего разных юзеров в PRO (за все время))</td>
	<td><?=$pro['cur']?> (<?=$pro['all']?>)</td>
</tr>
<tr>
	<td>Автопродление PRO FL:</td>
	<td><?=$cnt['autopro_fl']?></td>
</tr>
<tr>
	<td>Автопродление PRO EMP:</td>
	<td><?=$cnt['autopro_emp']?></td>
</tr>
<tr>
	<td>Всего народу (сегодня новых)</td>
	<td><?=$cnt['all']?> (<?=$cnt['frl_today']+$cnt['emp_today']?>)</td>
</tr>
<tr>
	<td>- фрилансеров (сегодня новых)</td>
	<td><?=$cnt['frl']?> (<?=$cnt['frl_today']?>)</td>
</tr>
<tr>
	<td>- работодателей (сегодня новых)</td>
	<td><?=$cnt['emp']?> (<?=$cnt['emp_today']?>)</td>
</tr>
<tr>
	<td>Всего живых</td>
	<td><?=$cnt['live_emp_today']+$cnt['live_frl_today']?></td>
</tr>
<tr>
	<td>- фрилансеров живых</td>
	<td><?=$cnt['live_frl_today']?></td>
</tr>
<tr>
	<td>- работодателей живых</td>
	<td><?=$cnt['live_emp_today']?></td>
</tr>
<tr>
	<td>- Проектов сегодня (вчера)</td>
	<td><?=$cnt['prjt']?> (<?=$cnt['prjy']?>)</td>
</tr>
<tr>
	<td>- пользуются сообщениями</td>
	<td><?=$cnt['mess']?></td>
</tr>
<tr>
	<td>- пользуются заметкой</td>
	<td><?=$cnt['notes']?></td>
</tr>
<tr>
	<td>- пользуются командой</td>
	<td><?=$cnt['teams']?></td>
</tr>
<tr>
	<td>- Уведомления о новых сообщениях</td>
	<td><?=$cnt['mcont']?></td>
</tr>
<tr>
	<td>- Уведомления об опубликованных на главной странице Проектах/Предложениях</td>
	<td><?=$cnt['mvac']?></td>
</tr>
<tr>
	<td>- Комментарии к сообщениям/комментариям в блогах</td>
	<td><?=$cnt['mblog']?></td>
</tr>
<tr>
	<td>- Уведомления об ответе на опубликованный Проект/Предложение</td>
	<td><?=$cnt['mprj']?></td>
</tr>
<tr>
	<td>- Закладка портфолио</td>
	<td><?=$cnt['tportf']?></td>
</tr>
<tr>
	<td>- Закладка услуги</td>
	<td><?=$cnt['tserv']?></td>
</tr>
<tr>
	<td>- Закладка инфо</td>
	<td><?=$cnt['tinfo']?></td>
</tr>
<tr>
	<td>- Закладка журнал</td>
	<td><?=$cnt['tjour']?></td>
</tr>
</table>



