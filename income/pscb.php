<?php

define('NO_CSRF', true);

$post = $_POST;
$get = $_GET;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pskb.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
$src = __paramValue('int', $get['res']);
$state = __paramValue('int', $post['state']);
$lc_id = __paramValue('int', $post['account']);

$log_data = array('GET' => $get, 'POST' => $post);

if (!pskb::validateCardRequest($post)) {
    $src = 2;
    $state = -999;
    $log_data['err'] = array('src' => $src, 'state' => $state);
}

$log = new log("pskb_cards/income-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ' . $_SERVER['REMOTE_ADDR'] . ' : ');
$log->writevar($log_data);

$pskb = new pskb();
$_lc = $pskb->getLCbyLCId($lc_id);

$err_msg = "Неизвестная ошибка";
if ($src == 2) {
    if ($state && in_array($state, array_keys(pskb::$card_messages))) {
        $err_msg = pskb::$card_messages[$state];
    }
    
    if (!in_array($state, array(2, -999))) {
        $pskb->upLC(array('state' => 'err', 'stateReason' => $err_msg), $_lc['lc_id']);
    }
}

if ($src === 1 && defined('PSKB_TEST_MODE')) {
    
    $lc = new pskb_lc(json_encode($_lc));
    $lc->id = $_lc['lc_id'];
    $lc->state = 'cover';

    $test = new pskb_server();
    $test->set($lc);
}

include_once ($_SERVER['DOCUMENT_ROOT'] . "/sbr/employer/tpl.pskb-cards-income.php");