<?
header("HTTP/1.0 403 Forbidden");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
get_uid(false);
//$no_personal = 1;
//$no_banner = 1;

// добавляем возможность редактировать вопросы в Help-попапе для страницы /registration/, на которую нельзя попасть зарегистрированному
if ($g_page_id !== "0|42" || !(hasPermissions("administrator") || hasPermissions("help"))) {
    $g_page_id = "0|26";
}

$header = ABS_PATH."/header.php";
$footer = ABS_PATH."/footer.html";
$content = ABS_PATH."/403_inner.php";
$page_title = "403 Forbidden";
$error403_page = 1;
include("template3.php");
?>