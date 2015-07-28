<?
$g_page_id = "0|9";
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
	session_start();
	get_uid();
	
	
	//if (!$_SESSION['prj_id'] || !$_SESSION['prjtype']) { include("../fbd.php"); exit;}
		
	/*if ($_SESSION['prjtype'] == 2) $sum = "10";
	if ($_SESSION['prjtype'] == 1) $sum = "50";
	if ($_SESSION['prjtype'] == 3) $sum = "10";
	$smail = new smail();
	$smail->PayedProject($sum, $_SESSION['prj_id'], $eprj);*/
	
$content = "project_cnt.php";
$css_file = 'fl2.css';
$header = "../header.php";
$footer = "../footer.html";

include ("../template.php");

?>