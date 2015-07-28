<?
	if (!is_admin_sm())
		{exit;}

?>
<h1>Реклама</h1>

<?
	$action = trim($_GET['action']);
	if (!$action) $action = trim($_POST['action']);
	
	switch ($action){
		case "save":
		$text = stripslashes($_POST['text']);
		$fp = fopen("../../adv/content.php", "w");
		fwrite($fp, $text);
		fclose($fp);
		break;
	}
	
	include("../../fckeditor/fckeditor.php");

	$text .= implode("", file("../../adv/content.php"));

?>

<form action="/siteadmin/adv/" method="post" name="frm" id="frm">
<?php
		$oFCKeditor = new FCKeditor('text') ;
		$oFCKeditor->Value =  stripslashes($text);
		$oFCKeditor->Height = '550';
		$oFCKeditor->Create() ;
?>
<input type="hidden" name="action" value="save" class="btn">
<input type="submit" name="btn" value="Сохранить" class="btn">
</form>
