<?
$g_page_id = "0|59";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();

$offer_id = __paramInit('int', 'offer_id', null, 0);

// страницу могут смотреть только зарегистрированые фрилансеры
if (!get_uid(0) || is_emp() || !$offer_id) {
    include $_SERVER['DOCUMENT_ROOT']."/403.php";
    exit;
}

$offer_url = "/sdelau/#o_" . $offer_id;

$stretch_page = true;
$header  = "../../header.php";
$footer  = "../../footer.html";
$content = "tpl.offer_published.php";

include ("../../template2.php");
?>