<?
$no_banner = 1;
	$rpath = "../../";
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
	session_start();
	get_uid();
	
	if (!(hasPermissions('adm') && (hasPermissions('stats') || hasPermissions('tmppayments')) ))
		{header ("Location: /404.php"); exit;}
	
$content = "../content.php";


$inner_page = "charts3";

$inner_page = "inner_".$inner_page.".php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
