<?php
$g_page_id = '0|9';
$rpath     = '../';

require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs_payed.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/account.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/payed.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
$stretch_page = true;
$showMainDiv  = true;
session_start();
$uid = get_uid();
$no_banner = true;

/*
if ( !$uid ) { 
    include( '../emp_only.php' );
    exit(); 
}
*/

if ( $uid && substr($_SESSION['role'], 0, 1) != 1 ) {
    header( 'Location: /payed/' );
    exit();
}

if($uid) {
    
	$mod  = hasPermissions('users') ? 0 : 1;
    $user = new employer();
    
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

	$tr_id          = intval( $_REQUEST['transaction_id'] );
	$account        = new account();
    $ok             = $account->GetInfo($_SESSION['uid'], true);
	$transaction_id = $account->start_transaction( $uid, $tr_id );
    $js_file        = array( 'payed.js', 'billing.js' );
    
	include_once( '../payed/freeze.php' );
    
    
    $pro_last = false;
    if($_SESSION['freeze_from'] && $_SESSION['is_freezed']) {
        $pro_last = $_SESSION['payed_to'];
    } else if($_SESSION['pro_last']) {
        $pro_last = $_SESSION['pro_last'];
    }
    
    $u_is_pro_auto_prolong = $user->GetField($uid, $e, 'is_pro_auto_prolong', false);
    
    $_SESSION['pro_last'] = payed::ProLast($_SESSION['login']);

    if($_SESSION['pro_last']['is_freezed']) {
        $_SESSION['payed_to'] = $_SESSION['pro_last']['cnt'];
    }

    $_SESSION['pro_last'] = $_SESSION['pro_last']['is_freezed'] ? false : $_SESSION['pro_last']['cnt'];
    
    $dateFrozenMaxLimit = "date_max_limit_" . date('Y_m_d', strtotime($_SESSION['pro_last'] ? $_SESSION['pro_last'] : "+30 day"));
    $dateFrozenMinLimit = "date_min_limit_" . date('Y_m_d', strtotime('+ 1 day'));
    
    
    //Инициализация попапа оплаты
    require_once(ABS_PATH . '/classes/quick_payment/quickPaymentPopupPro.php');
    quickPaymentPopupPro::getInstance()->init();
}

$prices = array(
    'pro' => array(
        'vacancy' => new_projects::getProjectInOfficePrice(true)
    ),
    'nopro' => array(
        'vacancy' => new_projects::getProjectInOfficePrice(false)
    )
);

$page_title   = 'Профессиональный аккаунт - фриланс, удаленная работа на FL.ru';

$header       = '../header.php';
$footer       = '../footer.html';
$footer_payed = true;
$css_file = array('/css/block/b-promo/__buy/b-promo__buy.css');

$content      = 'content.new.php';

include( '../template2.php' );