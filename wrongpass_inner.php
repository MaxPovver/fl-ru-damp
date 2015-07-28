<table width="100%" border="0" cellspacing="0" cellpadding="15">
<tr valign="top">
	<td height="400" valign="top" bgcolor="#FFFFFF" class="box2">
	<h1>Неправильный логин или пароль</h1>
			<? 
				if ($action == "send" && !$error) { ?>
			<table cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td width="25" height="20"><img src="/images/ico_ok.gif" alt="" width="19" height="18" border="0"></td>
				<td>На Ваш электронный ящик были высланы логин и пароль</td>
			</tr>
			</table>
			<? }  else { ?>
			Вы ввели неправильный логин или пароль.<br><br>
			Если вы забыли логин или пароль, введите Ваш электронный адрес, указанный при регистрации, в поле ниже, и логин с паролем будет выслан на него.
			<form action="/wrongpass.php" method="post">
			<input type="hidden" name="action" value="send">
			<table cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td width="115">Электронная почта:</td>
				<td><input type="text" name="email" value="<?=$s_email?>" size="33"> &nbsp;<input type="submit" name="btn" class="btn" value="Выслать"></td>
			</tr>
			<? if ($error) { ?>
			<tr>
				<td>&nbsp;</td>
				<td><?=view_error($error)?></td>
			</tr>
			<? } ?>
			</table>
			</form>
			<? } ?>
	</td>
</tr>
</table>
