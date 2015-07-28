<?php

define('NO_CSRF', 1);

require_once $_SERVER['DOCUMENT_ROOT']."/classes/stdf.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/walletWebmoney.php";

if(!get_uid(false)) {
    header("Location: /404.php");
    exit;
}

// Получен код авторизации получаем все токены какие нужны для пользователя
if(isset($_GET['code'])) {
    $uid          = get_uid(false);
    $walletWebmoney = new walletWebmoney($uid);
    $walletWebmoney->api->setAuthCode($_GET['code']);
    $result  = $walletWebmoney->api->initAccessToken();
    if($result['access_token'] != '') {
        $wallet = trim(str_replace('WebMoney Purse', '', $result['account_identifier']));
        $walletWebmoney->data['type']         = WalletTypes::WALLET_WEBMONEY;
        $walletWebmoney->data['uid']          = $uid;
        $walletWebmoney->data['wallet']       = $wallet;
        $walletWebmoney->setAccessToken($result['access_token']);

        $walletId = $walletWebmoney->saveWallet();
        // Токен получен и сохранен отправляем пользователя на страницу
        if($walletId > 0) {
            $_SESSION['wallet_success'] = true;
            $redirect = '/bill/';
            if(isset($_SESSION['redirect_uri_wallet']) && strpos($_SESSION['redirect_uri_wallet'], 'fail_') !== false) {
                $redirect = $_SESSION['redirect_uri_wallet'];
            }

            header("Location: {$redirect}");
            exit;
        }
    } else {
        $_SESSION['errorPs'] = 'Ошибка привязки кошелька Webmoney';
        header("Location: /bill/fail_ps/");
        exit;
//        var_dump($result);
    }

} else {
    header("Location: /404.php");
    exit;
}
