<?
$g_page_id = "0|60";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
session_start();

$prj_id = __paramInit('int', 'prj_id', null, 0);
// страницу могут смотреть только зарегистрированые работодатели
if (!get_uid(0) || !is_emp() || !$prj_id) {
    include $_SERVER['DOCUMENT_ROOT']."/403.php";
    exit;
}

$prj_url = getFriendlyURL('project', $prj_id);
$new_sbr_url = "/norisk2/?site=create&pid=" . $prj_id;

header('Location: '.$prj_url);
exit;

$stretch_page = true;
$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.project_published.php";

include ("../template2.php");
?>