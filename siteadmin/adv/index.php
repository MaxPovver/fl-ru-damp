<?
header ("Location: /404.php"); exit;
$no_banner = 1;
	$rpath = "../../";
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
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