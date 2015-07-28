<?php

require_once '../classes/stdf.php';
require_once '../classes/account.php';
require_once '../classes/session.php';
require_once '../classes/payed.php';


$users = $DB->rows("
                SELECT uid, login, EXTRACT(epoch FROM to_date)/86400 as days 
                FROM orders 
                INNER JOIN employer e ON e.uid = orders.from_id
                WHERE tarif = 76;
                ");

echo "Execute recovery PRO accounts for " . count($users) . " users.\n";

$payed = new payed;
$sess  = new session;
foreach ($users as $user) {
    $transaction_id = account::start_transaction($user['uid']);
    $payed->AdminAddPRO($user['uid'], $transaction_id, ($user['days']/7*17).' days');
    $session->UpdateProEndingDate($user['login']);
}


