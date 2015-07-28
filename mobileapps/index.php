<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
$g_page_id = "0|102";
$rpath="../";
$stretch_page = true;
$showMainDiv  = true;
$no_banner = true;

header_location_exit('/404.php');  //#0026472

$content = "content.php";
$header = "../header.php";
$footer = "../footer.html";
include ("../template3.php");

?>
