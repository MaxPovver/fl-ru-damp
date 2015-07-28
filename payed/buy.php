<?
$g_page_id = "0|9";
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
session_start();
$user_id = get_uid();
$uid = $user_id;

if (!$uid) {
    header("Location: /fbd.php");
    exit;
}
$user = new users();
$bill = new billing($uid);
$bill->clearOrders();
$created = $bill->create($_POST['oppro'], $user->GetField($uid, $e, 'is_pro_auto_prolong', false) == 't' ? 1 : 0);
 
if($created) {
    header("Location: /bill/orders/");
} else {
    header("Location: /bill/fail/");
}
exit;

$action = trim($_POST['action']);
$mnth = intval(trim($_POST['mnth']));
if($_POST['oppro'] == 76) {
    header("Location: ./"); 
    exit();
}

if (!$action || !$mnth) {
    header("Location: ./"); 
    exit();
}

$prof = new payed();
$tr_id = $_REQUEST['transaction_id'];

if (!$tr_id) {
	$account = new account();
	$account -> view_error("Невозможно завершить транзакцию. Попробуйте повторить операцию с самого начала.");
}

if($mnth > 0) {
   $oppro = intval(trim($_POST['oppro']));
   if($oppro <= 0)
       $oppro = is_emp()?15:48;
   $ok = $prof->SetOrderedTarif($user_id, $tr_id, $mnth, "Аккаунт PRO", $oppro, $error);
}


if (!$ok) {
    $_SESSION['bill.GET']['error'] = $error;
    header('Location: /bill/fail/');
    exit;
} else {
    $content = "content.php";
    $js_file = array( 'payed.js' );
}

$_SESSION['pro_last'] = payed::ProLast($_SESSION['login']);

if($_SESSION['pro_last']['is_freezed']) {
    $_SESSION['payed_to'] = $_SESSION['pro_last']['cnt'];
}

$_SESSION['pro_last'] = $_SESSION['pro_last']['is_freezed'] ? false : $_SESSION['pro_last']['cnt'];
$userdata = new users();
$_SESSION['pro_test'] = $userdata->GetField($user_id, $error2, 'is_pro_test', false);
// цены на PRO
$prices = $prof->GetProPrice(true);
// текущая сумма оплаты
$cost = $prices[$oppro] * $mnth;

if($ok) {
    payed::UpdateProUsers();
    // PRO для работтодателя
    if (is_emp()) {
        header("Location: /payed-emp/pro_payed.php?months=$mnth");
        exit;
    // тестовый PRO
    } elseif ($oppro == 47 || $oppro == 114) {
        header("Location: /payed/pro_test_payed.php");
        exit;
    // PRO для фрилансера
    } else {
        if ($oppro == 76) {
            $params = "weeks=$mnth&cost=$cost";
        } else {
            // срок PRO (месяцев)
            $periods = array('48'=>1,'49'=>3,'50'=>6,'51'=>12);
            $months = $periods[$oppro];
            $params = "months=$months&cost=$cost";
        }
        header("Location: /payed/pro_payed.php?$params");
        exit;
    }
}
$prof->getSuccessInfo($data);
$header = "../header.php";
$footer = "../footer.html";

include ("../template.php");

?>
