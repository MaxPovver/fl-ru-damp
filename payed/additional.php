<?

$g_page_id = "0|9";
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
session_start();
$uid = get_uid(false);
$account = new account();
$answers = new projects_offers_answers();
$op_codes = $answers->GetOpCodes();

$action = trim($_POST['action']);
if (!$action) header("Location: ./");

$payed = new payed();
$tr_id = __paramInit('int', NULL, 'transaction_id');

$answer_pay = __paramInit('bool', NULL, 'answer_pay');

$spec_pay = __paramInit('bool', NULL, 'spec_pay');
$spec_prolong = __paramInit('bool', NULL, 'prolong_specs');
if($spec_prolong) {
    return; // #0022795
    if($err = professions::prolongSpecs($uid)) {
        $error['prolong_specs'] = $err;
    }
    $answer_pay = null;
}
else if($spec_pay) {
    return; // #0022795
    if($spec_cnt = __paramInit('int', NULL, 'spec_cnt')) {
        $err = professions::buySpec($uid, $spec_cnt, $tr_id, '1 mon', 0);
        if($err) {
           $error['spec'] = $err;
        } else {
            $_SESSION['bill.GET']['addinfo'] = "<a href=\"/users/{$_SESSION['login']}/setup/specaddsetup/\">—траница управлени€ специализаци€ми</a>";
        }
    }else{
        $error['spec'] = 'Ќе верно указано заначение количества доп. специализаций ';
    }
}

if($answer_pay) {
    if($_POST['answers_sum'] > 0) {
        $num_answers = intval($_POST['num_answers']);
        if (!($err = $answers->BuyByFM($uid, $num_answers, $tr_id, 0))) {
            $_SESSION['answers_ammount'] = $_POST['num_answers'];
        }
        if($err) $error['answers'] = $err;
    }
}

if($account->check_transaction($tr_id, $uid) && !$error)
    $account->commit_transaction($tr_id, $uid, 'null');

if(!$error) {
    header("Location: /bill/success/");
    exit;
}
else {
    $_SESSION['bill.GET']['error'] = current($error);
    header("Location: /bill/fail/");
    exit;
}

$content = "content.php";
$js_file = array( 'payed.js' );
$header = "../header.php";
$footer = "../footer.html";

include ("../template2.php");
?>
