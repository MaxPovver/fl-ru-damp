<?php

define('NO_CSRF', 1);
$request = $_POST;

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/onlinedengi_cards.php';

// временная мера. нужен рефракторинг
if ( !empty($request['paymode']) && ($request['paymode'] == 204) ) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/webpay.php';
    $webpay = new webpay;
    $webpay->income($request);
} else {
    $src = __paramInit('int', 'src');
    $dol = new onlinedengi_cards();
    $resp = $dol->handleRequest($src, $request);
    echo $resp;
}
exit();