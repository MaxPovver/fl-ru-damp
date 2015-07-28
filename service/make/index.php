<?php
$g_page_id = "0|36";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
$rpath    = "../../";
include $_SERVER['DOCUMENT_ROOT']."/404.php";
exit;
$stretch_page = true;
$showMainDiv  = true;
$page_title = "Сервис «Сделаю» - фриланс, удаленная работа на FL.ru";
$header   = "$rpath/header.php";
$content  = 'content.php';
$js_file = array( 'banned.js', 'warning.js' );
$footer   = "$rpath/footer.html";
$template = 'template2.php';

$uid  = get_uid();
$frl_offers = new freelancer_offers();
$f_offers = $frl_offers->GetFreelancerOffers(false, 0, 3, false, true);
$hidden_block_button = true;
include( $rpath . $template );

?>
