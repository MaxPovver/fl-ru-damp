<?
  exit; // пользуемся /siteadmin/admin/
	if (!is_admin_sm())
		{exit;}
	$action = trim($_GET['action']);
	if (!$action) $action = trim($_POST['action']);
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/spam.php");
	$sm = new spam();
	
	switch ($action){
		case "post_msg":
		$msg = change_q($_POST['msg']);
		$name = change_q($_POST['name']);
		$role = $_POST['role'];
		switch ($role) {
			case "npro": $irole = 0;break;
			case "pro": $irole = 1;break;
			case "emp": $irole = 2;break;
		}
		$error = $sm->Update($msg, $name, $irole);
		if (!$error) unset($msg);
		break;
	}
	$val = $sm->Get();
?>

<strong>Спам</strong><br><br>
Теги %name% и %surname% в теле письма будут заменены на имя и фамилию юзера.
	<? if ($error) print(view_error($error));?>
<form action="/siteadmin/spam/" method="post" onSubmit="this.btn.value='Подождите'; this.btn.disabled=true;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="19" rowspan="5">&nbsp;</td>
		<td height="30" valign="bottom">
			Новое сообщение для всех НЕ ПРО фрилансеров:
		</td>
		<td width="19" rowspan="4">&nbsp;</td>
	</tr>
	<tr>
		<td>
			Заголовок: <input type="text" name="name" value="<?=$val['subj']?>">
		</td>
	</tr>
	<tr>
		<td height="115">
			<textarea cols="10" rows="7" name="msg" class="wdh100"><?=input_ref($val['msg'])?></textarea>
			<? if ($alert[2]) print(view_error($alert[2])) ?>
		</td>
	</tr>
	<tr>
		<td align="right"><input type="hidden" name="MAX_FILE_SIZE" value="100000">
			<input type="hidden" name="action" value="post_msg"><input type="submit" name="btn" class="btn" value="Сохранить">
			<input type="hidden" name="role" value="npro">
		</td>
	</tr>
	<tr><td colspan="3">&nbsp;</td></tr>
</table>
</form>
<?
	$val = $sm->Get(1);
?>
<form action="/siteadmin/spam/" method="post" onSubmit="this.btn.value='Подождите'; this.btn.disabled=true;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="19" rowspan="5">&nbsp;</td>
		<td height="30" valign="bottom">
			Новое сообщение для всех ПРО фрилансеров:
		</td>
		<td width="19" rowspan="4">&nbsp;</td>
	</tr>
	<tr>
		<td>
			Заголовок: <input type="text" name="name" value="<?=$val['subj']?>">
		</td>
	</tr>
	<tr>
		<td height="115">
			<textarea cols="10" rows="7" name="msg" class="wdh100"><?=input_ref($val['msg'])?></textarea>
			<? if ($alert[2]) print(view_error($alert[2])) ?>
		</td>
	</tr>
	<tr>
		<td align="right"><input type="hidden" name="MAX_FILE_SIZE" value="100000">
			<input type="hidden" name="action" value="post_msg"><input type="submit" name="btn" class="btn" value="Сохранить">
			<input type="hidden" name="role" value="pro">
		</td>
	</tr>
	<tr><td colspan="3">&nbsp;</td></tr>
</table>
</form>
<?
	$val = $sm->Get(2);
?>
<form action="/siteadmin/spam/" method="post" onSubmit="this.btn.value='Подождите'; this.btn.disabled=true;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="19" rowspan="5">&nbsp;</td>
		<td height="30" valign="bottom">
			Новое сообщение для всех работодателей:
		</td>
		<td width="19" rowspan="4">&nbsp;</td>
	</tr>
	<tr>
		<td>
			Заголовок: <input type="text" name="name" value="<?=$val['subj']?>">
		</td>
	</tr>
	<tr>
		<td height="115">
			<textarea cols="10" rows="7" name="msg" class="wdh100"><?=input_ref($val['msg'])?></textarea>
			<? if ($alert[2]) print(view_error($alert[2])) ?>
		</td>
	</tr>
	<tr>
		<td align="right"><input type="hidden" name="MAX_FILE_SIZE" value="100000">
			<input type="hidden" name="action" value="post_msg"><input type="submit" name="btn" class="btn" value="Сохранить">
			<input type="hidden" name="role" value="emp">
		</td>
	</tr>
	<tr><td colspan="3">&nbsp;</td></tr>
</table>
</form>
