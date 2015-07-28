<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
  // Меню для template2.php
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
	$user = new employer();
	$user->GetUser($login);
?>
<div class="acc-h c">
    <a href="/users/<?= $user->login ?>/setup/foto/">
        <?=view_avatar($user->login, $user->photo, 0, 'acc-userpic')?>
    </a>
	<div class="acc-h-i">
 	<div>
 	  <?=($user->is_pro=='t' ? (is_emp($user->role) ? view_pro_emp() : view_pro2($user->is_pro_test=='t')) : '')?>
 	  <?=$session->view_online_status($user->login)?>
 	  <a href="/users/<?=$user->login?>/" class="<?=(is_emp($user->role) ? 'employer' : 'freelancer')?>-name"><?=$user->uname?> <?=$user->usurname?> [<?=$user->login?>]</a></div>
                <table cellspacing="2" cellpadding="2" class="config-link-table" >
		<tr>
			<td style="width:17px; height:17px; vertical-align:middle"><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td style="height:17px">
			
			<? if ($inner == "main_inner.php") {?>Основные настройки<? } else {?><a href="/users/<?=$user->login?>/setup/main/" class="blue">Основные настройки</a><? } ?>
			</td>
		</tr>
		<tr>
			<td style="width:17px; height:17px; vertical-align:middle"><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td style="height:17px">
			<? if ($inner == "foto_inner.php") {?>Моя фотография<? } else {?><a href="/users/<?=$user->login?>/setup/foto/" class="blue">Моя фотография</a><? } ?>
			</td>
		</tr>
		<tr>
			<td style="width:17px; height:17px; vertical-align:middle"><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td style="height:17px">
			<? if ($inner == "mailer_inner.php") {?>Уведомления/Рассылка<? } else {?><a href="/users/<?=$user->login?>/setup/mailer/" class="blue">Уведомления/Рассылка</a><? } ?>
			</td>
		</tr>
		<tr>
			<td style="width:17px; height:17px; vertical-align:middle"><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td style="height:17px">
			<? if ($inner == "list_inner.php") {?>Настройка закладок<? } else {?><a href="/users/<?=$user->login?>/setup/tabssetup/" class="blue">Настройка закладок</a><? } ?>
			</td>
		</tr>
		<tr>
			<td style="width:17px; height:17px; vertical-align:middle"><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td style="height:17px">
			<? if ($inner == "safety_inner.php") {?>Безопасность<? } else {?><a href="/users/<?=$user->login?>/setup/safety/" class="blue">Безопасность</a><? } ?>
			</td>
		</tr>
		<tr>
			<td style="width:17px; height:17px; vertical-align:middle"><span class="del-icon"></span></td>
			<td style="height:17px">
					<a class="del-user-lnk blue" href="/users/<?=$user->login?>/setup/delete/">Удалить аккаунт</a>
			</td>
		</tr>
		</table>
		<!--<ul>
			<li><? if ($inner == "main_inner.php")   { ?>Основные настройки<? } else { ?><a href="/users/<?=$user->login?>/setup/main/">Основные настройки</a><? } ?></li>
			<li><? if ($inner == "foto_inner.php")   { ?>Моя фотография<? } else { ?><a href="/users/<?=$user->login?>/setup/foto/">Моя фотография</a><? } ?></li>
			<li><? if ($inner == "mailer_inner.php") { ?>Уведомления/Рассылка<? } else { ?><a href="/users/<?=$user->login?>/setup/mailer/">Уведомления/Рассылка</a><? } ?></li>
			<li><? if ($inner == "list_inner.php")   { ?>Настройки закладок<? } else { ?><a href="/users/<?=$user->login?>/setup/tabssetup/">Настройки закладок</a><? } ?></li>
			<li><? if ($inner == "safety_inner.php") { ?>Безопасность<? } else { ?><a href="/users/<?=$user->login?>/setup/safety/">Безопасность</a><? } ?></li>
		</ul>-->
	</div>
</div>
 		