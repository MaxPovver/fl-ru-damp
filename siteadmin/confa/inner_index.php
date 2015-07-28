<?	
	if (!is_admin_sm())
		{exit;}
	$action = trim($_GET['action']);
	
	switch ($action){
		case "delete":
			$id = trim($_GET['id']);
			if ($id) $error = confa::Delete($id);
		break;
		case "off":
			confa::Swch(0);
		break;
		case "on":
			confa::Swch(1);
		break;
	}
	
	$users = confa::GetAll();
?>
<strong>Пользователи</strong><br><br>
<? $check = confa::Check(); ?>
<a href=".?action=<?=($check)?"off":"on"?>" class="blue">В<?=($check)?"ы":""?>ключить регистрацию</a>
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
			<?=($i.". ".$user['name']." ".$user['surname'])?> <a href="mailto:<?=$user['email']?>"><?=$user['email']?></a>
			<? if ($user['type'] == 1) print("Фрилансер"); if ($user['type'] == 2) print("Работодатель"); if ($user['type'] == 3) print("Пресса") ?>
			<a href="/siteadmin/confa/?action=delete&amp;id=<?=$user['id']?>" onclick="return warning(20);">удалить</a><br><br>
			</td>
		</tr>
		</table>
	</td>
</tr>
<? } ?>
</table>

