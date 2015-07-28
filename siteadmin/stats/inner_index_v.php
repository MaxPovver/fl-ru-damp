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
<?php $mVerify = true; require_once ("top_menu.php"); ?>
<br><br>

<?
$action = trim($_GET['action']);
if (!$action) $action = trim($_POST['action']);

$forms_cnt = intval(trim($_POST['forms_cnt']));
if (!$forms_cnt) $forms_cnt = 1;

switch ($action){
	case "inc_forms":
		$forms_cnt++;
		break;
}

for ($i = 0; $i < $forms_cnt; $i++){
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

    // -----
}

?>
<form action="?t=<?=htmlspecialchars($_GET['t'])?>" method="post" name="frm" id="frm">
<input type="hidden" name="action" value="">
<input type="hidden" name="forms_cnt" value="<?=$forms_cnt?>">
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



<? } ?>

<?php

// Неуспешно
$verify_wm[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'wm', false, false);
$verify_ffpro[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'ffpro', false, false);
$verify_ffnopro[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'ffnopro', false, false);
$verify_yd[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'yd', false, false);
$verify_pskb[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'pskb', false, false);
$verify_okpay[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'okpay', false, false);
$verify_wm[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'wm', false, true);
$verify_ffpro[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'ffpro', false, true);
$verify_ffnopro[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'ffnopro', false, true);
$verify_yd[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'yd', false, true);
$verify_pskb[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'pskb', false, true);
$verify_okpay[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'okpay', false, true);

// Успешно
$verify_wm_compl[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'wm', true, false);
$verify_ffpro_compl[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'ffpro', true, false);
$verify_ffnopro_compl[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'ffnopro', true, false);
$verify_yd_compl[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'yd', true, false);
$verify_pskb_compl[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'pskb', true, false);
$verify_okpay_compl[$i]['emp'] = Verification::getStatVerify($fdate,$tdate, 'okpay', true, false);
$verify_wm_compl[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'wm', true, true);
$verify_ffpro_compl[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'ffpro', true, true);
$verify_ffnopro_compl[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'ffnopro', true, true);
$verify_yd_compl[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'yd', true, true);
$verify_pskb_compl[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'pskb', true, true);
$verify_okpay_compl[$i]['frl'] = Verification::getStatVerify($fdate,$tdate, 'okpay', true, true);

// Успешно - по странам
$verify_wm_compl[$i]['country']   = Verification::getStatVerify($fdate,$tdate, 'wm_country', true);
$verify_ff_compl[$i]['country']   = Verification::getStatVerify($fdate,$tdate, 'ff_country', true);
$verify_yd_compl[$i]['country']   = Verification::getStatVerify($fdate,$tdate, 'yd_country', true);
$verify_pskb_compl[$i]['country'] = Verification::getStatVerify($fdate,$tdate, 'pskb_country', true);
$verify_okpay_compl[$i]['country'] = Verification::getStatVerify($fdate,$tdate, 'okpay_country', true);

// Не успешно - по странам
$verify_wm[$i]['country']   = Verification::getStatVerify($fdate,$tdate, 'wm_country', false);
$verify_ff[$i]['country']   = Verification::getStatVerify($fdate,$tdate, 'ff_country', false);
$verify_yd[$i]['country']   = Verification::getStatVerify($fdate,$tdate, 'yd_country', false);
$verify_pskb[$i]['country'] = Verification::getStatVerify($fdate,$tdate, 'pskb_country', false);
$verify_okpay[$i]['country'] = Verification::getStatVerify($fdate,$tdate, 'okpay_country', false);

// Общая по странам
$verify_country[$i] = Verification::getStatVerify($fdate,$tdate, 'country');

?>
</form>


<?if(count($stats) == 0) { ?>
<strong>Статистика пустая</strong>
<?} else {?>

<table  border="1" cellspacing="2" cellpadding="2">
    <colgroup>
        <col width="530"/>
        <col />
    </colgroup>
    <tr>
        <td colspan=2 style="padding:10px"><strong>Верифицировано через Вебмани:</strong></td>
    </tr>
<tr>
	<td colspan="2">
        <table style="width:100%" class="brd-tbl">
            <colgroup>
                <col width="90"/>
                <col width="80"/>
                <col width="140"/>
                <col width="80"/>
                <col width="140"/>
            </colgroup>
            <tr>
                <td></td>
                <td>Успешно</td>
                <td>Успешно - страны</td>
                <td>Не успешно</td>
                <td>Не успешно - страны</td>
            </tr>
            <tr>
                <td>Фрилансеров</td>
                <td><?= $verify_wm_compl[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_wm_compl[$i]['country'] as $stat) { ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_wm_compl[$i]['frl']['cnt'] + $verify_wm_compl[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
                <td><?= $verify_wm[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_wm[$i]['country'] as $stat) { ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_wm[$i]['frl']['cnt'] + $verify_wm[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
            </tr>
            <tr>
                <td>Работодателей</td>
                <td><?= $verify_wm_compl[$i]['emp']['cnt']?></td>
                <td><?= $verify_wm[$i]['emp']['cnt']?></td>
            </tr>
            <tr>
                <td>Всего</td>
                <td><?= ($verify_wm_compl[$i]['frl']['cnt'] + $verify_wm_compl[$i]['emp']['cnt'])?></td>
                <td><?= ($verify_wm[$i]['frl']['cnt'] + $verify_wm[$i]['emp']['cnt'])?></td>
            </tr>
        </table>
    </td>
	<!-- <td><span title="Количество пользователей прошедших верификацию"><?= $verify_wm_compl[$i]['cnt']?></span> (<span title="Количество пользователей не закончивших проходить верификацию"><?=$verify_wm[$i]['cnt']?></span>)</td> -->
</tr>
<tr>
    <td colspan=2 style="padding:10px"><strong>Верифицировано ПРО через FF:</strong></td>
</tr>
<tr>
	<td colspan="2">
        <table style="width:100%" class="brd-tbl">
            <colgroup>
                <col width="90"/>
                <col width="80"/>
                <col width="140"/>
                <col width="80"/>
                <col width="140"/>
            </colgroup>
            <tr>
                <td></td>
                <td>Успешно</td>
                <td>Успешно - страны</td>
                <td>Не успешно</td>
                <td>Не успешно - страны</td>
            </tr>
            <tr>
                <td>Фрилансеров</td>
                <td><?= $verify_ffpro_compl[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_ff_compl[$i]['country'] as $stat) { 
                        if ($stat['is_pro'] == 'f') continue; ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_ffpro_compl[$i]['frl']['cnt'] + $verify_ffpro_compl[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
                <td><?= $verify_ffpro[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_ff[$i]['country'] as $stat) { 
                        if ($stat['is_pro'] == 'f') continue; ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_ffpro[$i]['frl']['cnt'] + $verify_ffpro[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
            </tr>
            <tr>
                <td>Работодателей</td>
                <td><?= $verify_ffpro_compl[$i]['emp']['cnt']?></td>
                <td><?= $verify_ffpro[$i]['emp']['cnt']?></td>
            </tr>
            <tr>
                <td>Всего</td>
                <td><?= ($verify_ffpro_compl[$i]['frl']['cnt'] + $verify_ffpro_compl[$i]['emp']['cnt'])?></td>
                <td><?= ($verify_ffpro[$i]['frl']['cnt'] + $verify_ffpro[$i]['emp']['cnt'])?></td>
            </tr>
        </table>
    </td>
	<!-- <td><span title="Количество пользователей прошедших верификацию"><?=$verify_ffpro_compl[$i]['cnt']?></span> (<span title="Количество пользователей не закончивших проходить верификацию"><?=$verify_ffpro[$i]['cnt']?></span>)</td> -->
</tr>
<tr>
    <td colspan=2 style="padding:10px"><strong>Верифицировано неПРО через FF:</strong></td>
</tr>
<tr>
	<td colspan="2">
        <table style="width:100%" class="brd-tbl">
            <colgroup>
                <col width="90"/>
                <col width="80"/>
                <col width="140"/>
                <col width="80"/>
                <col width="140"/>
            </colgroup>
            <tr>
                <td></td>
                <td>Успешно</td>
                <td>Успешно - страны</td>
                <td>Не успешно</td>
                <td>Не успешно - страны</td>
            </tr>
            <tr>
                <td>Фрилансеров</td>
                <td><?= $verify_ffnopro_compl[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_ff_compl[$i]['country'] as $stat) { 
                        if($stat['is_pro'] == 't') continue; ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_ffnopro_compl[$i]['frl']['cnt'] + $verify_ffnopro_compl[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
                <td><?= $verify_ffnopro[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_ff[$i]['country'] as $stat) { 
                        if($stat['is_pro'] == 't') continue; ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_ffnopro[$i]['frl']['cnt'] + $verify_ffnopro[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
            </tr>
            <tr>
                <td>Работодателей</td>
                <td><?= $verify_ffnopro_compl[$i]['emp']['cnt']?></td>
                <td><?= $verify_ffnopro[$i]['emp']['cnt']?></td>
            </tr>
            <tr>
                <td>Всего</td>
                <td><?= ($verify_ffnopro_compl[$i]['frl']['cnt'] + $verify_ffnopro_compl[$i]['emp']['cnt'])?></td>
                <td><?= ($verify_ffnopro[$i]['frl']['cnt'] + $verify_ffnopro[$i]['emp']['cnt'])?></td>
            </tr>
        </table>
    </td>
	<!-- <td><span title="Количество пользователей прошедших верификацию"><?=$verify_ffnopro_compl[$i]['cnt']?></span> (<span title="Количество пользователей не закончивших проходить верификацию"><?=$verify_ffnopro[$i]['cnt']?></span>)</td> -->
</tr><tr>
    <td colspan=2 style="padding:10px"><strong>Верифицировано через Яндекс.Деньги:</strong></td>
</tr>
<tr>
	<td colspan="2">
        <table style="width:100%" class="brd-tbl">
            <colgroup>
                <col width="90"/>
                <col width="80"/>
                <col width="140"/>
                <col width="80"/>
                <col width="140"/>
            </colgroup>
            <tr>
                <td></td>
                <td>Успешно</td>
                <td>Успешно - страны</td>
                <td>Не успешно</td>
                <td>Не успешно - страны</td>
            </tr>
            <tr>
                <td>Фрилансеров</td>
                <td><?= $verify_yd_compl[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_yd_compl[$i]['country'] as $stat) { ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_yd_compl[$i]['frl']['cnt'] + $verify_yd_compl[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
                <td><?= $verify_yd[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_yd[$i]['country'] as $stat) { ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_yd[$i]['frl']['cnt'] + $verify_yd[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
            </tr>
            <tr>
                <td>Работодателей</td>
                <td><?= $verify_yd_compl[$i]['emp']['cnt']?></td>
                <td><?= $verify_yd[$i]['emp']['cnt']?></td>
            </tr>
            <tr>
                <td>Всего</td>
                <td><?= ($verify_yd_compl[$i]['frl']['cnt'] + $verify_yd_compl[$i]['emp']['cnt'])?></td>
                <td><?= ($verify_yd[$i]['frl']['cnt'] + $verify_yd[$i]['emp']['cnt'])?></td>
            </tr>
        </table>
    </td>
</tr>
</tr><tr>
    <td colspan=2 style="padding:10px"><strong>Верифицировано через Вебкошелёк:</strong></td>
</tr>
<tr>
	<td colspan="2">
        <table style="width:100%" class="brd-tbl">
            <colgroup>
                <col width="90"/>
                <col width="80"/>
                <col width="140"/>
                <col width="80"/>
                <col width="140"/>
            </colgroup>
            <tr>
                <td></td>
                <td>Успешно</td>
                <td>Успешно - страны</td>
                <td>Не успешно</td>
                <td>Не успешно - страны</td>
            </tr>
            <tr>
                <td>Фрилансеров</td>
                <td><?= $verify_pskb_compl[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_pskb_compl[$i]['country'] as $stat) { ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_pskb_compl[$i]['frl']['cnt'] + $verify_pskb_compl[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
                <td><?= $verify_pskb[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_pskb[$i]['country'] as $stat) { ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_pskb[$i]['frl']['cnt'] + $verify_pskb[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
            </tr>
            <tr>
                <td>Работодателей</td>
                <td><?= $verify_pskb_compl[$i]['emp']['cnt']?></td>
                <td><?= $verify_pskb[$i]['emp']['cnt']?></td>
            </tr>
            <tr>
                <td>Всего</td>
                <td><?= ($verify_pskb_compl[$i]['frl']['cnt'] + $verify_pskb_compl[$i]['emp']['cnt'])?></td>
                <td><?= ($verify_pskb[$i]['frl']['cnt'] + $verify_pskb[$i]['emp']['cnt'])?></td>
            </tr>
        </table>
    </td>
</tr><tr>
    <td colspan=2 style="padding:10px"><strong>Верифицировано через OKPAY:</strong></td>
</tr>
<tr>
    <td colspan="2">
        <table style="width:100%" class="brd-tbl">
            <colgroup>
                <col width="90"/>
                <col width="80"/>
                <col width="140"/>
                <col width="80"/>
                <col width="140"/>
            </colgroup>
            <tr>
                <td></td>
                <td>Успешно</td>
                <td>Успешно - страны</td>
                <td>Не успешно</td>
                <td>Не успешно - страны</td>
            </tr>
            <tr>
                <td>Фрилансеров</td>
                <td><?= $verify_okpay_compl[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_okpay_compl[$i]['country'] as $stat) { ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_okpay_compl[$i]['frl']['cnt'] + $verify_okpay_compl[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
                <td><?= $verify_okpay[$i]['frl']['cnt']?></td>
                <td rowspan = "3"><?
                    foreach($verify_okpay[$i]['country'] as $stat) { ?>
                        <?= $stat['country_name']?> &ndash; <?= round($stat['cnt']*100/($verify_okpay[$i]['frl']['cnt'] + $verify_okpay[$i]['emp']['cnt'])).'%'?><br/>
                    <?}?>
                </td>
            </tr>
            <tr>
                <td>Работодателей</td>
                <td><?= $verify_okpay_compl[$i]['emp']['cnt']?></td>
                <td><?= $verify_okpay[$i]['emp']['cnt']?></td>
            </tr>
            <tr>
                <td>Всего</td>
                <td><?= ($verify_okpay_compl[$i]['frl']['cnt'] + $verify_okpay_compl[$i]['emp']['cnt'])?></td>
                <td><?= ($verify_okpay[$i]['frl']['cnt'] + $verify_okpay[$i]['emp']['cnt'])?></td>
            </tr>
        </table>
    </td>
</tr>
<tr><td colspan=2 style="padding:10px"><strong>Страны (верифицированных)</strong></td></tr>

<tr>
	<td colspan="2">
        <? if(!empty($verify_country[$i])) { ?>
        <table border="1" cellspacing="2" cellpadding="2"  class="brd-tbl" style="width:100%">
            <colgroup>
                <col width="90"/>
            </colgroup>    
            <? foreach($verify_country[$i] as $stat) { ?>
                <tr>
                    <td><?= $stat['country_name']?></td>
                    <td><?= $stat['cnt']?></td>
                </tr>
            <? }//foreach?>
        </table>
        <? } else {//if?>
        <table border="1" cellspacing="2" cellpadding="2"  class="brd-tbl" style="width:100%">
            <tr><td>&mdash;</td></tr>
        </table>
        <? } ?>
    </td>
</tr>
<tr><td colspan=2 style="padding:10px"><strong>Всего:</strong></td></tr>
<tr>
	<td colspan="2">
        <table style="width:100%" class="brd-tbl">
            <colgroup>
                <col width="90"/>
                <col width="80"/>
                <col width="80"/>
            </colgroup>
            <tr>
                <td></td>
                <td>Успешно</td>
                <td>Не успешно</td>
            </tr>
            <tr>
                <td>Фрилансеров</td>
                <td><?= ($frl_all_c = (
                    $verify_wm_compl[$i]['frl']['cnt']
                    + $verify_ffpro_compl[$i]['frl']['cnt']
                    + $verify_ffnopro_compl[$i]['frl']['cnt']
                    + $verify_yd_compl[$i]['frl']['cnt']
                    + $verify_pskb_compl[$i]['frl']['cnt']
                ))?></td>
                <td><?= ($frl_all = (
                    $verify_wm[$i]['frl']['cnt'] 
                    + $verify_ffpro[$i]['frl']['cnt'] 
                    + $verify_ffnopro[$i]['frl']['cnt']
                    + $verify_yd[$i]['frl']['cnt']
                    + $verify_pskb[$i]['frl']['cnt']
                ))?></td>
            </tr>
            <tr>
                <td>Работодателей</td>
                <td><?= ($emp_all_c = (
                    $verify_wm_compl[$i]['emp']['cnt'] 
                    + $verify_ffpro_compl[$i]['emp']['cnt'] 
                    + $verify_ffnopro_compl[$i]['emp']['cnt']
                    + $verify_yd_compl[$i]['emp']['cnt']
                    + $verify_pskb_compl[$i]['emp']['cnt']
                ))?></td>
                <td><?= ($emp_all = (
                    $verify_wm[$i]['emp']['cnt'] 
                    + $verify_ffpro[$i]['emp']['cnt'] 
                    + $verify_ffnopro[$i]['emp']['cnt']
                    + $verify_yd[$i]['emp']['cnt']
                    + $verify_pskb[$i]['emp']['cnt']
                ))?></td>
            </tr>
            <tr>
                <td>Всего</td>
                <td><?= ($frl_all_c + $emp_all_c)?></td>
                <td><?= ($frl_all + $emp_all)?></td>
            </tr>
        </table>
    </td>
	<!-- <td><span title="Количество пользователей прошедших верификацию"><?= ( $verify_wm_compl[$i]['cnt'] + $verify_ffpro_compl[$i]['cnt'] + $verify_ffnopro_compl[$i]['cnt'] )?></span> (<span title="Количество пользователей не закончивших проходить верификацию"><?= ( $verify_wm[$i]['cnt'] + $verify_ffpro[$i]['cnt'] + $verify_ffnopro[$i]['cnt'] )?></span>)</td> -->
</tr>
</table>
<?}?>