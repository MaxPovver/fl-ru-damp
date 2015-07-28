<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left"><strong>—татистика</strong></td>
	<td align="right"><a href="/siteadmin/stats/">“аблица</a></td>
</tr>
</table>
<br>

<?
//вычисл€ем в каких мес€цах была активность начина€ с 2006 года
$aMonthes[1] = '€нварь';
$aMonthes[2] = 'февраль';
$aMonthes[3] = 'март';
$aMonthes[4] = 'апрель';
$aMonthes[5] = 'май';
$aMonthes[6] = 'июнь';
$aMonthes[7] = 'июль';
$aMonthes[8] = 'август';
$aMonthes[9] = 'сент€брь';
$aMonthes[10] = 'окт€брь';
$aMonthes[11] = 'но€брь';
$aMonthes[12] = 'декабрь';
$aData = array();
for ($i=2006; $i<=date('Y'); $i++) {
	for ($j=1; $j<=12;$j++) {
		$aData[$i][$j]['data'] = 0;
		$aData[$i][$j]['date_m'] = 0;
		$aData[$i][$j]['date_y'] = 0;
	}

}
$DB = new DB('master');

for ($i=2006,$Y=date('Y'); $i<=$Y; $i++) {
	$date_from = $i.'-01-01';
	$date_to = ($i+1).'-01-01';
	$sql = "SELECT SUM(trs_sum) as ammount, to_char(op_date,'MM') as _day FROM
			account_operations WHERE op_date >= ? AND op_date < ? GROUP BY to_char(op_date,'MM') ORDER BY to_char(op_date,'MM')";
    if($i < $Y) {
    	$aTemp = $DB->cache(0)->rows($sql, $date_from, $date_to);
	} else {
    	$aTemp = $DB->rows($sql, $date_from, $date_to);
	}
	$aTemp = $DB->rows($sql, $date_from, $date_to);
	for ($j=0; $j<count($aTemp); $j++) {
		$iMonth = intval($aTemp[$j]['_day']);
		$aData[$i][$iMonth]['data'] = true;
		$aData[$i][$iMonth]['date_m'] = $aTemp[$j]['_day'];
		$aData[$i][$iMonth]['date_y'] = $i;
	}
}
?>

<script type="text/javascript">
var cur_y = '<?=date('Y');?>';
var cur_m = '<?=date('m');?>';
function showStats(y, m, ids) {
	var temp = new Array();
	temp = ids.split(',');

	document.getElementById('g_country_'+cur_y+'_'+cur_m).className = 'dotted_';
	document.getElementById('g_city_'+cur_y+'_'+cur_m).className = 'dotted_';
	document.getElementById('g_project_'+cur_y+'_'+cur_m).className = 'dotted_';
	document.getElementById('g_reg_'+cur_y+'_'+cur_m).className = 'dotted_';
	document.getElementById('g_service_'+cur_y+'_'+cur_m).className = 'dotted_';
	document.getElementById('g_pro_'+cur_y+'_'+cur_m).className = 'dotted_';
	document.getElementById('g_live_'+cur_y+'_'+cur_m).className = 'dotted_';
	document.getElementById('g_feedback_'+cur_y+'_'+cur_m).className = 'dotted_';
	document.getElementById('g_feedbackh_'+cur_y+'_'+cur_m).className = 'dotted_';
	document.getElementById('g_bonuses_'+cur_y+'_'+cur_m).className = 'dotted_';

    cur_y = y;
    cur_m = m;
    
	for (i=0; i<temp.length; i++) {
        try {
    		document.getElementById('g_country_'+temp[i]).className = 'dotted_';
    		document.getElementById('g_city_'+temp[i]).className = 'dotted_';
    		document.getElementById('g_project_'+temp[i]).className = 'dotted_';
    		document.getElementById('g_reg_'+temp[i]).className = 'dotted_';
    		document.getElementById('g_service_'+temp[i]).className = 'dotted_';
    		document.getElementById('g_pro_'+temp[i]).className = 'dotted_';
    		document.getElementById('g_live_'+temp[i]).className = 'dotted_';
            document.getElementById('g_feedback_'+temp[i]).className = 'dotted_';
            document.getElementById('g_bonuses_'+temp[i]).className = 'dotted_';
            document.getElementById('g_feedbackh_'+temp[i]).className = 'dotted_';
        } catch(err) {}
	}

	if (m) {
		document.getElementById('g_country').src = 'g_country.php?y='+y+'&m='+m;
		document.getElementById('g_city').src = 'g_city.php?y='+y+'&m='+m;
		document.getElementById('g_project').src = 'g_project.php?y='+y+'&m='+m;
		document.getElementById('g_reg').src = 'g_reg.php?y='+y+'&m='+m;
		document.getElementById('g_service').src = 'g_service.php?y='+y+'&m='+m;
		document.getElementById('g_pro').src = 'g_pro.php?y='+y+'&m='+m;
		document.getElementById('g_live').src = 'g_live.php?y='+y+'&m='+m;
		document.getElementById('g_feedback').src = 'g_feedback.php?y='+y+'&m='+m;
		document.getElementById('g_bonuses').src = 'g_bonuses.php?y='+y+'&m='+m;
		document.getElementById('g_feedbackh').src = 'g_feedback_hours.php?y='+y+'&m='+m;
	}
	else {
		document.getElementById('g_country').src = 'g_country.php?y='+y+'&rnd='+Math.random(1000);
		document.getElementById('g_city').src = 'g_city.php?y='+y+'&rnd='+Math.random(1000);
		document.getElementById('g_project').src = 'g_project.php?y='+y+'&rnd='+Math.random(1000);
		document.getElementById('g_reg').src = 'g_reg.php?y='+y+'&rnd='+Math.random(1000);
		document.getElementById('g_service').src = 'g_service.php?y='+y+'&rnd='+Math.random(1000);
		document.getElementById('g_pro').src = 'g_pro.php?y='+y+'&rnd='+Math.random(1000);
		document.getElementById('g_live').src = 'g_live.php?y='+y+'&rnd='+Math.random(1000);
		document.getElementById('g_feedback').src = 'g_feedback.php?y='+y+'&rnd='+Math.random(1000);
		document.getElementById('g_bonuses').src = 'g_bonuses.php?y='+y+'&rnd='+Math.random(1000);
		document.getElementById('g_feedbackh').src = 'g_feedback_hours.php?y='+y+'&rnd='+Math.random(1000);
	}

	document.getElementById('g_country_'+y+'_'+m).className = 'black_';
	document.getElementById('g_city_'+y+'_'+m).className = 'black_';
	document.getElementById('g_project_'+y+'_'+m).className = 'black_';
	document.getElementById('g_reg_'+y+'_'+m).className = 'black_';
	document.getElementById('g_service_'+y+'_'+m).className = 'black_';
	document.getElementById('g_pro_'+y+'_'+m).className = 'black_';
	document.getElementById('g_live_'+y+'_'+m).className = 'black_';
	document.getElementById('g_feedback_'+y+'_'+m).className = 'black_';
	document.getElementById('g_bonuses_'+y+'_'+m).className = 'black_';
	document.getElementById('g_feedbackh_'+y+'_'+m).className = 'black_';
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
		echo '<strong>'.$k.'</strong>:';
		for ($i=1; $i<=12; $i++) {
            $im = $i;
            if($i<10) $im = '0'.$i;
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$im.'\', \''.'\'); void(0);" id="g_country_'.$k.'_'.$im.'">'.$aMonthes[$i].'</a>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
 } ?>
<tr>
	<td><br><img src="g_country.php" id='g_country'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<?php
if (count($aData)) {
	foreach ($aData as $k => $v) {
		echo '<tr>
				<td style="padding-left: 100px">';
		echo '<strong>'.$k.'</strong>:';
		for ($i=1; $i<=12; $i++) {
            $im = $i;
            if($i<10) $im = '0'.$i;
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$im.'\', \''.'\'); void(0);" id="g_city_'.$k.'_'.$im.'">'.$aMonthes[$i].'</a>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
 } ?>
<tr>
	<td><br><img src="g_city.php" id='g_city'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<?php
if (count($aData)) {
	foreach ($aData as $k => $v) {
		echo '<tr>
				<td style="padding-left: 100px">';
		echo '<strong>'.$k.'</strong>:';
		for ($i=1; $i<=12; $i++) {
            $im = $i;
            if($i<10) $im = '0'.$i;
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$im.'\', \''.'\'); void(0);" id="g_project_'.$k.'_'.$im.'">'.$aMonthes[$i].'</a>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
 } ?>
<tr>
	<td><br><img src="g_project.php" id='g_project'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<?php
if (count($aData)) {
	foreach ($aData as $k => $v) {
		echo '<tr>
				<td style="padding-left: 100px">';
		echo '<strong>'.$k.'</strong>:';
		for ($i=1; $i<=12; $i++) {
            $im = $i;
            if($i<10) $im = '0'.$i;
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$im.'\', \''.'\'); void(0);" id="g_reg_'.$k.'_'.$im.'">'.$aMonthes[$i].'</a>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
 } ?>
<tr>
	<td><br><img src="g_reg.php" id='g_reg'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<?php
if (count($aData)) {
	foreach ($aData as $k => $v) {
		echo '<tr>
				<td style="padding-left: 100px">';
		echo '<strong>'.$k.'</strong>:';
		for ($i=1; $i<=12; $i++) {
            $im = $i;
            if($i<10) $im = '0'.$i;
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$im.'\', \''.'\'); void(0);" id="g_service_'.$k.'_'.$im.'">'.$aMonthes[$i].'</a>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
 } ?>
<tr>
	<td><br><img src="g_service.php" id='g_service'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<?php
if (count($aData)) {
	foreach ($aData as $k => $v) {
		echo '<tr>
				<td style="padding-left: 100px">';
		echo '<strong>'.$k.'</strong>:';
		for ($i=1; $i<=12; $i++) {
            $im = $i;
            if($i<10) $im = '0'.$i;
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$im.'\', \''.'\'); void(0);" id="g_pro_'.$k.'_'.$im.'">'.$aMonthes[$i].'</a>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
 } ?>
<tr>
	<td><br><img src="g_pro.php" id='g_pro'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<?php
if (count($aData)) {
	foreach ($aData as $k => $v) {
		echo '<tr>
				<td style="padding-left: 100px">';
		echo '<strong>'.$k.'</strong>:';
		for ($i=1; $i<=12; $i++) {
            $im = $i;
            if($i<10) $im = '0'.$i;
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$im.'\', \''.'\'); void(0);" id="g_live_'.$k.'_'.$im.'">'.$aMonthes[$i].'</a>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
 } ?>
<tr>
	<td><br><img src="g_live.php" id='g_live'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<?php
if (count($aData)) {
	foreach ($aData as $k => $v) {
		echo '<tr>
				<td style="padding-left: 100px">';
		echo '<strong>'.$k.'</strong>:';
		for ($i=1; $i<=12; $i++) {
            $im = $i;
            if($i<10) $im = '0'.$i;
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$im.'\', \''.'\'); void(0);" id="g_feedback_'.$k.'_'.$im.'">'.$aMonthes[$i].'</a>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
 } ?>
<tr>
	<td><br><img src="g_feedback.php" id='g_feedback'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<?php
if (count($aData)) {
	foreach ($aData as $k => $v) {
		echo '<tr>
				<td style="padding-left: 100px">';
		echo '<strong>'.$k.'</strong>:';
		for ($i=1; $i<=12; $i++) {
            $im = $i;
            if($i<10) $im = '0'.$i;
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$im.'\', \''.'\'); void(0);" id="g_feedbackh_'.$k.'_'.$im.'">'.$aMonthes[$i].'</a>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
 } ?>
<tr>
	<td><br><img src="g_feedback_hours.php" id='g_feedbackh'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<?php
if (count($aData)) {
	foreach ($aData as $k => $v) {
		echo '<tr>
				<td style="padding-left: 100px">';
		echo '<strong>'.$k.'</strong>:';
		for ($i=1; $i<=12; $i++) {
            $im = $i;
            if($i<10) $im = '0'.$i;
			echo '&nbsp;&nbsp;<a class="dotted_" href="javascript: showStats('.$k.',\''.$im.'\', \''.'\'); void(0);" id="g_bonuses_'.$k.'_'.$im.'">'.$aMonthes[$i].'</a>';
		}
		echo '</td>
				</tr>';
	}
	echo '<tr><td></td></tr>';
 } ?>
<tr>
	<td><br><img src="g_bonuses.php" id='g_bonuses'></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>


</table>

<script type="text/javascript">
document.getElementById('g_country_'+'<?php echo date('Y_m'); ?>').className = 'black_';
document.getElementById('g_city_'+'<?php echo date('Y_m'); ?>').className = 'black_';
document.getElementById('g_project_'+'<?php echo date('Y_m'); ?>').className = 'black_';
document.getElementById('g_reg_'+'<?php echo date('Y_m'); ?>').className = 'black_';
document.getElementById('g_service_'+'<?php echo date('Y_m'); ?>').className = 'black_';
document.getElementById('g_pro_'+'<?php echo date('Y_m'); ?>').className = 'black_';
document.getElementById('g_live_'+'<?php echo date('Y_m'); ?>').className = 'black_';
document.getElementById('g_feedback_'+'<?php echo date('Y_m'); ?>').className = 'black_';
document.getElementById('g_bonuses_'+'<?php echo date('Y_m'); ?>').className = 'black_';
document.getElementById('g_feedbackh_'+'<?php echo date('Y_m'); ?>').className = 'black_';
</script>
