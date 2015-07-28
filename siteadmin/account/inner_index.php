<?php 
if (!defined('IS_SITE_ADMIN')) { 
    header('Location: /404.php'); 
    exit; 
} 
if (!(hasPermissions('statsaccounts') || hasPermissions('tmppayments'))) {
    exit; 
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/exchrates.php");

$action = trim($_GET['action']);
if (!$action) $action = trim($_POST['action']);

$DB = new DB('master');
$forms_cnt = intval(trim($_POST['forms_cnt']));
if (!$forms_cnt) $forms_cnt = 1;

switch ($action) {
	case "inc_forms":
		$forms_cnt++;
		break;
}

for ($i = 0; $i < $forms_cnt; $i++) {
    
    //Обрабатываем даты
	$fmnth[$i] = intval(trim($_POST['fmnth'][$i]));
	$fday[$i] = intval(trim($_POST['fday'][$i]));
	$fyear[$i] = intval(trim($_POST['fyear'][$i]));
	$tmnth[$i] = intval(trim($_POST['tmnth'][$i]));
	$tday[$i] = intval(trim($_POST['tday'][$i]));
	$tyear[$i] = intval(trim($_POST['tyear'][$i]));
	if (!checkdate($fmnth[$i], $fday[$i] , $fyear[$i]) || !checkdate($tmnth[$i], $tday[$i] , $tyear[$i])){
		$fday[$i] = $tday[$i] = date("d");
		$fmnth[$i] = $tmnth[$i] = date("m");
		$fyear[$i] = $tyear[$i] = date("Y");
	}
	$fdate = $fyear[$i] . "-". $fmnth[$i] ."-" .$fday[$i];
	$tdate = $tyear[$i] . "-". $tmnth[$i] ."-" .$tday[$i];


    //Получаем данные оераций
    $sstat = $account->GetStatOPEx($fdate, $tdate, true); 
    
    //Всего зачислено
    $inc[$i] = $sstat[12];
	
    //ПРО
	$prop[$i]  = $account->GetPROStat($fdate, $tdate, 0);
	$prop2[$i] = $account->GetPROStat($fdate, $tdate);

    //Платные места
	$fpp[$i] = $account->GetStatOP(array(10,11), $fdate, $tdate);
	$fppc[$i] = $sstat[19];
	$fppci[$i] = $sstat[20];
    $cho[$i] = $sstat[21];
    
    //Платные опции проектов
    $ppp[$i]      = $account->getStatOPProject($fdate, $tdate);
    $ppp_top[$i]  = $account->getStatOPProject($fdate, $tdate, 3); 
    $ppp_logo[$i] = $account->getStatOPProject($fdate, $tdate, 0);
    $ppp_office[$i] = $account->getStatOPProject($fdate, $tdate, 4);

    //Конкурсы и их платные опции
    $konk[$i]      = $account->getStatOPProject($fdate, $tdate, '', true);
	$konk_top[$i]  = $account->getStatOPProject($fdate, $tdate, 3,  true); 
    $konk_logo[$i] = $account->getStatOPProject($fdate, $tdate, 0,  true);
    
    // Карусель на главной
    $ppfm[$i] =  $sstat[65]; 
	
    //Массовая рассылка    
    $mass_sending_n[$i] = $account->GetStatOP(array(45), $fdate, $tdate, "NOT ((role&'000010')='000010' OR (role&'000100')='000100') AND mass_sending.is_accepted IS NULL", "LEFT JOIN mass_sending ON account_operations.id=mass_sending.account_op_id LEFT JOIN users ON mass_sending.user_id=users.uid");
    $mass_sending_a[$i] = $account->GetStatOP(array(45), $fdate, $tdate, "NOT ((role&'000010')='000010' OR (role&'000100')='000100') AND mass_sending.is_accepted='t'", "LEFT JOIN mass_sending ON account_operations.id=mass_sending.account_op_id LEFT JOIN users ON mass_sending.user_id=users.uid");
    $mass_sending_r[$i] = $account->GetStatOP(array(46), $fdate, $tdate, "NOT ((role&'000010')='000010' OR (role&'000100')='000100')", "LEFT JOIN account ON account_operations.billing_id=account.id LEFT JOIN users ON account.uid=users.uid");

    //Переводы
    $ours_to_alien[$i] = $account->getStatTransferOursAlien( $fdate, $tdate, true );
    $alien_to_ours[$i] = $account->getStatTransferOursAlien( $fdate, $tdate, false );
    
    
    //Общая статистика сумм
    $aOverall   = $account->getStatOverall( $fdate, $tdate, true );
    $ost_b[$i]  = $aOverall['begin'];
    $ost_e[$i]  = $aOverall['end'];
    $spend[$i]  = $aOverall['spent'];
    $income[$i] = $aOverall['income'];

    //Статистика пополнений счета
	$pstat = $account->GetStatPSEx($fdate, $tdate, array(12,36,38,43));

    $yd[$i] = zin($pstat[12][3]);
    $wmrw[$i] = zin($pstat[12][10]);
    $bn[$i] = zin($pstat[12][4]);
    $sb[$i] = zin($pstat[12][17]);
    $cc[$i] = zin($pstat[12][6]);
    $osmp_op[$i] = zin($pstat[12][8]);
	$alpha_op[$i] = zin($pstat[12][16]);
    
}


$aMonthes[1] = 'январь';
$aMonthes[2] = 'февраль';
$aMonthes[3] = 'март';
$aMonthes[4] = 'апрель';
$aMonthes[5] = 'май';
$aMonthes[6] = 'июнь';
$aMonthes[7] = 'июль';
$aMonthes[8] = 'август';
$aMonthes[9] = 'сентябрь';
$aMonthes[10] = 'октябрь';
$aMonthes[11] = 'ноябрь';
$aMonthes[12] = 'декабрь';

$statYears = account::getStatYears(2006);
$aData = $statYears['data'];
$ids = $statYears['ids'];

?>
<style>
.bt, tr.bt td   {border-top:1px solid #d0d0d0}
.br, tr.br td   {border-right:1px solid #d0d0d0}
.bb, tr.bb td   {border-bottom:1px solid #d0d0d0}
.bl, tr.bl td   {border-left:1px solid #d0d0d0}
.bl2, tr.bl2 td   {border-left:2px solid #d0d0d0}
.ba, tr.ba td   {border:1px solid #d0d0d0}
.ac, tr.ac td   {text-align:center}
.tbl-acc td{ padding:5px;}
</style>
<form action="." method="post" name="frm" id="frm">
<input type="hidden" name="action" value="">
<input type="hidden" name="forms_cnt" value="<?=$forms_cnt?>">
<strong>Деньги</strong><br><br>
	<? if ($error) print(view_error($error));?>

<? for ($i = 0; $i < $forms_cnt; $i++) {
	$fdate = $fyear[$i] . "-". $fmnth[$i] ."-" .$fday[$i];
	$tdate = $tyear[$i] . "-". $tmnth[$i] ."-" .$tday[$i];
?>
с&nbsp;&nbsp;
<input type="text" name="fday[]" size="2" maxlength="2" value="<?=$fday[$i]?>">
<select name="fmnth[]">
	<option value="1" <? if ($fmnth[$i] == 1) print "SELECTED"?>>января</option>
	<option value="2" <? if ($fmnth[$i] == 2) print "SELECTED"?>>февраля</option>
	<option value="3" <? if ($fmnth[$i] == 3) print "SELECTED"?>>марта</option>
	<option value="4" <? if ($fmnth[$i] == 4) print "SELECTED"?>>апреля</option>
	<option value="5" <? if ($fmnth[$i] == 5) print "SELECTED"?>>мая</option>
	<option value="6" <? if ($fmnth[$i] == 6) print "SELECTED"?>>июня</option>
	<option value="7" <? if ($fmnth[$i] == 7) print "SELECTED"?>>июля</option>
	<option value="8" <? if ($fmnth[$i] == 8) print "SELECTED"?>>августа</option>
	<option value="9" <? if ($fmnth[$i] == 9) print "SELECTED"?>>сентября</option>
	<option value="10" <? if ($fmnth[$i] == 10) print "SELECTED"?>>октября</option>
	<option value="11" <? if ($fmnth[$i] == 11) print "SELECTED"?>>ноября</option>
	<option value="12" <? if ($fmnth[$i] == 12) print "SELECTED"?>>декабря</option>
</select>
<input type="text" name="fyear[]" size="4" maxlength="4" value="<?=$fyear[$i]?>">&nbsp;&nbsp;
по&nbsp;&nbsp;
<input type="text" name="tday[]" size="2" maxlength="2" value="<?=$tday[$i]?>">
<select name="tmnth[]">
	<option value="1" <? if ($tmnth[$i] == 1) print "SELECTED"?>>января</option>
	<option value="2" <? if ($tmnth[$i] == 2) print "SELECTED"?>>февраля</option>
	<option value="3" <? if ($tmnth[$i] == 3) print "SELECTED"?>>марта</option>
	<option value="4" <? if ($tmnth[$i] == 4) print "SELECTED"?>>апреля</option>
	<option value="5" <? if ($tmnth[$i] == 5) print "SELECTED"?>>мая</option>
	<option value="6" <? if ($tmnth[$i] == 6) print "SELECTED"?>>июня</option>
	<option value="7" <? if ($tmnth[$i] == 7) print "SELECTED"?>>июля</option>
	<option value="8" <? if ($tmnth[$i] == 8) print "SELECTED"?>>августа</option>
	<option value="9" <? if ($tmnth[$i] == 9) print "SELECTED"?>>сентября</option>
	<option value="10" <? if ($tmnth[$i] == 10) print "SELECTED"?>>октября</option>
	<option value="11" <? if ($tmnth[$i] == 11) print "SELECTED"?>>ноября</option>
	<option value="12" <? if ($tmnth[$i] == 12) print "SELECTED"?>>декабря</option>
</select>
<input type="text" name="tyear[]" size="4" maxlength="4" value="<?=$tyear[$i]?>">
<input type="submit" value="Ага!"><br><br>


<strong>За указанный период:</strong>
<table width="100%" border="0" cellspacing="0" cellpadding="4" style="margin-top: 10px;" class="tbl-acc">
<tr align="center" class="box4">
	<td width="13%" class="box3" style="border-top: 1px solid #C6C6C6;">&nbsp;</td>
	<td width="12%" style="border-top: 1px solid #C6C6C6;"><strong>ЯД</strong></td>
	<td style="border-top: 1px solid #C6C6C6;"><strong>WMR-Б</strong></td>
	<td style="border-top: 1px solid #C6C6C6;"><strong>Б/Н</strong></td>
	<td style="border-top: 1px solid #C6C6C6;"><strong>СБ</strong></td>
	<td style="border-top: 1px solid #C6C6C6;"><strong>Карта</strong></td>
	<td style="border-top: 1px solid #C6C6C6;"><strong>ОСМП</strong></td>
	<td style="border-top: 1px solid #C6C6C6;"><strong>Альфа</strong></td>
</tr>
<tr align="center" class="box4">
	<td class="box3"><strong>Счет</strong></td>
	<td><?=$yd[$i]['sum']?> р<? if ($inc[$i]['sum']) { ?> |  <?=round((zin($yd[$i]['sum'])/$inc[$i]['sum'])*100)?>%<? } ?> 
	(<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=22" class="blue"><?=$yd[$i]['count']?></a>)
	</td>
    <td><?=$wmrw[$i]['sum']?> р<? if ($inc[$i]['sum']) { ?> |  <?=round((zin($wmrw[$i]['sum'])/$inc[$i]['sum'])*100)?>%<? } ?> 
    (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=29" class="blue"><?=$wmrw[$i]['count']?></a>)
    </td>
	<td><?=$bn[$i]['sum']?> р<? if ($inc[$i]['sum']) { ?> |  <?=round((zin($bn[$i]['sum'])/$inc[$i]['sum'])*100)?>%<? } ?> 
    (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=25" class="blue"><?=$bn[$i]['count']?></a>)
    </td>
	<td><?=$sb[$i]['sum']?> Cр<? if ($inc[$i]['sum']) { ?> |  <?=round((zin($sb[$i]['sum'])/$inc[$i]['sum'])*100)?>%<? } ?> 
    (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=26" class="blue"><?=$sb[$i]['count']?></a>)
    </td>
	<td><?=$cc[$i]['sum']?> р<? if ($inc[$i]['sum']) { ?> |  <?=round((zin($cc[$i]['sum'])/$inc[$i]['sum'])*100)?>%<? } ?> 
    (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=27" class="blue"><?=$cc[$i]['count']?></a>)
    </td>
	<td><?=$osmp_op[$i]['sum']?> р<? if ($inc[$i]['sum']) { ?> |  <?=round((zin($osmp_op[$i]['sum'])/$inc[$i]['sum'])*100)?>%<? } ?>
	(<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=17" class="blue"><?=$osmp_op[$i]['cnt']?></a>)
	</td>
	<td><?=$alpha_op[$i]['sum']?> р<? if ($inc[$i]['sum']) { ?> |  <?=round((zin($alpha_op[$i]['sum'])/$inc[$i]['sum'])*100)?>%<? } ?>
	(<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=89" class="blue"><?=$alpha_op[$i]['count']?></a>)
	</td>
</tr>

<tr>
    <td colspan="14" class="box3"><strong>&sum;</strong> <?=$yd[$i]['sum']+$wmr[$i]['sum']+$wmrw[$i]['sum']+$bn[$i]['sum']+$sb[$i]['sum']+$cc[$i]['sum']+$osmp_op[$i]['sum']+$alpha_op[$i]['sum']?> р = <strong><?= $inc[$i]['sum'] ?> руб.</strong>
	 (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>" class="blue">операций: <?=zin($inc[$i]['cnt'])?></a>)</td>
</tr>
<tr class="box4">
	<td colspan="2" colspan="2" class="box3"><strong>Остаток на начало</strong></td>
	<td colspan="12" title="Изменение счетов всех пользователей c 12 декабря 2000 года до начала периода выборки"><?= $ost_b[$i];?> руб.</td>
</tr>
<tr class="box4">
	<td colspan="2" colspan="2" class="box3"><strong>Остаток на конец</strong></td>
	<td colspan="12" title="Изменение счетов всех пользователей c 12 декабря 2000 года до окончания периода выборки"><?=$ost_e[$i]?> руб.</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3"><strong>Всего зачислено</strong></td>
	<td colspan="12"><?=$inc[$i]['sum']?> руб.</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3"><strong>Всего потрачено</strong></td>
	<td colspan="12"><?=$spend[$i]?> руб.</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3"><strong>Переводы от "своих" к "чужим"</strong></td>
	<td colspan="12"><?=$ours_to_alien[$i]?> руб.</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3"><strong>Переводы от "чужих" к "своим"</strong></td>
	<td colspan="12"><?=$alien_to_ours[$i]?> руб.</td>
</tr>
</table>


<? if ($spend[$i]) { ?>
<br/>

<table width="100%" border="0" cellspacing="0" cellpadding="4" style="margin-top: 10px;" class="tbl-acc">
<tr class="box4">
	<td colspan="2" class="box3"><strong>PRO фрилансеры</strong></td>
	<td colspan="12"><?=zin(abs($prop[$i]['sum']))?> руб. | <?=round((zin(abs($prop[$i]['sum']))/$spend[$i])*100)?>% (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=1" class="blue">операций: <?=zin($prop[$i]['cnt'])?></a>)</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3"><strong>PRO работодатели</strong></td>
	<td colspan="12"><?=zin(abs($prop2[$i]['sum']))?> руб. | <?=round((zin(abs($prop2[$i]['sum']))/$spend[$i])*100)?>% (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=11" class="blue">операций: <?=zin($prop2[$i]['cnt'])?></a>)</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3"><strong>Платные проекты</strong></td>
	<td colspan="12"><?=abs(zin($ppp[$i]['sum']))?> руб. | <?=abs(round((zin($ppp[$i]['sum'])/$spend[$i])*100))?>% (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=101" class="blue">операций: <?=zin($ppp[$i]['cnt'])?></a>)</td>
</tr>

<?php
$t_pp_sum[$i] = zin($ppp_top[$i]['sum'])+zin($ppp_logo[$i]['sum'])+zin($ppp_office[$i]['sum']);
if($t_pp_sum[$i]==0) $t_pp_sum[$i]=1;
?>
<tr class="box4">
	<td colspan="2" class="box3">- Проекты В ОФИС</td>
	<td colspan="12"><?=zin($ppp_office[$i]['sum'])?> руб. | <?=round((zin($ppp_office[$i]['sum'])/$t_pp_sum[$i])*100)?>% (операций: <?=zin($ppp_office[$i]['cnt'])?>)</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3">- Закрепить наверху ленты</td>
	<td colspan="12"><?=zin($ppp_top[$i]['sum'])?> руб. | <?=round((zin($ppp_top[$i]['sum'])/$t_pp_sum[$i])*100)?>% (операций: <?=zin($ppp_top[$i]['cnt'])?>)</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3">- Логотип со ссылкой</td>
	<td colspan="12"><?=zin($ppp_logo[$i]['sum'])?> руб. | <?=round((zin($ppp_logo[$i]['sum'])/$t_pp_sum[$i])*100)?>% (операций: <?=zin($ppp_logo[$i]['cnt'])?>)</td>
</tr>


<tr class="box4">
	<td colspan="2" class="box3"><strong>Место на первой</strong></td>
	<td colspan="12"><?=abs(zin($fpp[$i]['sum']))?> руб. | <?=round((zin(abs($fpp[$i]['sum']))/$spend[$i])*100)?>% (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=2" class="blue">операций: <?=zin($fpp[$i]['cnt'])?></a>)</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3"><strong>Место в общем каталоге</strong></td>
	<td colspan="12"><?=zin(abs($fppc[$i]['sum']))?> руб. | <?=round((zin(abs($fppc[$i]['sum']))/$spend[$i])*100)?>% (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=5" class="blue">операций: <?=zin($fppc[$i]['cnt'])?></a>)</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3"><strong>Место внутри каталога</strong></td>
	<td colspan="12"><?=zin(abs($fppci[$i]['sum']))?> руб. | <?=round((zin(abs($fppci[$i]['sum']))/$spend[$i])*100)?>% (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=6" class="blue">операций: <?=zin($fppci[$i]['cnt'])?></a>)</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3"><strong>Перемещения</strong></td>
	<td colspan="12"><?=zin(abs($cho[$i]['sum']))?> руб. | <?=round((zin(abs($cho[$i]['sum']))/$spend[$i])*100)?>% (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=7" class="blue">операций: <?=zin($cho[$i]['cnt'])?></a>)</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3"><strong>Конкурсы</strong></td>
	<td colspan="12"><?=zin(abs($konk[$i]['sum']))?> руб. | <?=round((zin(abs($konk[$i]['sum']))/$spend[$i])*100)?>% (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=103" class="blue">операций: <?=zin($konk[$i]['cnt'])?></a>)</td>
</tr>

<?php
$t_kn_sum[$i] = zin($konk_top[$i]['sum'])+zin($konk_logo[$i]['sum']);
if($t_kn_sum[$i]==0) $t_kn_sum[$i]=1;
?>
<tr class="box4">
	<td colspan="2" class="box3">- Закрепить наверху ленты</td>
	<td colspan="12"><?=zin($konk_top[$i]['sum'])?> руб. | <?=round((zin($konk_top[$i]['sum'])/$t_kn_sum[$i])*100)?>% (операций: <?=zin($konk_top[$i]['cnt'])?>)</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3">- Логотип со ссылкой</td>
	<td colspan="12"><?=zin($konk_logo[$i]['sum'])?> руб. | <?=round((zin($konk_logo[$i]['sum'])/$t_kn_sum[$i])*100)?>% (операций: <?=zin($konk_logo[$i]['cnt'])?>)</td>
</tr>
<tr class="box4">
    <td colspan="2" class="box3"><strong>Карусель на главноей (руб.) </strong></td>
    <td colspan="12"><?=zin(abs($ppfm[$i]['sum']))?> руб. | <?=round((zin(abs($ppfm[$i]['sum']))/$spend[$i])*100)?>% (<a href="/siteadmin/users/?action=selacop&fdate=<?=$fdate?>&tdate=<?=$tdate?>&akop=20" class="blue">операций: <?=zin(abs($ppfm[$i]['cnt']))?></a>)</td>
</tr>
<tr class="box4">
	<td colspan="2" class="box3"><strong>Платная рассылка</strong></td>
	<td colspan="12">
        Новые: <?=zin(abs($mass_sending_n[$i]['sum']))?> руб. (операций: <?=zin(abs($mass_sending_n[$i]['cnt']))?>)
        <br>
        Принятые: <?=zin(abs($mass_sending_a[$i]['sum']))?> руб. | <?=round((zin(abs($mass_sending_a[$i]['sum']))/$spend[$i])*100)?>% (<a href="/siteadmin/users/?action=selacop&fdate=<?= $fdate ?>&tdate=<?= $tdate ?>&akop=45" class="blue">операций: <?=zin($mass_sending_a[$i]['cnt'])?></a>)
        <br>
        Отказанные: <?=zin(abs($mass_sending_r[$i]['sum']))?> руб. (операций: <?=zin(abs($mass_sending_r[$i]['cnt']))?>)
    </td>
</tr>


</table>
<? } ?>

<br><br>

<div style="margin-bottom: 50px"></div>
<? } ?>
<div style="margin-top: 10px"><a href="#" class="blue" onClick="frm.action.value='inc_forms'; frm.submit();">+ Еще разок</a></div>

</form>
<script type="text/javascript">
    function showStats(y, m, ids) {
        
        if (!y && !m) {
            y = 'all';
            m = '0';
        }
        
        var temp = new Array();
        temp = ids.split(',');
        for (i=0; i<temp.length; i++) {
            try {
                document.getElementById('top_'+temp[i]).className = 'dotted_';
                document.getElementById('bottom_'+temp[i]).className = 'dotted_';
            } catch(err) {}
        }
        $$('#top_all_0, #bottom_all_0').removeClass('black_').addClass('dotted_');
        
        if (m) {
            document.getElementById('stats1').src = 'top_stats.php?y='+y+'&m='+m;
            document.getElementById('stats2').src = 'bottom_stats.php?y='+y+'&m='+m;
        }
        else {
            document.getElementById('stats1').src = 'top_stats.php?y='+y+'&rnd='+Math.random(1000);
            document.getElementById('stats2').src = 'bottom_stats.php?y='+y+'&rnd='+Math.random(1000);
        }
        
        document.getElementById('top_'+y+'_'+m).className = 'black_';
        document.getElementById('bottom_'+y+'_'+m).className = 'black_';
    }
</script>
<style>
.dotted_ {
	color: #26589d;

	font: bold 11px Tahoma;
	text-decoration: none;
	border-bottom: 1px dotted #26589d;
}

.black_ {
	color: White;
	font: bold 11px Tahoma;
	text-decoration: none;
	background: #000;
	padding: 1px;
}

.grey {
	font: bold 11px Tahoma;
	color: #a8afb4;
}
</style>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<?php
if (count($aData)) {
	foreach ($aData as $k => $v) {
		echo '<tr>
				<td style="padding-left: 100px">';
		echo '<a class="dotted_" href="javascript: showStats('.$k.',0, \''.implode(',',$ids).'\'); void(0);" id="top_'.$k.'_0"><strong>'.$k.'</strong>:</a>';
		for ($i=1; $i<=12; $i++) {
			if ($v[$i]['data'])
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$v[$i]['date_m'].'\', \''.implode(',',$ids).'\'); void(0);" id="top_'.$k.'_'.$v[$i]['date_m'].'">'.$aMonthes[$i].'</a>';
			else
			echo '&nbsp;&nbsp;<span class="grey">'.$aMonthes[$i].'</span>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
 } ?>
<tr>
    <td style="padding-left: 100px"><a class="dotted_" id="top_all_0" href="javascript: void(0)" onclick="showStats(null,null,'<?= implode(',',$ids) ?>')">Все года</a></td>
    <td colspan="12"></td>
</tr>
<tr>
	<td><img src="top_stats.php" id='stats1'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td><img src="bottom_stats.php" id='stats2'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
<?php
if (count($aData)) {
	foreach ($aData as $k => $v) {
		echo '<tr>
				<td style="padding-left: 100px">';
		echo '<a class="dotted_" href="javascript: showStats('.$k.',0, \''.implode(',',$ids).'\'); void(0);" id="bottom_'.$k.'_0"><strong>'.$k.'</strong>:</a>';
		for ($i=1; $i<=12; $i++) {
			if ($v[$i]['data'])
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$v[$i]['date_m'].'\', \''.implode(',',$ids).'\'); void(0);" id="bottom_'.$k.'_'.$v[$i]['date_m'].'">'.$aMonthes[$i].'</a>';
			else
			echo '&nbsp;&nbsp;<span class="grey">'.$aMonthes[$i].'</span>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
}
?>
<tr>
    <td style="padding-left: 100px"><a class="dotted_" id="bottom_all_0" href="javascript: void(0)" onclick="showStats(null,null,'<?= implode(',',$ids) ?>')">Все года</a></td>
    <td colspan="12"></td>
</tr>
</table>

<script type="text/javascript">
document.getElementById('top_'+'<?php echo date('Y_m'); ?>').className = 'black_';
document.getElementById('bottom_'+'<?php echo date('Y_m'); ?>').className = 'black_';
</script>

