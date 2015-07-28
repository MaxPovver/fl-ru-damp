<?php

define('NO_CSRF', 1);

require_once $_SERVER['DOCUMENT_ROOT']."/classes/stdf.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/walletYandex.php";

if(!get_uid(false)) {
    header("Location: /404.php");
    exit;
}

// Получен код авторизации получаем все токены какие нужны для пользователя
// @todo защита данной страницы
if(isset($_GET['code'])) {
    $uid          = get_uid(false);
    $walletYandex = new walletYandex($uid);
    $walletYandex->api->setAuthCode($_GET['code']);
    $result  = $walletYandex->api->initAccessToken();

    if($result['access_token'] != '') {
        list($wallet, $token) = explode(".", $result['access_token']);
        $walletYandex->data['type']         = WalletTypes::WALLET_YANDEX;
        $walletYandex->data['uid']          = $uid;
        $walletYandex->data['wallet']       = $wallet;
        $walletYandex->setAccessToken($result['access_token']);

        $walletId = $walletYandex->saveWallet();
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
        $_SESSION['errorPs'] = 'Ошибка привязки кошелька Яндекс.Деньги';
        header("Location: /bill/fail_ps/");
        exit;
//        var_dump($result);
    }

} else {
    header("Location: /404.php");
    exit;
}

?>