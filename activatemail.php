<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/activate_mail.php");
session_start();
get_uid(false);
$no_personal = 0;
$no_banner = 1;
$stretch_page = 1;

$code = trim($_GET['code']);

if ($code) {
	$act = new activate_mail;
	$pass = "";
	$activated = $act->Activate($code);
}

$header = "header.php";
$footer = "footer.html";
$content = "activatemail_inner.php";
include("template.php");
?>
