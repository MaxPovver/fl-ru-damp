<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if (is_release()) {
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/onlinedengi.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pskb.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");

$step = __paramInit('int', null, 'step', 'null');

$userid = __paramInit('int', null, 'nickname');
$userid_extra = __paramInit('int', null, 'nick_extra');
$amount = __paramInit('int', null, 'amount');
$paymode = __paramInit('int', null, 'mode_type');
$order_id = __paramInit('int', null, 'order_id');

$paymentid = mt_rand(mt_rand(1, 9999), mt_rand(11000, 999999));

$key1 = md5('0' . $userid . '0' . onlinedengi::SECRET);
$key2 = md5($amount . $userid . $paymentid . onlinedengi::SECRET);

$params1 = array(
    'userid'        => $userid,
    'userid_extra'  => $userid_extra,
    'key'           => $key1,
);

$params2 = array(
    'amount'        => $amount,
    'userid'        => $userid,
    'userid_extra'  => $userid_extra,
    'paymentid'     => $paymentid,
    'key'           => $key2,
    'paymode'       => $paymode,
    'orderid'       => $order_id,
);

$m = new pskb_server();
$lc = $m->get($order_id);

if ($lc->state == 'cover') { ?>
<h1>уже покрыт</h1>
<? exit; } ?>

<form method="post" action="" id="frm">
    <? foreach($_POST as $k => $v) { if ($k == 'u_token_key') continue;?>
    <input type="hidden" name="<?= $k ?>" value="<?= html_attr($v) ?>"/>
    <? } ?>
    <input type="hidden" name="step" id="step_fld" value="1"/>
    <input type="hidden" name="u_token_key" value="<?= $_SESSION['rand'] ?>"/>
</form>
<? if (!$lc || !$lc->id) { ?>
<script>
    document.getElementById('frm').setAttribute('action', '/income/do.php?src=4');
    document.getElementById('frm').submit();
</script>
<? } ?>

<?
switch ($step) {    
    case 'null':
        ?>
        <p>«десь пользователь производит оплату в платежном терминале той пл.системы, которую выбрал.</p>
        <p>¬ результате мы либо получаем статус аккредитива cover (если покрыт - пользователь оплатил счет), либо возврат пользовател€ к нам (в случае ошибки платежа, отказа и тд).</p>
        <input type="button" value="cover" onclick="document.getElementById('step_fld').setAttribute('value', 1); document.getElementById('frm').submit();"/>
        <input type="button" value="отказ от платежа" onclick="document.getElementById('step_fld').setAttribute('value', 2); document.getElementById('frm').submit();"/>        
        <?
        break;
    case 1:
        $lc->state = 'cover';
        $m->set($lc);
        ?>
        <script>
            document.getElementById('frm').setAttribute('action', '/income/do.php?src=3');
            document.getElementById('frm').submit();
        </script>
        <?
        break;
    case 2:
        ?>
        <script>
            document.getElementById('frm').setAttribute('action', '/income/do.php?src=4');
            document.getElementById('frm').submit();
        </script>
        <?
}


function __make_request($params, $src) {
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $GLOBALS['host'] . '/income/do.php?src=' . $src);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if(defined('BASIC_AUTH')) {
        curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    }
    $res = curl_exec($ch);
    
    return $res;
}


?>
