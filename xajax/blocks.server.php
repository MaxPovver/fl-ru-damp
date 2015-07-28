<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/blocks.common.php");

function pay_place_top($catalog=0, $caruselTop) {
    global $DB, $session;

    if($catalog==0) {
        $yaM = "yaCounter6051055.reachGoal('main_carousel_ref');";
    } else {
        $yaM = "yaCounter6051055.reachGoal('cat_carousel_ref');";
    }

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
    $payPlace = new pay_place($catalog);
    $ppAds = $payPlace->getUserPlaceNew();
    
    if(is_array($ppAds)) {
        foreach ($ppAds as $ppAd) {
            $pp_uids[] = $ppAd['uid'];
        }
        $pp_uids = array_unique($pp_uids);
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $usrs = new users();
        $pp_result = $usrs->getUsers("uid IN (".implode(",", array_values($pp_uids)).")");

        foreach($pp_result as $k=>$v) $toppay_usr[$v['uid']] = $v;

        $pp_h = $payPlace->getAllInfo($pp_uids);
    }
    
    $not_load_info = true;
    ob_start();
    include ($_SERVER['DOCUMENT_ROOT'] . '/templates/pay_place.php');
    $html = antispam(str_replace(array("\r", "\n"), "", ob_get_clean()));
    
    $aRes['success'] = true;
    $aRes['html']    = iconv("windows-1251", "UTF-8", $html);
    
    echo json_encode( $aRes );
}

function qaccess() {
    global $DB, $session;
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
    
    ob_start();
    include ($_SERVER['DOCUMENT_ROOT'] . '/templates/qaccess.php');
    $html = trim(str_replace(array("\r", "\n"), "", ob_get_clean()));
    
    $aRes['success'] = true;
    $aRes['html']    = iconv("windows-1251", "UTF-8", $html);
    
    echo json_encode( $aRes );
    
}

function catalog_promo($prof_id) {
    global $session;
    
    ob_start();
    include ($_SERVER['DOCUMENT_ROOT'] . '/templates/catalog_promo.php');
    $html = trim(str_replace(array("\r", "\n"), "", ob_get_clean()));
    
    $aRes['success'] = true;
    $aRes['html']    = iconv("windows-1251", "UTF-8", $html);
    
    echo json_encode( $aRes );
}

$xajax->processRequest();
?>