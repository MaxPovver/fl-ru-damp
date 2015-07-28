<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } 
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/settings.php");
  if (!hasPermissions('birthday')) { exit; }
	$users = $birthday->GetAll();
  $i = 0;
?>
<style>
  .rbx {background:#d9efff;padding:15px;width:337px}
</style>
<strong>День рождения <?=$year?> (участники)</strong><br><br>
<br/>
<? if ($error) print(view_error($error).'<br/>'); ?>
<? if (!$birthday->isClosed) { ?>
		Статус регистрации:&nbsp;&nbsp;<B>Открыта</B> &nbsp;&nbsp;&nbsp;&raquo; <A href="?year=<?=$year?>&action=close" class="blue">Закрыть</A>
<? } else	{ ?>
		Статус регистрации:&nbsp;&nbsp;<B>Закрыта</B> &nbsp;&nbsp;&nbsp;&raquo; <A href="?year=<?=$year?>&action=open" class="blue">Открыть</A>
<? } ?>
<br/><br/><br/>

<div class="rbx">
  <form action="." method="post">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
  		<tr>
  		  <td width="60">Логин:</td>
  		  <td><input type="text" name="login" value="<?=($login ? $login : '')?>"/></td>
  		</tr>
  		<tr>
  		  <td>Имя:</td>
  		  <td><input type="text" name="name" value="<?=($user['uname'] ? $user['uname'] : '')?>"/></td>
  		</tr>
  		<tr>
  		  <td>Фамилия:</td>
  		  <td><input type="text" name="surname" value="<?=($user['usurname'] ? $user['usurname'] : '')?>"/></td>
  		</tr>
		</table>
    <br/><br/>
    <input type="radio" name="type" id="rtype1" value="1"<?=($user['utype']==1 ? ' checked' : '')?>/><label for="rtype1">Фрилансер</label>&nbsp;
    <input type="radio" name="type" id="rtype2" value="2"<?=($user['utype']==2 ? ' checked' : '')?>/><label for="rtype2">Работодатель</label>&nbsp;
    <input type="radio" name="type" id="rtype3" value="3"<?=($user['utype']==3 ? ' checked' : '')?>/><label for="rtype3">Пресса</label>
    <br/><br/>
    <input type="hidden" name="action" value="add"/>
    <input type="submit" value="Добавить пользователя"/>
  </form>
</div>
<br/><br/>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
  <? if ($users) foreach($users as $user){ $i++; ?>
    <tr class="qpr">
    	<td>
    		<table width="100%" cellspacing="0" cellpadding="0" border="0">
    		<tr valign="top" class="n_qpr">
    			<td>
    			  <?=$i?>. <a href="/users/<?=$user['login']?>"><?=$user['uname']." ".$user['usurname']." [".$user['login']."]"?></a> 
    			  <a href="mailto:<?=$user['email']?>"><?=$user['email']?></a> 
    		    <?=($user['utype'] == 1 ? 'Фрилансер' : ($user['utype'] == 2 ? 'Работодатель' : 'Пресса'))?>
    			</td>
    			<td align="right">
    			  <? if($user['is_accepted']!='t') { ?>
              [<a href="?year=<?=$year?>&action=accept&id=<?=$user['id']?>"> Оплатил </a>]
    			  <? } else  { ?>
              [<a href="?year=<?=$year?>&action=unaccept&id=<?=$user['id']?>" style="color:red"> Снять оплату </a>]
    			  <? } ?>
            &nbsp;&nbsp;&nbsp;[<a href="?year=<?=$year?>&action=del&id=<?=$user['id']?>" onclick="return warning()"> Удалить </a>]
    			</td>
    		</tr>
    		</table>
    	</td>
    </tr>
  <? } ?>
</table>

