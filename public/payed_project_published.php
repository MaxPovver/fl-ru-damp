<?
$g_page_id = "0|61";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
session_start();

$prj_id = __paramInit('int', 'prj_id', null, 0);
$top = __paramInit('int', 'top', null, 0);
$top_days = __paramInit('int', 'top_days', null, 0);
$color = __paramInit('int', 'color', null, 0);
$bold = __paramInit('int', 'bold', null, 0);
$logo = __paramInit('int', 'logo', null, 0);
$inoffice = __paramInit('float', 'inoffice', null, 0);

// страницу могут смотреть только зарегистрированые работодатели
if (!get_uid(0) || !is_emp() || !$prj_id) {
    include $_SERVER['DOCUMENT_ROOT']."/403.php";
    exit;
}

$prj_url = getFriendlyURL('project', $prj_id);
$new_sbr_url = "/norisk2/?site=create&pid=" . $prj_id;

$stretch_page = true;
$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.payed_project_published.php";

include ("../template2.php");
?>