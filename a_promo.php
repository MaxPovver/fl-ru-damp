<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banner_promo.php");
session_start();
$banner = intval($_GET['type']);
$bpromo = new banner_promo($_GET['type']);
if(intval($bpromo->info['id'])) {
    $bpromo->writeClickStat();
    header('Location: '.$bpromo->info['banner_link']);
    exit;
} else {
    require_once("404.php");
    exit;
}

?>
