<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
	session_start();
	$i = trim($_GET['i']);
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<title>Free-lance</title>
</head>
<script type="text/javascript">
<!--
	window.returnValue = false;
	
<? 
	$num = intval($_GET['num']);
	if ($num){ ?>
var	num = <?=$num?>
<?	} else { ?>
var num = window.dialogArguments;
<? } ?>
	
	function subm(i){
		window.returnValue = i;
		window.close(i);
	}
	
	switch (num){
		case 1: tx="Вы действительно хотите удалить сообщение?"; break;
		case 2: tx="Вы действительно хотите удалить проект/предложение?"; break;
		case 3: tx="Вы действительно хотите удалить контакты?"; break;
		case 4: tx="Вы действительно хотите удалить резюме?"; break;
		case 5: tx="Вы действительно хотите удалить работу?"; break;
		case 6: tx="Вы действительно хотите удалить фотографию?"; break;
		case 7: tx="Поле заполнено некорректно."; break;
		case 8: tx="Вы действительно хотите удалить эту позицию?"; break;
		case 9: tx="Удалится только папка. Контакты переместятся в&nbsp;папку &laquo;Все&raquo;."; break;
		case 10: tx="Вы действительно хотите удалить файл?"; break;
		case 11: tx="Вы действительно хотите удалить логотип?"; break;
		case 12: tx="Вы действительно хотите удалить эту группы и всех её членов?"; break
		case 13: tx="Вы действительно хотите удалить этого человека?"; break
		case 14: tx="Вы действительно хотите восстановить сообщение?"; break
		default: tx="Вы уверены?";
	}
	
//-->
</script>

<style type="text/css">
.s {
	font-family: Tahoma;
	font-size: 11px;
}
#btn {
	width: 80px;
}
</style>

<body text="#666666">
<table cellspacing="19" cellpadding="0" border="0" height="100%">
<tr>
	<td colspan="2">
	<table cellspacing="0" cellpadding="4" border="0" class="s">
	<tr>
		<td><img src="images/ico_error.gif" alt="" width="22" height="18" border="0"></td>
		<td><script type="text/javascript">
			<!--
		 		document.write(tx);
		 	//-->
			</script>
		</td>
	</tr>
	</table>
	</td>
</tr>
<tr>
	<td valign="bottom"><input type="submit" id="btn" value="Ok" class="s" onClick="subm(true);"></td>
	<td valign="bottom"><input type="submit" id="btn" value="Отмена" class="s" onClick="subm(false);"></td>
</tr>
</table>

</body>
</html>
