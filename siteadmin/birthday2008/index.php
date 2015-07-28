<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
	$rpath = "../../";
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/birthday08.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/settings.php");
	session_start();
	get_uid();
	
	if (!hasPermissions('birthday'))
		{header ("Location: /404.php"); exit;}
	
$content = "../content.php";


//$inner_page = trim($_GET['page']);
//if (!$inner_page)
$inner_page = "index";

$inner_page = "inner_".$inner_page.".php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
