<?
header ("Location: /404.php"); exit; // пользуемся /siteadmin/admin/
$no_banner = 1;
	$rpath = "../../";
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
	
	session_start();
	get_uid();
	
	if (!is_admin_sm())
		{header ("Location: /404.php"); exit;}
	
	
$content = "../content.php";


$inner_page = "inner_index.php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>