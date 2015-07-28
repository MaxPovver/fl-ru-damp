<?php
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/stdf.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/account.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/payed.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/users.php';

DB::setStandby('master', NULL);

$uid = __paramInit("int", "uid", null, 0);
$uid = intval($uid);
$admin = 103;
if ( $uid > 0 ) {
    global $DB;
    //Есть ли такой незабаненый не про, заходивший в проследний раз более года назад?
    $targetUser = users::userWasInOldYear($uid);
    if ( is_array($targetUser) ) {
        //Даем про на неделю
        $account = new account();
        $payed = new payed();
        $op_code = 115; // 52
        $tr_id = $account->start_transaction($admin);
        $interval = "7 days";
        if ( $targetUser["role"][0] == 1 ) {
            $interval = "1 month";
        }
        $success = $payed->GiftOrderedTarif($bill_id, $gift_id, $uid, $admin, $tr_id, $interval, "Аккаунт PRO в подарок", $op_code);
        if( !$success ) {
            $rpath = "./";
            if (!$fpath) $fpath = "";
            $header = ABS_PATH."/header.new.php";
            $footer = ABS_PATH."/footer.new.html";
            $content = ABS_PATH."/gift_pro_week_error.php";
            $page_title = "Ошибка при активации подарка";
            include("template3.php");
            exit;
        }
        $sql = "UPDATE week_pro_action SET ts = NOW() WHERE uid = {$uid}";
        $DB->query($sql);
        header("location: /login/");
        exit;
    }
} 
header("location: /404.php");
exit;


