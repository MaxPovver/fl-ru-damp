<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
// идет в релиз из-за связей, поэтому пока 404
header_location_exit('/404.php');

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");

$rpath    = "../../";
$stretch_page       = true;
$showMainDiv        = true;
$page_title = "Выделение ответа в проекте - фриланс, удаленная работа на FL.ru";
$header   = "$rpath/header.php";
$content  = 'content.php';
$footer   = "$rpath/footer.html";
$template = 'template2.php';

$uid  = get_uid();

include( $rpath . $template );

?>
