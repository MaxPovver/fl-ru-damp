<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
?>
<table style="width:100%; border:0" cellspacing="0" cellpadding="0">
<tr>
	<td style="width:19px" rowspan="2">&nbsp;</td>
	<td style="height:40px" colspan="2"><strong>Основные настройки</strong>
	</td>
	<td style="width:19px" rowspan="2">&nbsp;</td>
<tr>
	<td style="height:100px"><?php if ($error) print view_error($error); else { ?>Чтобы
	 изменить e-mail, пройдите по ссылке в письме, отправленном по старому e-mail.<br />
Если вы не можете этого сделать - обратитесь в <a href="https://feedback.fl.ru/">Службу поддержки</a><?php  } ?></td>
</tr>
</table>
