<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
	$user = new employer();
	$user->GetUser($_SESSION['login']);
?>
<table style="width:100%; border:0" cellspacing="0" cellpadding="0">
<tr style="text-align:left; vertical-align:top">
	<td rowspan="2"  style="text-align:center; width:110px"><?=view_avatar($user->login, $user->photo, 0)?></td>
	<td class="empname"><?=($user->is_pro == 't')?view_pro_emp(1):''?><?=$session->view_online_status($user->login)?><?=$user->uname?> <?=$user->usurname?> [<?=$user->login?>]</td>
	<!-- <td align="right"><a href="/users/<?=$user->login?>/setup/delete/"><img src="/images/ico_close.gif" alt="" width="9" height="9" border="0"></a>&nbsp;<a href="/users/<?=$user->login?>/setup/delete/">Удалить аккаунт</a></td> -->
</tr>
<tr style="text-align:left">
	<td colspan="2" style="vertical-align:top">
		<table cellspacing="2" cellpadding="2"  style="border:0" class="config-link-table" >
		<tr>
			<td><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td>
			
			<? if ($inner == "../setup/main_inner.php") {?> Основные настройки<? } else {?><a href="/users/<?=$user->login?>/setup/main/" class="blue">Основные настройки</a><? } ?>
			</td>
		</tr>
		<tr>
			<td><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td>
			<? if ($inner == "../setup/foto_inner.php") {?> Моя фотография<? } else {?><a href="/users/<?=$user->login?>/setup/foto/" class="blue">Моя фотография</a><? } ?>
			</td>
		</tr>
		<tr>
			<td><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td>
			<? if ($inner == "mailer_inner.php") {?> Уведомления/Рассылка<? } else {?><a href="/users/<?=$user->login?>/setup/mailer/" class="blue">Уведомления/Рассылка</a><? } ?>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>
