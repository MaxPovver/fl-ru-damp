<h1><?=($opinfo['op_code'] == 23)?"Перевод":"Подарок"?></h1>
<table width="100%" border="0" cellspacing="0" cellpadding="19">
<tr valign="top">
	<td height="400" valign="top" bgcolor="#FFFFFF" class="box2">
		<h2>Вам <?=($opinfo['op_code'] == 23)?"перевод":"подарок от"?></h2>
		<table cellspacing="0" cellpadding="0" border="0">
		<tr valign="top">
			<td width="70" align="center"><a href="/users/<?=$user->login?>/" class="<?=$cnt_role?>name11"><?=view_avatar($user->login, $user->photo) ?></a></td>
			<td class="<?=$cnt_role?>name11">
			<?/* if ($is_pro && $cnt_role =="emp") { ?><?=view_pro_emp()?><? } ?>
			<a href="/users/<?=$user->login?>" class="<?=$cnt_role?>name11"><?=($user->uname." ".$user->usurname)?></a> [<a href="/users/<?=$user->login?>" class="<?=$cnt_role?>name11"><?=$user->login?></a>]<br> */?>
			<?=view_user($user);?><br/>
			<a href="/contacts/?from=<?=$user->login?>#form" class="blue">Написать сообщение</a><br>
			</td>
		</tr>
		</table>
		<h3><img src="/images/present.gif" alt="" width="32" height="32" border="0" style="margin-right: 10px;"><?=$pr_txt?>
  <? if ($opinfo['op_code'] == 23) { ?>
      на сумму <?= floatval($opinfo['ammount']) ?> FM
  <? } ?>
  </h3>
		<?=($opinfo['op_code'] == 23 || $opinfo['op_code'] == 90 || in_array($opinfo['op_code'], array(95,96,97,99,100,101)))?"":$info?>
	<table width="90%" border="0" cellspacing="0" cellpadding="0" style="padding-top: 30px;">		
	<? if ($opinfo['comments']){ ?>
	<tr valign="top">			
	<td width="22"><img src="/images/ico_dq_l.gif" alt="" width="19" height="21" border="0"></td>
	<td style="color: #666; font-size: 20px;"><?=reformat($opinfo['comments'],51)?>
	<img src="/images/ico_dq_r.gif" alt="" width="19" height="19" border="0" align="top">
	</td></tr>
	<? } else print "<tr><td>Сообщения нет</td></tr>"?>
	</table>
	</td>
</tr>
</table>