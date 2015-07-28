<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
$prfs = new professions();
$profs = $prfs->GetAllProfessionsSpec(0);
?>


<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left"><strong>—татистика</strong></td>
	<td align="right"><a href="/siteadmin/stats/index.php">“аблица</a></td>
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
<a href="#" onClick="if(document.getElementById('mspec').style.display=='none') { document.getElementById('mspec').style.display='block'; } else { document.getElementById('mspec').style.display='none'; } return false;">ѕоказать/скрыть выбор специализации</a>
<br><br>
<form method="post" name="mspec_frm" id="mspec_frm" onSubmit="document.getElementById('g_mspec').src = 'c_mspec.php?y='+document.getElementById('mspec_year').value+'&m='+document.getElementById('mspec_month').options[document.getElementById('mspec_month').selectedIndex].value+'&rnd='+Math.random(1000)+'&s='+getspec('spec_main'); return false;">

<div id="mspec" style="display:none;">
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
                if($lastgrname != $prof['groupname'])
                {
                  if($j) {
                    print('</td>');
                    if($j % 2 == 0) {
                      print('</tr><tr valign="top">');
                    }
                    print('<td style="width:33%;padding-bottom:20px">');
                  }

                  ?><div style="padding-bottom:2px">&nbsp;<strong><?=$prof['groupname']?></strong></div><?
                  $j++;
                  $lastgrname = $prof['groupname'];
                }
              ?>
                <table border="0" cellspacing="0" cellpadding="1">
                  <tr>
                    <td valign="top">
                      <input type="radio" name='spec_main' value="<?=$prof['id']?>" id="mlb<?=$prof['id']?>">
                    </td>
                    <td width="100%">
                      <label for="lb<?=$prof['id']?>"><?=$prof['profname']?></label>
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

<select name="mspec_month" id="mspec_month">
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
<input type="text" name="mspec_year" id="mspec_year" size="4" maxlength="4" value="<?=date('Y')?>">





<input type="submit" value="јга!"><br>
</form>

<img src="c_mspec.php?m=<?=date('m')?>&y=<?=date('Y')?>&s=0" id="g_mspec">


<br><br><br><br><br>


<a href="#" onClick="if(document.getElementById('aspec').style.display=='none') { document.getElementById('aspec').style.display='block'; } else { document.getElementById('aspec').style.display='none'; } return false;">ѕоказать/скрыть выбор специализации</a>
<br><br>
<form method="post" name="aspec_frm" id="aspec_frm" onSubmit="document.getElementById('g_aspec').src = 'c_aspec.php?y='+document.getElementById('aspec_year').value+'&m='+document.getElementById('aspec_month').options[document.getElementById('aspec_month').selectedIndex].value+'&rnd='+Math.random(1000)+'&s='+getspec('spec_add'); return false;">

<div id="aspec" style="display:none;">
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
                if($lastgrname != $prof['groupname'])
                {
                  if($j) {
                    print('</td>');
                    if($j % 2 == 0) {
                      print('</tr><tr valign="top">');
                    }
                    print('<td style="width:33%;padding-bottom:20px">');
                  }

                  ?><div style="padding-bottom:2px">&nbsp;<strong><?=$prof['groupname']?></strong></div><?
                  $j++;
                  $lastgrname = $prof['groupname'];
                }
              ?>
                <table border="0" cellspacing="0" cellpadding="1">
                  <tr>
                    <td valign="top">
                      <input type="radio" name='spec_add' value="<?=$prof['id']?>" id="alb<?=$prof['id']?>">
                    </td>
                    <td width="100%">
                      <label for="lb<?=$prof['id']?>"><?=$prof['profname']?></label>
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

<select name="aspec_month" id="aspec_month">
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
<input type="text" name="aspec_year" id="aspec_year" size="4" maxlength="4" value="<?=date('Y')?>">





<input type="submit" value="јга!"><br>
</form>

<img src="c_aspec.php?m=<?=date('m')?>&y=<?=date('Y')?>&s=0" id="g_aspec">


</center>
