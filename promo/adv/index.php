<?php

//@todo: Отключает лишнюю обертку в template3
$stretch_page = true;
$g_page_id = "0|35";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

$css_file = array('/css/block/b-menu/_promo/b-menu_promo.css');
$js_file[] = 'uSlider-1.1.js';
$content  = 'content.php';
include ($_SERVER['DOCUMENT_ROOT'] . '/template3.php');