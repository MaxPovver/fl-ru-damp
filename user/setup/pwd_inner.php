<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
?>
<script>
checkLngPass = function(v) {
    if(v == undefined) var obj = $('pwd').value;
    else var obj = v;
    if(obj.length > 24) {
        $('error_box').style.display = 'block';
        $('error_text').innerHTML = 'Слишком длинный пароль (максимум — 24 символа)';
        return false;  
    } else {
        $('error_box').style.display = 'none';
        return true; 
    }
}
</script>
<form action="." method="post" onsubmit="return checkLngPass()">
<div class="b-layout b-layout_padtop_20">
<h2 class="b-layout__title">Изменить пароль</h2>
<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20">От 6 до 24 символов. Допустимы латинские буквы, цифры и следующие спецсимволы: !@#$%^&*()_+-=;,./?[]{}</div>
<? if ($info || $error) { ?>
   <div class="b-layout__txt">
	<? if ($info) { print(view_info($info)); } ?>
	<? if ($error) { print(view_error($error)); } ?>
   </div>
<? } ?>
<table cellspacing="0" cellpadding="0" class="b-layout__table">
<tr class="b-layout__tr">
	<td class="b-layout__td b-layout__td_padbot_20"><div class="b-layout__txt b-layout__txt_padtop_5">Старый пароль:&#160;</div></td>
	<td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_320">
	<div class="b-combo">
		<div class="b-combo__input">
			<input class="b-combo__input-text" type="password" name="oldpwd">
		</div>
	</div>
	
	<? if ($alert[1]) print(view_error($alert[1])) ?>
    </td>
</tr>
<tr class="b-layout__tr">
	<td class="b-layout__td b-layout__td_padbot_20"><div class="b-layout__txt b-layout__txt_padtop_5">Новый пароль:&#160;</div></td>
	<td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_320">
	<div class="b-combo">
		<div class="b-combo__input">
			<input class="b-combo__input-text" type="password" id="pwd" name="pwd" onkeyup="checkLngPass(this.value)" onblur="checkLngPass(this.value)" onfocus="checkLngPass(this.value)">
		</div>
	</div>
	<div class="errorBox" id="error_box" style="display:none"><img src="/images/ico_error.gif" alt="" width="22" height="18" /> &nbsp; <span id="error_text"></span></div>
	<? if ($alert[2]) print(view_error($alert[2])) ?>
    </td>
</tr>
<tr class="b-layout__tr">
	<td class="b-layout__td b-layout__td_padbot_20"><div class="b-layout__txt b-layout__txt_padtop_5">Повторите пароль:&#160;</div></td>
	<td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_320">
	<div class="b-combo">
		<div class="b-combo__input">
			<input class="b-combo__input-text" type="password" name="pwd2">
		</div>
	</div>
	<? if ($alert[3]) print(view_error($alert[3])) ?>
    </td>
</tr>
<tr class="b-layout__tr">
    <td class="b-layout__td b-layout__td_right" colspan="2">
       <button type="submit" name="btn" class="b-button b-button_flat b-button_flat_green">Изменить</button>
       <input type="hidden" name="action" value="pwd_change" />
    </td>
</tr>
</table>
</div>
</form>
