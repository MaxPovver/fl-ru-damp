<?php

require_once 'classes/stdf.php';
require_once 'classes/QChat.php';

header('Content-Type: text/html; charset=utf-8');
//$_POST = $_GET;
$uid  = get_uid(false);
$cid  = isset($_POST['cid'])? $_POST['cid']: '';
$ckey = isset($_POST['ckey'])? $_POST['ckey']: '';
$func = isset($_POST['func'])? $_POST['func']: '';
$attr = isset($_POST['attr'])? $_POST['attr']: '';

if ( empty($uid) || !preg_match('/^[a-zA-Z0-9]{8}$/', $cid) || !preg_match('/^[0-9]{1,9}$/', $ckey) ) {
    QChat::error(2, true);
}

$stream = '';
if ( !empty($_POST['stream']) && ($_POST['stream'] == 'hold' || $_POST['stream'] == 'drop') ) {
    $stream = $_POST['stream'];
}



$qChat = new QChat($uid, $cid, $ckey);

if ( $stream ) {
    
    $qChat->stream($stream);
    
} else if ( $func ) {

    $attr = json_decode(stripslashes($attr));
    
    if ( !is_null($attr) ) {
        $qChat->event($func, $attr);
    }
    
}