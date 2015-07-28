<?
//$g_page_id = "0|4";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
	session_start();
	get_uid();
	
	if (!$_SESSION['uid']) { include("../fbd.php"); exit;}
	
$content = "up_cnt.php";

$header = "../header.php";
$footer = "../footer.html";

include ("../template.php");

?>