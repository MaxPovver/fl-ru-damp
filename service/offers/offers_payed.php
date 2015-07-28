<?
$g_page_id = "0|63";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();

$answers = __paramInit('int', 'answers', null, 0);
$cost = __paramInit('int', 'cost', null, 0);

// эта страница только для фрилансеров
if (!get_uid(0) || is_emp() || !$answers || !$cost) {
    include $_SERVER['DOCUMENT_ROOT']."/403.php";
    exit;
}


$stretch_page = true;
$header  = "../../header.php";
$footer  = "../../footer.html";
$content = "tpl.offers_payed.php";

include ("../../template2.php");
?>