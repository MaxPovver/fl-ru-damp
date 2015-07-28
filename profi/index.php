<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");

$stretch_page = true;
$showMainDiv  = true;
$footer_payed = true;
$no_banner = true;

$freelancer = new freelancer();
$is_allow = isAllowProfi();

if ($is_allow) {

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
    
    $uid = get_uid();
    $account = new account();
    $account->GetInfo($uid, true);
    
    if(__paramInit('bool', 'quickprofi_ok', NULL, false)) {
        //Обновляем сессию
        $freelancer->GetUserByUID($uid);
        $_SESSION['is_profi'] = $freelancer->isProfi();
    
        $pro_last = payed::ProLast($_SESSION['login']);
        $_SESSION['pro_last'] = $pro_last['is_freezed'] ? false : $pro_last['cnt'];
    }
}        

$catalogList = $freelancer->getProfiCatalog(80);

$maxFirstCLBlock = 90;
$cntCatalogList = count($catalogList);
$isMoreCatalogList = $cntCatalogList > $maxFirstCLBlock;
$cntFirstCLBlock = $isMoreCatalogList?$maxFirstCLBlock:$cntCatalogList;


$page_title = "PROFI аккаунт - фриланс, удаленная работа на FL.ru";

$css_file = array('/css/block/b-icon/__cont/b-icon__cont.css');
$js_file = array('payed.js','billing.js');

$content = 'content.php';
$header = "../header.php";
$footer = "../footer.html";

include("../template3.php");
exit;