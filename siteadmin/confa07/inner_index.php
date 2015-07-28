<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
	if (!is_admin_sm())
		{exit;}
	$action = trim($_GET['action']);
	
	switch ($action){
		case "delete":
			$id = trim($_GET['id']);
			if ($id) $error = confa07::Delete($id);
		break;
	}
	
	$users = confa07::GetAll();
?>
<strong>Пользователи</strong><br><br>

<br><br>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
<?
$i = 0;

 if ($users) foreach($users as $ikey=>$user){ $i++; ?>
<tr class="qpr">
	<td>
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr valign="top" class="n_qpr">
			<td>
			<?=$i?> <a href="/users/<?=$user['login']?>"><?=". ".$user['name']." ".$user['surname']." [".$user['login']."]"?></a> 
			<a href="mailto:<?=$user['email']?>"><?=$user['email']?></a> 
			<? if ($user['type'] == 1) print("Фрилансер"); if ($user['type'] == 2) print("Работодатель"); if ($user['type'] == 3) print("Пресса") ?>
			<a href="/siteadmin/confa07/?action=delete&amp;id=<?=$user['id']?>" onclick="return warning(20);">удалить</a>
			<br>
			 Что написал: <?=($user['message']  ? $user['message'] : "ничего не написал")?><br><br>
			
			</td>
		</tr>
		</table>
	</td>
</tr>
<? } ?>
</table>

