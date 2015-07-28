<?php
define( 'IS_ACCESS_PAGE', 1 );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banner_promo.php");
session_start();

if(!get_uid(false)) {
    include "../403.php";
    exit;
}

//просмотр информации о статистике баннера
if(isset($_GET['type'])) {
    $type = __paramInit('int', 'type');
    $bpromo = new banner_promo();
    $access = $bpromo->isAccess($type);
    $type   = $bpromo->setType(intval($type), true);
    if($type > 0 && ( $access || hasPermissions('advstat') || hasPermissions('adm') ) ) {
        $count = $bpromo->getCountStat();
        $stats = $bpromo->getStat();
        $banner = $bpromo->info;
        $content = "inner_index.php";
    } else {
        include "../403.php";
        exit;
    }
} else {
    include "../403.php";
    exit;
}

$content = "inner_index.php";

include ($_SERVER['DOCUMENT_ROOT']."/template2.php");

?>