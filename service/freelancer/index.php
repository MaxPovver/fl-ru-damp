<?php
$grey_service = 1;
$g_page_id = "0|9";
$stretch_page = true;
$showMainDiv  = true;
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';

session_start();
get_uid();

$guest = false;
$forFrl = true;
$forEmp = false;

$page_title = "Услуги - фриланс, удаленная работа на FL.ru";

$header   = '../../header.php';
$footer   = '../../footer.php';
$content  = '../content_new.php';

include '../../template2.php';