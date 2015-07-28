<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

$redirect = is_emp() ? '/payed-emp/' : '/payed/';
header_location_exit($redirect);

/*
session_start();
$uid = get_uid(false);
$stretch_page = true;

$no_banner = 1;
if (!$fpath) $fpath = "";
$header = $fpath."header.php";
$footer = $fpath."footer.html";
$css_file = 'payed.css';
$js_file = array( 'payed.js' );
if (!$uid) {
    header_location_exit('/fbd.php');
} elseif (is_emp()) {
    $content = $fpath."proonly_inner_emp.php";
    $js_file = array( 'payed.js' );
} else {
    $content = $fpath."proonly_inner_frl.php";
}


include("template2.php");*/
?>
