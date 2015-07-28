<?
$g_page_id = "0|9";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
	session_start();
	get_uid();
	
	if (substr($_SESSION['role'], 0, 1)  !== '0') { include("../fbd.php"); exit;}
	
$content = "choose_cnt.php";

$header = "../header.php";
$footer = "../footer.html";

include ("../template.php");

?>