<?
$g_page_id = "0|9";
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
session_start();
$user_id = get_uid();
$uid = $user_id;

$result = array("success" => false);

if (!$uid) {
    echo json_encode($result);
    exit;
}
 
$action = trim($_POST['action']);
$mnth = intval(trim($_POST['mnth']));
if($_POST['oppro'] == 76) {
    echo json_encode($result); 
    exit();
}

if (!$action || !$mnth) {
    echo json_encode($result); 
    exit();
}

$prof = new payed();
$tr_id = $_REQUEST['transaction_id'];

if (!$tr_id) {
    $result['error'] = "Невозможно завершить транзакцию. Попробуйте повторить операцию с самого начала.";
    echo json_encode($result); 
    exit();
}

if($mnth > 0) {
   $oppro = intval(trim($_POST['oppro']));
   if($oppro <= 0)
       $oppro = is_emp()?15:48;
   $ok = $prof->SetOrderedTarif($user_id, $tr_id, $mnth, "Аккаунт PRO", $oppro, $error);
}


if (!$ok) {
    //$_SESSION['bill.GET']['error'] = $error;
    //header('Location: /bill/fail/');
    $result['error'] = $error;
    echo json_encode($result); 
    exit;
} else {
    $content = "content.php";
    $js_file = array( 'payed.js' );
}

$_SESSION['pro_last'] = payed::ProLast($_SESSION['login']);

if($_SESSION['pro_last']['is_freezed']) {
    $is_freezed = true;
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
    $account = new account();
	$account->GetInfo($uid, true);
    $payed_list = payed::getPayedPROList(is_emp() ? 'emp' : 'frl');
    $is_not_enough = array('' => 'default');
    foreach($payed_list as $value) {
        if($value['is_test'] && payed::IsUserWasPro($uid)) continue;
        $dcost = $value['cost'] - $account->sum;
        if($dcost <= 0) continue;
        $is_not_enough[$value['opcode']] = $dcost;
    }
    payed::UpdateProUsers();
    $result['success']  = true;
    $result['opcode']   = $oppro;
    $result['transaction'] = $account -> start_transaction($uid, $tr_id);
    $result['acc_sum']  = $account->sum;
    $result['pro_last'] = date('d.m.Y', strtotime($is_freezed ? $_SESSION['payed_to'] : $_SESSION['pro_last']));
    $result['date_max_limit'] = 'date_max_limit_' . date('Y_m_d', strtotime($is_freezed ? $_SESSION['payed_to'] : $_SESSION['pro_last']));
    $result['is_not_enough'] = $is_not_enough;
    echo json_encode($result); 
    exit;
    // PRO для работтодателя
//    if (is_emp()) {
//        $result['oppro'] = ;
//        exit;
//    // тестовый PRO
//    } elseif ($oppro == 47 || $oppro == 114) {
//        header("Location: /payed/pro_test_payed.php");
//        exit;
//    // PRO для фрилансера
//    } else {
//        if ($oppro == 76) {
//            $params = "weeks=$mnth&cost=$cost";
//        } else {
//            // срок PRO (месяцев)
//            $periods = array('48'=>1,'49'=>3,'50'=>6,'51'=>12);
//            $months = $periods[$oppro];
//            $params = "months=$months&cost=$cost";
//        }
//        header("Location: /payed/pro_payed.php?$params");
//        exit;
//    }
}
//$prof->getSuccessInfo($data);
//$header = "../header.php";
//$footer = "../footer.html";
//
//include ("../template.php");

?>
