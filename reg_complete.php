<?

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

session_start();
$no_banner = false;
$stretch_page = true;
$footer_remind = true;
$header = "header.php";
$footer = "footer.html";
$content = "reg_complete_inner.php";
include ("template2.php");

?>
