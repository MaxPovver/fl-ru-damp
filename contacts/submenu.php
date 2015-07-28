<td colspan="2" style="padding-bottom: 12px;">
<script type="text/javascript">
<!--
	function CheckSelected(){
		chc = false;
		mnts = frm.elements.length;
		for (i = 0; i < mnts; i++){
			if (frm.elements[i].name == "selected[]" && frm.elements[i].checked == 1)
			chc = true;
		}
		if (!chc) document.getElementById('submenu').innerHTML = "<?=ref_scr(view_error("Необходимо выбрать хотя бы один контакт"))?>";
		return (chc);
	}
//-->
</script>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr class="n_qpr">
	<td height="18" align="left" valign="bottom"><img src="/images/arrow_rl.gif" alt="" width="12" height="14" hspace="6" border="0"></td>
	<td valign="top">
		Действия с отмеченными: 
		 <a href="#" class="blue" onClick="if (CheckSelected() && warning(3)) {frm.action.value='delete'; frm.submit();} else return(false);">Удалить</a> |
		 <? if ($page != "team") { ?>
		 <a href="#" class="blue" onClick="if (CheckSelected()) {frm.action.value='team'; frm.submit();}"><? if ((substr($_SESSION['role'], 0, 1)  == '0')) { ?>Рекомендовать фрилансера<? } else { ?>Добавить в команду<? } ?></a> | <? } ?>
		 <? if ($page == "ignor") { ?>
		 <a href="#" class="blue"onClick="if (CheckSelected()) {frm.action.value='unignor'; frm.submit();}">Снять Игнорирование</a>
		 <? } else {?>
		 <a href="#" class="blue" onClick="if (CheckSelected()) {frm.action.value='ignor'; frm.submit();}">Игнорировать</a>
		<? } ?>
			
	</td>
</tr>
<tr class="n_qpr"><td colspan="2" align="center" id="submenu"></td></tr>
</table>
</td>
