<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
	if (!is_admin_sm())
		{exit;}
	$action = trim($_GET['action']);
	
	switch ($action){
		case "delete":
			$id = trim($_GET['id']);
			if ($id) $error = hoster::Delete($id);
		break;
	}
	
	$users = hoster::GetAll();
?>
<strong>Пользователи</strong><br><br>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
<?
$i = 0;
 if ($users) foreach($users as $ikey=>$user){ $i++; ?>
<tr class="qpr">
	<td>
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr valign="top" class="n_qpr">
			<td>
			<?=($i.". ".$user['name']." ".$user['surname'])?> <a href="mailto:<?=$user['email']?>"><?=$user['email']?></a>
			<a href="/siteadmin/hoster/?action=delete&amp;id=<?=$user['id']?>" onclick="return warning(20);">удалить</a><br><br>
			</td>
		</tr>
		</table>
	</td>
</tr>
<? } ?>
</table>

