<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bar_notify.php");

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');

$uid = get_uid(false);

if ($uid <= 0) {
    header_location_exit('/404.php');
}

$bill = new billing($uid);

$period = __paramInit(int, 'period', null, 0);
$event = __paramInit(int, 'event', null, 0);
$page = __paramInit('int', 'page', null, 1);
$page = $page < 1 ? 1 : $page;

$itemsPerPage = 20;
$todayEnd = mktime(23, 59, 59); // конец сегодняшнего дня
switch ($period) {
    case 0: // за последнюю неделю
        $startTime = strtotime('- 1 week', $todayEnd);
        break;
    case 1: // за последний месяц
        $startTime = strtotime('- 1 month', $todayEnd);
        break;
    case 2: // за последний год
        $startTime = strtotime('- 1 year', $todayEnd);
        break;
    case 3: // за все время
        $startTime = null;
        break;
}

$history = account::getBillHistory($page, $itemsPerPage, $startTime, $event);

//Идентификаторы СБР для получения признака новая или старая
$sbrIds = array(); $nSbr = 0;
foreach ($history['items'] as &$historyItem) {
    if (in_array($historyItem['op_code'], array(sbr::OP_RESERVE, sbr::OP_DEBIT, sbr::OP_CREDIT))) {
        if (preg_match('~(?:СБР|БС)-(\d+)-[ТАПБ]/О~', $historyItem['comments'], $m)) {
            if ((int)$m[1]) {
                $sbrIds[] = (int)$m[1];
                $historyItem['sbrId'] = (int)$m[1];
                $nSbr++;
            }
        }
    }
}
unset($historyItem);
if ($nSbr) {
    $sbrSchemes = sbr_meta::getShemesSbr($sbrIds);
    if ($sbrSchemes) {
        foreach ($history['items'] as &$historyItem) {
            if((int)$historyItem["sbrId"]) {
                $historyItem['comments'] = sbr_meta::parseOpComment($historyItem['comments'], null, null, $sbrSchemes[$historyItem["sbrId"]]);
            }
        }
    }
}
unset($historyItem);

// делаем уведомления прочитанными
$barNotify = new bar_notify($_SESSION['uid']);
$barNotify->delNotifies( array('page'=>'bill') );


if ($page > 1 && $page > $history['pagesCount']) {
    header_location_exit('/404.php');
}

$events = account::searchBillEvent($startTime ? $startTime : '2000-01-01', time());
$js_file = array('billing.js');


$is_jury = sbr_meta::isFtJuri($uid);
$is_emp = is_emp();

$isAllowAddFunds = !$is_emp && !$is_jury;
if ($isAllowAddFunds) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupAccount.php');
    quickPaymentPopupAccount::getInstance()->init(array(
        'acc_sum' => $bill->getAccSum()
    ));
}

//Пользователь юрик с заполненными реквизитами?
$isAllowBillInvoice = $is_jury;

if ($isAllowBillInvoice) {
    $isValidBillInvoice = sbr_meta::isValidUserReqvs($uid, $is_emp);
    if($isValidBillInvoice){
        //Попап пополнения счета по безналу путем генерации счета
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupBillInvoice.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bill/models/BillInvoicesModel.php');

        quickPaymentPopupBillInvoice::getInstance()->init();
        $billInvoicesModel = new BillInvoicesModel();
        $billInvoice = $billInvoicesModel->getLastActiveInvoice($uid);
    }
    $showReserveNotice = $is_emp;
}



$content = "content.php";
$header = "../../header.new.php";
$footer = "../../footer.new.html";
include ("../../template3.php");