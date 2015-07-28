<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';

if ( empty($_GET['cdakey']) ) {
    die('NO KEY');
}
$key = $_GET['cdakey'];

$mem = new memBuff;
$data = $mem->get(CROSSDOMAINAUTH_KEY_NAME . $key);
if ( empty($data['back']) || empty($data['time']) || (mktime() - $data['time'] > 120) ) {
    die('KEY EMPTY');
}

session_start();
$data['sess'] = session_id();

$mem->set(CROSSDOMAINAUTH_KEY_NAME . $key, $data, 120);

if ( strpos($data['back'], '?') === FALSE ) {
    /*if ( preg_match("/\/$/", $data['back']) ) {
        $back = $data['back'] . '?' . 'cdakey=' . $key;
    } else {
        $back = $data['back'] . '/?' . 'cdakey=' . $key;
    }*/
    $back = $data['back'] . '?' . 'cdakey=' . $key;
} else {
    $back = $data['back'] . '&' . 'cdakey=' . $key;
}

header('Location: ' . $back);
exit;