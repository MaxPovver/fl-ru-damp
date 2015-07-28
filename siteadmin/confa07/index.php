<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
	$rpath = "../../";
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/confa07.php");
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