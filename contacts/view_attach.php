<?

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
get_uid(false);

if (eregi("^[A-Za-z0-9_\-]{1,32}$", $_GET['user']) && eregi("^[A-Za-z0-9_.]{1,32}$", $_GET['attach']))
{
	$str = viewattach($_GET['user'], $_GET['attach'], "contacts", $file, -1, -1, 5242880, 0);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
    $stc = new static_compress;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Удаленная работа (фри-ланс) на Free-lance.ru</title>
	<meta name="description" lang="ru" content="Free-lance.ru это профессиональный ресурс, предназначенный для поиска работы или исполнителя (фрилансера) на удаленную работу (фри-ланс).">
	<meta name="keywords" lang="ru" content="работа, ищу работу, поиск работы, удаленная работа, фри-ланс">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
	<?php $stc->Add("/scripts/swfobject.js"); ?>
    <?php $stc->Send(); ?>
</head>

<body bgcolor="#FFFFFF" text="#000000">

<table height="100%" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr valign="top">
	<td align="center"><?=$str?></td>
</tr>
</table>

<?

}
else
{
{
	header ("Location: /403.php"); exit;}
}

?>
