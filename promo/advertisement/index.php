<?php

//@todo: Отключает лишнюю обертку в template3
$stretch_page = true;
$hide_footer = true;
$g_page_id = "0|35";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

$css_file = array(
    '/css/promo/media/css/reset.css',
    //'/css/promo/media/css/stylesheet.css',
    '/css/promo/media/css/styles.css'
);

$full_content  = 'content.php';
include ($_SERVER['DOCUMENT_ROOT'] . '/template3.php');