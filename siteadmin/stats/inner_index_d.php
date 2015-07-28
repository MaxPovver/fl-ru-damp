<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Verification.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stats.php");
$DB = new DB('master');
?>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left"><strong>Статистика</strong></td>
</tr>
</table>


<br><br>

<?php $mIndex = true; require_once ("top_menu.php"); ?>
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


</form>



<?php
$prop[$i] = account::GetPROStat($fdate, $tdate, 0);
$testpro[$i] = account::GetStatOP(47, $fdate, $tdate);
$prop2[$i] = account::GetPROStat($fdate, $tdate);
$ppp[$i] = account::GetStatOP(array(8), $fdate, $tdate);
$gpp[$i] = account::GetStatOP(array(16,17,18,34,35), $fdate, $tdate, "", "RIGHT JOIN present ON billing_from_id = account_operations.id");
$fpp[$i] = account::GetStatOP(array(10,11), $fdate, $tdate);
$fppc[$i] = account::GetStatOP(array(19), $fdate, $tdate);
$fppci[$i] = account::GetStatOP(array(20), $fdate, $tdate);
$cho[$i] = account::GetStatOP(array(21), $fdate, $tdate);

$konkCodes = new_projects::getContestOpCodes();
$konk[$i] = account::GetStatOP($konkCodes, $fdate, $tdate);

$upproj[$i] = account::GetStatOP(array(7), $fdate, $tdate);
$transf[$i] = account::GetStatOP(array(23), $fdate, $tdate);
$testbuypro[$i] = account::GetStatTestBuyPro($fdate,$tdate);
$bonuses[$i] = account::GetStatBonuses($fdate,$tdate);

// Статистика по регистрациям и привязке мобильных телефонов
$regs = stats::getRegStats($fdate, $tdate);

?>

<table  border="1" cellspacing="2" cellpadding="2"  class="brd-tbl">
<tr>
	<td width=200><strong>Проекты:</strong></td>
	<td>
        <?php
        $sql = "SELECT COUNT(*) as cnt FROM projects WHERE kind != 9 AND post_date >= ? AND post_date - '1 day'::interval < ?";
        $s_project = $DB->rows($sql, $fdate, $tdate);
        ?>
        <?=$s_project[0]['cnt']?>
    </td>
</tr>
<tr>
	<td width=200><strong>Проекты (заблокированно):</strong></td>
	<td>
        <?php
        $sql = "SELECT COUNT(*) AS cnt FROM projects p INNER JOIN projects_blocked pb ON pb.project_id=p.id WHERE p.post_date >= ? AND p.post_date - '1 day'::interval < ?";
        $s_bproject = $DB->rows($sql, $fdate, $tdate);
        ?>
        <?=$s_bproject[0]['cnt']?>
    </td>
</tr>
<tr>
	<td width=200><strong>Проекты (для ПРО):</strong></td>
	<td>
        <?php
        $sql = "SELECT COUNT(*) as cnt FROM projects WHERE pro_only='t' AND post_date >= ? AND post_date - '1 day'::interval < ?";
        $s_pproject = $DB->rows($sql, $fdate, $tdate);
        ?>
        <?=$s_pproject[0]['cnt']?>
    </td>
</tr>
<tr>
	<td width=200><strong>Среднее кол-во ответов на проекты (для ПРО):</strong></td>
	<td>
        <?php
        $c_projectoffers = 0;
        $sql = "SELECT COUNT(*) as cnt FROM projects_offers po INNER JOIN projects p ON p.id=po.project_id AND p.pro_only='t' WHERE p.post_date >= ? AND p.post_date - '1 day'::interval < ?";
        $s_project_offers = $DB->rows($sql, $fdate, $tdate);
        if($s_pproject[0]['cnt']>0) {
        	$c_projectoffers = round($s_project_offers[0]['cnt']/$s_pproject[0]['cnt'],2);
        }
        ?>
        <?=$c_projectoffers?>
    </td>
</tr>
<tr>
	<td width=200><strong>Кол-во ответов на проекты:</strong></td>
	<td>
        <?php
        $sql = "SELECT COUNT(*) as cnt FROM projects_offers WHERE post_date >= ? AND post_date - '1 day'::interval < ?";
        $s_project_offers = $DB->rows($sql, $fdate, $tdate);
        ?>
        <?=$s_project_offers[0]['cnt']?>
    </td>
</tr>
<tr>
	<td width=200><strong>Среднее кол-во проектов:</strong></td>
	<td>
        <?
        list($fyd, $fmd, $fdd) = preg_split("/-/",$fdate);
        list($tyd, $tmd, $tdd) = preg_split("/-/",$tdate);
        $daysd = 1+(mktime(0,0,0,$tmd,$tdd,$tyd)-mktime(0,0,0,$fmd,$fdd,$fyd))/60/60/24;
        echo round($s_project[0]['cnt']/$daysd,2);
        ?>
    </td>
</tr>
<tr>
	<td width=200><strong>Среднее кол-во ответов на проект:</strong></td>
	<td>
        <?php
        $sql = "select count(1) as cnt from projects_offers where post_date >= ? AND post_date - '1 day'::interval < ?";
        $s_project_offers = $DB->rows($sql, $fdate, $tdate, $fdate, $tdate);

        ?>
        <?php if($s_project[0]['cnt']==0) { echo '0'; } else { echo round($s_project_offers[0]['cnt']/$s_project[0]['cnt'],2); } ?>
    </td>
</tr>
<tr>
	<td width=200><strong>Кол-во проектов для верифицированных:</strong></td>
	<td>
        <?php
        $sql = "select count(1) as cnt from projects where verify_only = true AND post_date >= ? AND post_date - '1 day'::interval < ?";
        $s_project_only_verify = $DB->row($sql, $fdate, $tdate, $fdate, $tdate);

        ?>
        <?= $s_project_only_verify['cnt']?>
    </td>
</tr>
<tr>
	<td width=200><strong>Среднее кол-во ответов на проект для верифицированных:</strong></td>
	<td>
        <?php
        $sql = "SELECT count(1) as cnt FROM projects p
                INNER JOIN projects_offers po ON p.id = po.project_id
                WHERE p.verify_only = true AND p.post_date >= ? AND p.post_date - '1 day'::interval < ?";
        $s_project_offers_only_verify = $DB->row($sql, $fdate, $tdate, $fdate, $tdate);
        ?>
        <?php if($s_project_only_verify['cnt']==0) { echo '0'; } else { echo round($s_project_offers_only_verify['cnt']/$s_project_only_verify['cnt'],2); } ?>
    </td>
</tr>
<tr>
	<td width=200><strong>Кол-во проектов для верифицированных и ПРО:</strong></td>
	<td>
        <?php
        $sql = "select count(1) as cnt from projects WHERE (verify_only = true AND pro_only = true) AND post_date >= ? AND post_date - '1 day'::interval < ?";
        $s_project_only_verify = $DB->row($sql, $fdate, $tdate, $fdate, $tdate);

        ?>
        <?= $s_project_only_verify['cnt']?>
    </td>
</tr>
<tr>
	<td width=200><strong>Среднее кол-во ответов на проект для верифицированных и ПРО:</strong></td>
	<td>
        <?php
        $sql = "SELECT count(1) as cnt FROM projects p
                INNER JOIN projects_offers po ON p.id = po.project_id
                WHERE ( p.verify_only = true AND p.pro_only = true ) AND p.post_date >= ? AND p.post_date - '1 day'::interval < ?";
        $s_project_offers_only_verify = $DB->row($sql, $fdate, $tdate, $fdate, $tdate);
        ?>
        <?php if($s_project_only_verify['cnt']==0) { echo '0'; } else { echo round($s_project_offers_only_verify['cnt']/$s_project_only_verify['cnt'],2); } ?>
    </td>
</tr>
<tr>
	<td width=200><strong>Количество переносов в вакансии:</strong></td>
	<td>
        <?php
        $sql = "SELECT count(1) FROM projects p
                WHERE moved_vacancy::character != '' AND p.moved_vacancy >= ? AND p.moved_vacancy - '1 day'::interval < ?";
        $s_project_moved_vacancy = $DB->val($sql, $fdate, $tdate);
        ?>
        <?=$s_project_moved_vacancy?>
    </td>
</tr>
<tr>
	<td width=200>Из них оплачено:</td>
	<td>
        <?php
        $sql = "SELECT count(1) as cnt FROM projects p
                WHERE p.moved_vacancy_pro IS NULL AND p.moved_vacancy::character != '' AND p.state = 0 AND p.moved_vacancy >= ? AND p.moved_vacancy - '1 day'::interval < ?";
        $s_project_moved_vacancy_payed = $DB->val($sql, $fdate, $tdate);
        ?>
        <?=$s_project_moved_vacancy_payed?>
    </td>
</tr>
<tr>
	<td width=200>Из них бесплатные у PRO</td>
	<td>
        <?php
        $sql = "SELECT count(1) as cnt FROM projects p
                WHERE p.moved_vacancy_pro = TRUE AND p.moved_vacancy::character != '' AND p.state = 0 AND p.moved_vacancy >= ? AND p.moved_vacancy - '1 day'::interval < ?";
        $s_project_moved_vacancy_pro = $DB->val($sql, $fdate, $tdate);
        ?>
        <?=$s_project_moved_vacancy_pro?>
    </td>
</tr>
<tr><td colspan=2><strong>Регистрации</strong></td></tr>
<tr>
	<td>- Фрилансеры:</td>
	<td>  <?=$regs['reg']['frl']?>  </td>
</tr>
<tr>
	<td>- Работадатели:</td>
	<td>  <?=$regs['reg']['emp']?>  </td>
</tr>
<tr>
	<td>- Всего:</td>
	<td>  <?=$regs['reg']['all']?>  </td>
</tr>

</table>


