<?php

$g_page_id = "0|9";
$rpath = "../";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs_payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");

$stretch_page = true;
$showMainDiv  = true;
$footer_payed = true;
$no_banner = true;


session_start();
$uid = get_uid(false);


if($uid && substr($_SESSION['role'], 0, 1)==1) {
    header( 'Location: /payed-emp/' );
    exit;
} elseif(isProfi()) {
    $content = 'content.disabled.php';
} else {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
    
    $js_file = array( 'payed.js', 'billing.js' );
    
    $user = new freelancer();
    // Изменяем авто продление PRO, если нужно
    if (strtolower($_POST['pro_auto_prolong']) == 'on') {
        $user->setPROAutoProlong('on', $uid);
        $result['success'] = true;
        if( !WalletTypes::isWalletActive($uid) ) {
            $result['wallet_popup'] = true;
        }
        echo json_encode($result);
        exit();
    }
    if (strtolower($_POST['pro_auto_prolong']) == 'off') {
        $user->setPROAutoProlong('off', $uid);
        echo json_encode(array('success'=> true));
        exit();
    }
    
    if ($uid) {
        
        $bill = new billing($uid);
        $_SESSION['pro_last'] = payed::ProLast($_SESSION['login']);
        $_SESSION['pro_last'] = $_SESSION['pro_last']['is_freezed'] ? false : $_SESSION['pro_last']['cnt'];
        $_SESSION['is_was_pro'] = ($_SESSION['pro_last']) ? true : payed::isWasPro($_SESSION['uid']);
        
        if ($_SESSION['pro_last']['is_freezed']) {
            $_SESSION['payed_to'] = $_SESSION['pro_last']['cnt'];
        }
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        $op_codes = new op_codes();
        $opcodes = $op_codes->getCodes('80,16,65');

        $user = new freelancer();
        $user->GetUser($_SESSION['login']);
        
        $account = new account();
        $ok = $account->GetInfo($uid, true);

        $u_is_pro_auto_prolong = $user->GetField($uid, $e, 'is_pro_auto_prolong', false); // Включено ли у юзера автоматическое продление PRO
        $is_user_was_pro = $bill->IsUserWasPro(array(billing::STATUS_RESERVE, billing::STATUS_NEW));//payed::IsUserWasPro($uid);

        $dateFrozenMaxLimit = "date_max_limit_" . date('Y_m_d', strtotime($_SESSION['pro_last'] ? $_SESSION['pro_last'] : ($is_user_was_pro ? "+30 day" : "+ 7 day") ));
        $dateFrozenMinLimit = "date_min_limit_" . date('Y_m_d', strtotime('+ 1 day'));

        $pro_last = false;
        if($_SESSION['freeze_from'] && $_SESSION['is_freezed']) {
            $pro_last = $_SESSION['payed_to'];
        } else if($_SESSION['pro_last']) {
            $pro_last = $_SESSION['pro_last'];
        }
        
        $mod = (hasPermissions('users')) ? 0 : 1;

        $tr_id = intval($_REQUEST['transaction_id']);
        $transaction_id = $account->start_transaction($uid, $tr_id);

        include_once('freeze.php');
        
        
        //Инициализация попапа оплаты
        require_once(ABS_PATH . '/classes/quick_payment/quickPaymentPopupPro.php');
        quickPaymentPopupPro::getInstance()->init();
    }
    
    $content = 'content.new.php';
}


$page_title = "Профессиональный аккаунт - фриланс, удаленная работа на FL.ru";

$css_file = array(
    '/css/block/b-promo/__buy/b-promo__buy.css'
);

$template = 'template3.php';
include ("../".$template);