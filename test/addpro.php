<?php

require_once '../classes/stdf.php';
require_once '../classes/account.php';
require_once '../classes/session.php';
require_once '../classes/firstpage.php';
require_once '../classes/payed.php';

$payed = new payed;
$sess  = new session;

/*
$users = $DB->rows("
    SELECT
        DISTINCT from_id, login
    FROM
        orders o
    INNER JOIN
        users ON o.from_id = users.uid
    WHERE
        (from_date <= '2013-10-25 00:00:00') AND (from_date + to_date >= '2013-10-24 00:00:00')
        AND NOT EXISTS ( SELECT 1 FROM orders  WHERE from_id = o.from_id AND ( (from_date <= '2013-10-18 00:00:00') AND (from_date + to_date >= '2013-10-17 00:00:00') ) )
");

echo "Execute recovery PRO accounts for " . count($users) . " users.\n";

foreach ($users as $user) {
    $transaction_id = account::start_transaction($user['from_id']);
    $payed->AdminAddPRO($user['from_id'], $transaction_id, '2 days');
    $session->UpdateProEndingDate($user['login']);
}
*/

$users = $DB->rows("
    SELECT
        DISTINCT from_id, login
    FROM
        orders o
    INNER JOIN
        users ON o.from_id = users.uid
    WHERE
        (from_date <= '2013-10-25 00:00:00') AND (from_date + to_date >= '2013-10-24 00:00:00')
        AND EXISTS ( SELECT 1 FROM orders  WHERE from_id = o.from_id AND ( (from_date <= '2013-10-18 00:00:00') AND (from_date + to_date >= '2013-10-17 00:00:00') ) )
");

echo "Execute recovery PRO accounts for " . count($users) . " users.\n";

foreach ($users as $user) {
    $transaction_id = account::start_transaction($user['from_id']);
    $payed->AdminAddPRO($user['from_id'], $transaction_id, '1 days');
    $session->UpdateProEndingDate($user['login']);
}

/*

$users = $DB->rows("
SELECT
	DISTINCT user_id, profession
FROM
	users_first_page x
WHERE
	(from_date <= '2013-10-25 00:00:00') AND (from_date + to_date >= '2013-10-24 00:00:00')
        AND NOT EXISTS ( SELECT 1 FROM users_first_page WHERE user_id = x.user_id AND ( (from_date <= '2013-10-18 00:00:00') AND (from_date + to_date >= '2013-10-17 00:00:00') ) )
");

echo "Execute recovery pay places for " . count($users) . " users.\n";

$fp = new firstpage;
foreach ($users as $user) {
    $transaction_id = account::start_transaction($user['user_id']);
    $fp->AdminAddFP($user['user_id'], $transaction_id, $user['profession'], '1 days');
}

*/