<?php

require_once '../classes/stdf.php';
require_once '../classes/account.php';
require_once '../classes/session.php';
require_once '../classes/firstpage.php';
require_once '../classes/payed.php';


$users = $DB->rows("
    SELECT
        DISTINCT from_id, login
    FROM
        orders
    INNER JOIN
        users ON orders.from_id = users.uid
    WHERE
        (from_date <= '2014-01-24 23:59:59') AND (from_date + to_date >= '2014-01-01 00:00:00')
");

echo "Execute recovery PRO accounts for " . count($users) . " users.\n";

$payed = new payed;
$sess  = new session;
foreach ($users as $user) {
    $transaction_id = account::start_transaction($user['from_id']);
    $payed->AdminAddPRO($user['from_id'], $transaction_id, '7 days');
    $session->UpdateProEndingDate($user['login']);
}
