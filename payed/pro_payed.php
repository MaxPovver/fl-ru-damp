<?
$g_page_id = "0|50";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();


$months = __paramInit('int', 'months', null, 0);
$weeks = __paramInit('int', 'weeks', null, 0);
$cost = __paramInit('int', 'cost', null, 0);

if (!get_uid(0) || is_emp() || !($months || $weeks)) {
    include $_SERVER['DOCUMENT_ROOT']."/403.php";
    exit;
}

if ($months) {
    $period = $months . " " . ending($months, "мес€ц", "мес€ца", "мес€цев");
} elseif ($weeks) {
    $period = $weeks . " " . ending($weeks, "неделю", "недели", "недель");
}

$stretch_page = true;
$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.pro_payed.php";

include ("../template2.php");
?>