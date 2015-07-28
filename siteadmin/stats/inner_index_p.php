<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
$DB = new DB('master');
?>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left"><strong>Статистика</strong></td>
	<td align="right"><a href="/siteadmin/stats/charts.php">График</a></td>
</tr>
</table>


<br><br>

<?php $mPro = true; require_once ("top_menu.php"); ?>
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

list($frlpp, $emppp) = account::getStatsPRO($fdate, $tdate);
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


</form>



<table  width="100%" border="1" cellspacing="2" cellpadding="2" class="brd-tbl">
    <tr>
        <td colspan="2"><strong>Фрилансеры:</strong></td>
    </tr>
    <tr>
        <td width=500>- Покупок тестового pro:</td>
        <td><?=$frlpp['tp']?></td>
    </tr>
    <tr>
        <td>- Купившие полный pro после тестового pro:</td>
        <td><?=$frlpp['fpp_tp']?></td>
    </tr>
    <tr>
        <td>- Купившие полный pro 2 раза после тестового pro:</td>
        <td><?=$frlpp['fpp2_tp']?></td>
    </tr>
    <tr>
        <td>- Купившие полный pro 3 раза после тестового pro:</td>
        <td><?=$frlpp['fpp3_tp']?></td>
    </tr>
    <tr>
        <td>- Купившие полный pro 4 раза после тестового pro:</td>
        <td><?=$frlpp['fpp4_tp']?></td>
    </tr>
    <tr>
        <td>- Купившие полный pro 5 раз после тестового pro:</td>
        <td><?=$frlpp['fpp5_tp']?></td>
    </tr>
    <!-- 
    <tr>
        <td colspan="2"><strong>Фрилансеры:</strong></td>
    </tr>-->
    <tr>
        <td>- Купивших полноценный pro:</td>
        <td><?=$frlpp['fpp']?></td>
    </tr>
    <tr>
        <td>- Купившие полноценный pro 2 раза:</td>
        <td><?=$frlpp['fpp2']?></td>
    </tr>
    <tr>
        <td>- Купившие полноценный pro 3 раза:</td>
        <td><?=$frlpp['fpp3']?></td>
    </tr>
    <tr>
        <td>- Купившие полноценный pro 4 раза:</td>
        <td><?=$frlpp['fpp4']?></td>
    </tr>
    <tr>
        <td>- Купившие полноценный pro 5 раз:</td>
        <td><?=$frlpp['fpp5']?></td>
    </tr>
    <!-- -->
    <tr>
        <td colspan="2"><strong>Работодатели:</strong></td>
    </tr>
    <tr>
        <td>- Купивших полноценный pro:</td>
        <td><?=$emppp['epp']?></td>
    </tr>
    <tr>
        <td>- Купившие полноценный pro 2 раза:</td>
        <td><?=$emppp['epp2']?></td>
    </tr>
    <tr>
        <td>- Купившие полноценный pro 3 раза:</td>
        <td><?=$emppp['epp3']?></td>
    </tr>
    <tr>
        <td>- Купившие полноценный pro 4 раза:</td>
        <td><?=$emppp['epp4']?></td>
    </tr>
    <tr>
        <td>- Купившие полноценный pro 5 раз:</td>
        <td><?=$emppp['epp5']?></td>
    </tr>
</table>

