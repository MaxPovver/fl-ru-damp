<?php
$grey_service = 1;
$g_page_id = "0|9";
$stretch_page = true;
$showMainDiv  = true;
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';

session_start();
get_uid();

// настраиваем контент страницы
if (!get_uid(0)) {
    $guest = true;
    $forFrl = true;
    $forEmp = true;
} elseif (is_emp()) {
    $guest = false;
    $forFrl = false;
    $forEmp = true;
} else {
    $guest = false;
    $forFrl = true;
    $forEmp = false;
}

$page_title = "Услуги - фриланс, удаленная работа на FL.ru";

$header   = '../header.php';
$footer   = '../footer.php';
$css_file = '/css/block/b-promo/__servis/b-promo__servis.css';
$content  = 'content_new.php';

include '../template2.php';