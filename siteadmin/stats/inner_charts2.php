<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left"><strong>—татистика</strong></td>
	<td align="right"><a href="/siteadmin/stats/index.php">“аблица</a></td>
</tr>
</table>


<br><br>

<center>

<form method="post" name="reg_frm" id="reg_frm" onSubmit="document.getElementById('g_reg').src = 'c_reg.php?y='+document.getElementById('reg_year').value+'&m='+document.getElementById('reg_month').options[document.getElementById('reg_month').selectedIndex].value+'&rnd='+Math.random(1000); return false;">
<select name="reg_month" id="reg_month">
	<option value="01" <? if (date('m') == '01') print "SELECTED"?>>€нварь</option>
	<option value="02" <? if (date('m') == '02') print "SELECTED"?>>февраль</option>
	<option value="03" <? if (date('m') == '03') print "SELECTED"?>>март</option>
	<option value="04" <? if (date('m') == '04') print "SELECTED"?>>апрель</option>
	<option value="05" <? if (date('m') == '05') print "SELECTED"?>>май</option>
	<option value="06" <? if (date('m') == '06') print "SELECTED"?>>июнь</option>
	<option value="07" <? if (date('m') == '07') print "SELECTED"?>>июль</option>
	<option value="08" <? if (date('m') == '08') print "SELECTED"?>>август</option>
	<option value="09" <? if (date('m') == '09') print "SELECTED"?>>сент€брь</option>
	<option value="10" <? if (date('m') == '10') print "SELECTED"?>>окт€брь</option>
	<option value="11" <? if (date('m') == '11') print "SELECTED"?>>но€брь</option>
	<option value="12" <? if (date('m') == '12') print "SELECTED"?>>декабрь</option>
</select>
<input type="text" name="reg_year" id="reg_year" size="4" maxlength="4" value="<?=date('Y')?>">
<input type="submit" value="јга!"><br>
</form>

<img src="c_reg.php?m=<?=date('m')?>&y=<?=date('Y')?>" id="g_reg">


<br><br><br><br>


<form method="post" name="pro_frm" id="pro_frm" onSubmit="document.getElementById('g_pro').src = 'c_pro.php?y='+document.getElementById('pro_year').value+'&m='+document.getElementById('pro_month').options[document.getElementById('pro_month').selectedIndex].value+'&rnd='+Math.random(1000); return false;">
<select name="pro_month" id="pro_month">
	<option value="01" <? if (date('m') == '01') print "SELECTED"?>>€нварь</option>
	<option value="02" <? if (date('m') == '02') print "SELECTED"?>>февраль</option>
	<option value="03" <? if (date('m') == '03') print "SELECTED"?>>март</option>
	<option value="04" <? if (date('m') == '04') print "SELECTED"?>>апрель</option>
	<option value="05" <? if (date('m') == '05') print "SELECTED"?>>май</option>
	<option value="06" <? if (date('m') == '06') print "SELECTED"?>>июнь</option>
	<option value="07" <? if (date('m') == '07') print "SELECTED"?>>июль</option>
	<option value="08" <? if (date('m') == '08') print "SELECTED"?>>август</option>
	<option value="09" <? if (date('m') == '09') print "SELECTED"?>>сент€брь</option>
	<option value="10" <? if (date('m') == '10') print "SELECTED"?>>окт€брь</option>
	<option value="11" <? if (date('m') == '11') print "SELECTED"?>>но€брь</option>
	<option value="12" <? if (date('m') == '12') print "SELECTED"?>>декабрь</option>
</select>
<input type="text" name="pro_year" id="pro_year" size="4" maxlength="4" value="<?=date('Y')?>">
<input type="submit" value="јга!"><br>
</form>

<img src="c_pro.php?m=<?=date('m')?>&y=<?=date('Y')?>" id="g_pro">


<br><br><br><br>


<form method="post" name="frl_frm" id="frl_frm" onSubmit="document.getElementById('g_frl').src = 'c_frl.php?y='+document.getElementById('frl_year').value+'&m='+document.getElementById('frl_month').options[document.getElementById('frl_month').selectedIndex].value+'&rnd='+Math.random(1000); return false;">
<select name="frl_month" id="frl_month">
	<option value="01" <? if (date('m') == '01') print "SELECTED"?>>€нварь</option>
	<option value="02" <? if (date('m') == '02') print "SELECTED"?>>февраль</option>
	<option value="03" <? if (date('m') == '03') print "SELECTED"?>>март</option>
	<option value="04" <? if (date('m') == '04') print "SELECTED"?>>апрель</option>
	<option value="05" <? if (date('m') == '05') print "SELECTED"?>>май</option>
	<option value="06" <? if (date('m') == '06') print "SELECTED"?>>июнь</option>
	<option value="07" <? if (date('m') == '07') print "SELECTED"?>>июль</option>
	<option value="08" <? if (date('m') == '08') print "SELECTED"?>>август</option>
	<option value="09" <? if (date('m') == '09') print "SELECTED"?>>сент€брь</option>
	<option value="10" <? if (date('m') == '10') print "SELECTED"?>>окт€брь</option>
	<option value="11" <? if (date('m') == '11') print "SELECTED"?>>но€брь</option>
	<option value="12" <? if (date('m') == '12') print "SELECTED"?>>декабрь</option>
</select>
<input type="text" name="frl_year" id="frl_year" size="4" maxlength="4" value="<?=date('Y')?>">
<input type="submit" value="јга!"><br>
</form>

<img src="c_frl.php?m=<?=date('m')?>&y=<?=date('Y')?>" id="g_frl">


<br><br><br><br>


<form method="post" name="emp_frm" id="emp_frm" onSubmit="document.getElementById('g_emp').src = 'c_emp.php?y='+document.getElementById('emp_year').value+'&m='+document.getElementById('emp_month').options[document.getElementById('emp_month').selectedIndex].value+'&rnd='+Math.random(1000); return false;">
<select name="emp_month" id="emp_month">
	<option value="01" <? if (date('m') == '01') print "SELECTED"?>>€нварь</option>
	<option value="02" <? if (date('m') == '02') print "SELECTED"?>>февраль</option>
	<option value="03" <? if (date('m') == '03') print "SELECTED"?>>март</option>
	<option value="04" <? if (date('m') == '04') print "SELECTED"?>>апрель</option>
	<option value="05" <? if (date('m') == '05') print "SELECTED"?>>май</option>
	<option value="06" <? if (date('m') == '06') print "SELECTED"?>>июнь</option>
	<option value="07" <? if (date('m') == '07') print "SELECTED"?>>июль</option>
	<option value="08" <? if (date('m') == '08') print "SELECTED"?>>август</option>
	<option value="09" <? if (date('m') == '09') print "SELECTED"?>>сент€брь</option>
	<option value="10" <? if (date('m') == '10') print "SELECTED"?>>окт€брь</option>
	<option value="11" <? if (date('m') == '11') print "SELECTED"?>>но€брь</option>
	<option value="12" <? if (date('m') == '12') print "SELECTED"?>>декабрь</option>
</select>
<input type="text" name="emp_year" id="emp_year" size="4" maxlength="4" value="<?=date('Y')?>">
<input type="submit" value="јга!"><br>
</form>

<img src="c_emp.php?m=<?=date('m')?>&y=<?=date('Y')?>" id="g_emp">


<br><br><br><br>


<form method="post" name="lfrl_frm" id="lfrl_frm" onSubmit="document.getElementById('g_lfrl').src = 'c_lfrl.php?y='+document.getElementById('lfrl_year').value+'&m='+document.getElementById('lfrl_month').options[document.getElementById('lfrl_month').selectedIndex].value+'&rnd='+Math.random(1000); return false;">
<select name="lfrl_month" id="lfrl_month">
	<option value="01" <? if (date('m') == '01') print "SELECTED"?>>€нварь</option>
	<option value="02" <? if (date('m') == '02') print "SELECTED"?>>февраль</option>
	<option value="03" <? if (date('m') == '03') print "SELECTED"?>>март</option>
	<option value="04" <? if (date('m') == '04') print "SELECTED"?>>апрель</option>
	<option value="05" <? if (date('m') == '05') print "SELECTED"?>>май</option>
	<option value="06" <? if (date('m') == '06') print "SELECTED"?>>июнь</option>
	<option value="07" <? if (date('m') == '07') print "SELECTED"?>>июль</option>
	<option value="08" <? if (date('m') == '08') print "SELECTED"?>>август</option>
	<option value="09" <? if (date('m') == '09') print "SELECTED"?>>сент€брь</option>
	<option value="10" <? if (date('m') == '10') print "SELECTED"?>>окт€брь</option>
	<option value="11" <? if (date('m') == '11') print "SELECTED"?>>но€брь</option>
	<option value="12" <? if (date('m') == '12') print "SELECTED"?>>декабрь</option>
</select>
<input type="text" name="lfrl_year" id="lfrl_year" size="4" maxlength="4" value="<?=date('Y')?>">
<input type="submit" value="јга!"><br>
</form>

<img src="c_lfrl.php?m=<?=date('m')?>&y=<?=date('Y')?>" id="g_lfrl">


<br><br><br><br>


<form method="post" name="lemp_frm" id="lemp_frm" onSubmit="document.getElementById('g_lemp').src = 'c_lemp.php?y='+document.getElementById('lemp_year').value+'&m='+document.getElementById('lemp_month').options[document.getElementById('lemp_month').selectedIndex].value+'&rnd='+Math.random(1000); return false;">
<select name="lemp_month" id="lemp_month">
	<option value="01" <? if (date('m') == '01') print "SELECTED"?>>€нварь</option>
	<option value="02" <? if (date('m') == '02') print "SELECTED"?>>февраль</option>
	<option value="03" <? if (date('m') == '03') print "SELECTED"?>>март</option>
	<option value="04" <? if (date('m') == '04') print "SELECTED"?>>апрель</option>
	<option value="05" <? if (date('m') == '05') print "SELECTED"?>>май</option>
	<option value="06" <? if (date('m') == '06') print "SELECTED"?>>июнь</option>
	<option value="07" <? if (date('m') == '07') print "SELECTED"?>>июль</option>
	<option value="08" <? if (date('m') == '08') print "SELECTED"?>>август</option>
	<option value="09" <? if (date('m') == '09') print "SELECTED"?>>сент€брь</option>
	<option value="10" <? if (date('m') == '10') print "SELECTED"?>>окт€брь</option>
	<option value="11" <? if (date('m') == '11') print "SELECTED"?>>но€брь</option>
	<option value="12" <? if (date('m') == '12') print "SELECTED"?>>декабрь</option>
</select>
<input type="text" name="lemp_year" id="lemp_year" size="4" maxlength="4" value="<?=date('Y')?>">
<input type="submit" value="јга!"><br>
</form>

<img src="c_lemp.php?m=<?=date('m')?>&y=<?=date('Y')?>" id="g_lemp">


</center>
