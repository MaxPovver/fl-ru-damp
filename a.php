<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
if(!($href = stripslashes(trim($_GET['href'])))) {
    //header('Location: /404.php');
    //exit;
}
$stretch_page = 1;
preg_match('~^([a-z]{3,5}:/{2,3})?(.*)$~', $href, $parts);
list($href, $scheme, $url) = $parts;
if(!$scheme)
    $scheme = 'http://';
//$no_banner = 1;
$g_page_id = "0|26";
$header = "header.php";
$footer = "footer.html";
$content = "a_inner.php";
$uid = get_uid( false );
include ("template2.php");
?>
