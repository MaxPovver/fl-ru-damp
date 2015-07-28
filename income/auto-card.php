<?php

define('NO_CSRF', 1);

require_once $_SERVER['DOCUMENT_ROOT']."/classes/stdf.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/walletAlpha.php";

if(!get_uid(false)) {
    header("Location: /404.php");
    exit;
}

$uid         = get_uid(false);
$walletAlpha = new walletAlpha($uid);

if(isset($_GET['orderId'])) {
    $filter = array(
        'order_id' => $_GET['orderId'],
        'state'    => 'new'
    );

    $order = $walletAlpha->getOrder(null, $filter);

    if(!empty($order)) {
        //@todo делаем возврат записываем данные биндинга
        $status = $walletAlpha->api->getOrderStatus($order['order_id']);
        if($status['OrderStatus'] == API_AlphaBank::STATUS_SUCCESS_PAYMENT) {
            $year  = substr($status['expiration'], 0, 4);
            $month = substr($status['expiration'], 4, 2);

            $y = $year.$month."01";
            $n = date("Ymd");
            $k = strtotime($y) - strtotime($n);
            $days = floor( $k / ( 60*60*24 ) );


            $walletAlpha->data['validity']     = $days . " days";
            $walletAlpha->data['type']         = WalletTypes::WALLET_ALPHA;
            $walletAlpha->data['uid']          = $uid;
            $walletAlpha->data['wallet']       = $status['Pan'];
            $walletAlpha->setAccessToken($status['bindingId']);

            $walletId = $walletAlpha->saveWallet();
            // Токен получен и сохранен отправляем пользователя на страницу
            if($walletId > 0) {
                $res = $walletAlpha->api->refund(API_AlphaBank::REGISTER_SUM, $order['order_id']);
                if($res['errorCode'] == 0) {
                    $update = array(
                        'state'             => walletAlpha::STATUS_REFUND,
                        'pan'               => $status['Pan'],
                        'expiration'        => $status['expiration'],
                        'cardholder_name'   => $status['cardholderName'],
                        'ip'                => $status['Ip'],
                        'binding_id'        => Wallet::des()->encrypt($status['bindingId'])
                    );
                    $walletAlpha->updateOrder($order['id'], $update);
                }

                $_SESSION['wallet_success'] = true;
                $redirect = '/bill/';
                if(isset($_SESSION['redirect_uri_wallet']) && strpos($_SESSION['redirect_uri_wallet'], 'fail_') !== false) {
                    $redirect = $_SESSION['redirect_uri_wallet'];
                }

                header("Location: {$redirect}");
                exit;
            }
        } else {
            $_SESSION['errorCards'] = $status;
            header("Location: /bill/fail_card/");
            exit;
        }
    } else {
        if($_GET['orderId']==$_SESSION['quick_ver_card_num']) {
            // Верификация
            $walletAlpha->api->getAccessData('bind');
            $status = $walletAlpha->api->getOrderStatus($_SESSION['quick_ver_card_num']);

            if($status['OrderStatus'] == API_AlphaBank::STATUS_SUCCESS_PAYMENT) {
                $res = $walletAlpha->api->reverse($_SESSION['quick_ver_card_num']);
                require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Verification.php';
                $_SESSION['verifyStatus'] = array( 'status' => 1 );
                $_SESSION['is_verify']    = 't';
                $verification = new Verification;
                $verification->card(get_uid(), $status['Pan']);
                $redirect_part = "?vok=1&vuse=card";
            } else {
                $redirect_part = "?verror=1&vuse=card";
            }
            ?>
            <html><body><script>window.close();</script></body></html>
            <?
            exit;
        } elseif($_SESSION['quickpro_card_orderid']==$_GET['orderId']) {
            // Быстрая покупка pro
            $walletAlpha->api->getAccessData('bind');
            $status = $walletAlpha->api->getOrderStatus($_SESSION['quickpro_card_orderid']);
            if($status['OrderStatus'] == API_AlphaBank::STATUS_SUCCESS_PAYMENT) {
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/account.php";
                $account = new account();
                $descr = "Карта ".$status['Pan']." сумма - ".$_SESSION['quickpro_card_sum'].", номер покупки - ".$_GET['orderId'];
                $account->deposit($op_id, $_SESSION['quickpro_card_billing'], $_SESSION['quickpro_card_sum'], $descr, 20, $_SESSION['quickpro_card_sum']);
                $_SESSION['quickpro_card_orderid'] = 'done';
            }
            ?>
            <html><body><script>window.close();</script></body></html>
            <?
            exit;
        } else {
            header("Location: /404.php");
            exit;
        }
    }
} else {
    header("Location: /404.php");
    exit;
}

?>