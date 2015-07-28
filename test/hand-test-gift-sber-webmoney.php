<?php
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/stdf.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/account.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/payed.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/users.php';?>
<div style="padding-left:25px; padding-bottom:100px">
 Перед тестированием можно войти в аккаунт bolvan1.<br/><br/>
 Сбербанк<br/><br/>
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=95&uid=237169"> Пополнение счета для bolvan1 на 2000</a><br/> 
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=96&uid=237169"> Пополнение счета для bolvan1 на 1000</a><br/> 
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=97&uid=237169"> Пополнение счета для bolvan1 на 5000</a><br/><br/> 
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=97&uid=237962"> Пополнение счета для givejob1 на 5000</a><br/><br/><br/>
 
 Webmoney<br/><br/>
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=91&uid=237169"> Пополнение счета для bolvan1 на 2000</a><br/>
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=93&uid=237169"> Пополнение счета для bolvan1 на 5000</a><br/><br/>
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=93&uid=237962"> Пополнение счета для givejob1 на 5000</a><br/>
 </div><div style="text-align:center; width:100%">
<?php

$uid = __paramInit("int", "uid", null, 0);
$uid = intval($uid);
$opcode = intval(__paramInit("int", "oc", null, 0));
if ( $opcode != 95 && $opcode != 96 && $opcode != 97 && $opcode != 91 && $opcode != 93   ) {
 $opcode = 0;
}
$admin = 103;
if ( $uid > 0 ) {
    global $DB;
    //Проверяем параметры
    if ( $uid && $opcode ) {
        //Дарим
        $account = new account();
        $payed = new payed();
        $op_code = $opcode;
        $tr_id = $account->start_transaction($admin);
        $error = $account->Gift($id, $gid, $tr_id, $op_code, $admin, $uid, "Тестирование!!!", "", 1);//$payed->GiftOrderedTarif($bill_id, $gift_id, $uid, $admin, $tr_id, $interval, "Тестирование пополнение счета через Сбербанк или Вебмани", $op_code);
        if( $error ) {
            echo "Произошла какая-то ошибка, код ошибки ".$error;
        } else {
            echo "<a href='/login.php' target='_blank'>Пройдите на сайт, чтобы прочесть сообщение</a>";
        }
    }
} 
?></div>