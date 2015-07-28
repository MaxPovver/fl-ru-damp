<?php

//Если пришли от WM отключаем проверку CSRF  
if (isset($_POST['WmLogin_WMID'])) {
    $allow_fp = true;
    define('NO_CSRF', 1);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Verification.php';

$g_page_id = "0|35";
$stretch_page = true;
$showMainDiv  = true;

session_start();
$uid = get_uid();
$rpath = "../../";

$page_title = "Верификация - фриланс, удаленная работа на FL.ru";
$header = "../../header.php";
$footer = "../../footer.html";
$content = "content.php";

$no_banner = true;

$verification = new Verification;
$verifyCount = $verification->verifyCount();

$js_file  = array( '/css/block/b-shadow/b-shadow.js', 'timer.js' , 'verification.js' );
include "../../template3.php";