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
        (from_date <= '2011-05-27 00:00:00') AND (from_date + to_date >= '2011-05-23 00:00:00')
");

echo "Execute recovery PRO accounts for " . count($users) . " users.\n";

$payed = new payed;
$sess  = new session;
foreach ($users as $user) {
    $transaction_id = account::start_transaction($user['from_id']);
    $payed->AdminAddPRO($user['from_id'], $transaction_id, '3 days');
    $session->UpdateProEndingDate($user['login']);
}



$users = $DB->rows("
SELECT
	DISTINCT user_id, profession
FROM
	users_first_page
WHERE
	(from_date <= '2011-05-27 00:00:00') AND (from_date + to_date >= '2011-05-23 00:00:00')
");

echo "Execute recovery pay places for " . count($users) . " users.\n";

$fp = new firstpage;
foreach ($users as $user) {
    $transaction_id = account::start_transaction($user['user_id']);
    $fp->AdminAddFP($user['user_id'], $transaction_id, $user['profession'], '3 days');
}