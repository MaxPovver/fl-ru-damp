<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banners.php");
	session_start();
	get_uid();
 if (!$_SESSION['bannerid']) header("Location: /404.php");
	
$id = trim($_GET['id']);
$type = trim($_GET['type']);
	
	if (substr($role, 3) == '1') 
		$user_id = -1;
	else
		$user_id = $_SESSION['uid'];
	
	
	switch ($type){
		case "bn" : $content = "../siteadmin/banners/stat_ban_cnt.php"; break;
		default: $content = "../siteadmin/banners/stat_cl_cnt.php"; break;
		}
$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
