<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/account.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer.php';

$g_page_id = "0|35";
$stretch_page = true;
$showMainDiv  = true;

session_start();
$uid = get_uid();
$rpath = "../../";

$page_title = "Получи MacBook PRO 13\" оплачивая услуги fl.ru в июне чаще других";
$header = "../../header.php";
$footer = "../../footer.html";
$content = "content.php";


$macbook_top_10_all = $DB->rows(
    "select count(*) as nums, billing_id from account_operations
    where 
        op_date >= '2015-06-01 00:00:00' 
        and op_date <= '2015-06-30 23:59:59' 
        and ammount < 0 and 
        op_code in(48, 49, 50, 51, 163, 164, 142, 148, 181, 184, 143, 149, 182, 185, 144, 150, 183, 186, 155, 173, 156, 174, 157, 175, 158, 176)
        and is_our = false
    group by billing_id
    order by nums desc
    "
);

// Количество человек
$macbook_top_10_total = sizeof($macbook_top_10_all);

$uid = get_uid();
$billing_id = $DB->val(
    "SELECT id from account where uid=?", $uid
);

$user_position = 0;

$macbook_top_10 = array();

foreach ($macbook_top_10_all as $key => $value) {
    if ($key < 10) {
        $user_id = $DB->val(
            "SELECT freelancer.uid from freelancer INNER JOIN account ON account.uid=freelancer.uid WHERE account.id = ?", 
            $value['billing_id']
        );

        $freelancer = new freelancer();
        $freelancer->GetUserByUID($user_id);

        $macbook_top_10[$key]['user'] = $freelancer;
    }

    if (intval($billing_id) === intval($value['billing_id'])) {
        $user_position = $key + 1;
    }

    if ($key > 10 && $user_position > 0) {
        break;
    }

}

$js_file  = array( '/css/block/b-shadow/b-shadow.js', 'timer.js' , 'verification.js' );
include "../../template3.php";