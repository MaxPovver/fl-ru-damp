<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
$profs = professions::GetAllGroupsLite();
?>


<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left"><strong>Статистика</strong></td>
	<td align="right"><a href="/siteadmin/stats/index.php">Таблица</a></td>
</tr>
</table>


<br><br>

<script>
function getspec(RXGroup) {
     var radioRXGroup = document.getElementsByName(RXGroup);
     for (var RowR = 0; RowR < radioRXGroup.length; RowR++)
     {
        if(radioRXGroup[RowR].checked)
        {
           return radioRXGroup[RowR].value;
           break;
        }
     }
}
</script>

<center>
<a href="#" onClick="if(document.getElementById('pspec').style.display=='none') { document.getElementById('pspec').style.display='block'; } else { document.getElementById('pspec').style.display='none'; } return false;">Показать/скрыть выбор специализации</a>
<br><br>
<form method="post" name="prj_frm" id="prj_frm" onSubmit="document.getElementById('g_prj').src = 'c_prj.php?y='+document.getElementById('prj_year').value+'&m='+document.getElementById('prj_month').options[document.getElementById('prj_month').selectedIndex].value+'&rnd='+Math.random(1000)+'&s='+getspec('spec_prj'); return false;">

<div id="pspec" style="display:none;">
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
  <tr valign="top">
    <td style="padding:10 0 0 30">
      <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
        <tr valign="top">
          <td style="width:33%;padding-bottom:20px">
            <? 
              $lastgrname = NULL;
              $j=0;
              foreach($profs as $prof)
              { 
              ?>
                <table border="0" cellspacing="0" cellpadding="1">
                  <tr>
                    <td valign="top">
                      <input type="radio" name='spec_prj' value="<?=$prof['id']?>" id="mlb<?=$prof['id']?>">
                    </td>
                    <td width="100%">
                      <label for="lb<?=$prof['id']?>"><?=$prof['name']?></label>
                    </td>
                  </tr>
                </table>
           <? } ?>
          </td>
        </tr>
      </table>
     </td>
   </tr>
 </table>
</div>

<br>

<select name="prj_month" id="prj_month">
	<option value="01" <? if (date('m') == '01') print "SELECTED"?>>январь</option>
	<option value="02" <? if (date('m') == '02') print "SELECTED"?>>февраль</option>
	<option value="03" <? if (date('m') == '03') print "SELECTED"?>>март</option>
	<option value="04" <? if (date('m') == '04') print "SELECTED"?>>апрель</option>
	<option value="05" <? if (date('m') == '05') print "SELECTED"?>>май</option>
	<option value="06" <? if (date('m') == '06') print "SELECTED"?>>июнь</option>
	<option value="07" <? if (date('m') == '07') print "SELECTED"?>>июль</option>
	<option value="08" <? if (date('m') == '08') print "SELECTED"?>>август</option>
	<option value="09" <? if (date('m') == '09') print "SELECTED"?>>сентябрь</option>
	<option value="10" <? if (date('m') == '10') print "SELECTED"?>>октябрь</option>
	<option value="11" <? if (date('m') == '11') print "SELECTED"?>>ноябрь</option>
	<option value="12" <? if (date('m') == '12') print "SELECTED"?>>декабрь</option>
</select>
<input type="text" name="prj_year" id="prj_year" size="4" maxlength="4" value="<?=date('Y')?>">





<input type="submit" value="Ага!"><br>
</form>

<img src="c_prj.php?m=<?=date('m')?>&y=<?=date('Y')?>&s=0" id="g_prj">



</center>
