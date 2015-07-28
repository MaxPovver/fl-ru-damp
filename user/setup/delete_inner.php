<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
?>
<form action="." method="post">
<table style="width:100%; border:0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="width:19px" rowspan="6">&nbsp;</td>
		<td style="vertical-align:bottom; height:30px" class="red">
			<strong>Удалить аккаунт</strong>
		</td>
		<td style="width:19px" rowspan="6">&nbsp;</td>
	</tr>
	<tr>
		<td style="height:30px">Вы действительно хотите удалить аккаунт?</td>
	</tr>
	<tr>
		<td>Введите пароль</td>
	</tr>
	<tr>
		<td style="vertical-align:top; height:40px">
			<input type="password" name="passwd" class="wdh100" />
			<? if ($error) print(view_error($error)) ?>
		</td>
	</tr>
	<tr>
		<td style="text-align:right">
			<input type="hidden" name="action" value="delete" /><input type="submit" name="btn" class="btn" value="Удалить" />
		</td>
	</tr>
	<tr><td colspan="3">&nbsp;</td></tr>
</table>
</form>
