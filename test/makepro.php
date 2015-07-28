<?php
require_once "../classes/log.php";
if (isset($_GET['alpha'])) {
	require_once("../../../_alpha/html/classes/config.php");
} else {
	require_once("../classes/config.php");
}

require_once "../classes/session.php";
require_once "../classes/DB.php";
$DB = new DB('master');

pg_connect("host=".$pg_db['master']['host']." port=".$pg_db['master']['port']." dbname=".$pg_db['master']['name']." user=".$pg_db['master']['user']." password=".$pg_db['master']['pwd']) or die ("could not connect to base");


$pro_op_codes = array(3, 15, 16, 28, 35, 42, 47, 48, 49, 50, 51, 52, 66, 67, 68);

$login   = isset($_GET['l'])? (string) $_GET['l']: FALSE;
$date    = isset($_GET['d'])? date('Y-m-d', strtotime($_GET['d'])): FALSE;
$prolong = (isset($_GET['a']) && in_array((string) $_GET['a'], array('t', 'f')))? (string) $_GET['a']: FALSE;

if ($login != '' && $date) {
	
	$sql = "
		SELECT
			users.uid,
			users.login,
			users.is_pro,
			users.is_pro_test,
			users.is_pro_auto_prolong,
			orders.*
		FROM
			users 
		JOIN
			orders ON orders.from_id = users.uid /*AND tarif IN (".implode(',', $pro_op_codes).")*/ AND (from_date + to_date) >= NOW()
		WHERE
			LOWER(users.login)=LOWER('$login') AND users.active = 't' AND users.passwd <> '' AND users.is_pro = 't'
	";
	$res = pg_query($sql);
	
	if (!pg_num_rows($res)) {
		die("ѕользователь $login не найден или он не PRO");
	}
	
	$updated = 0;
	pg_query('ALTER TABLE orders DISABLE TRIGGER "bI orders"');
	while ($user = pg_fetch_assoc($res)) {
		if (date('Y-m-d', strtotime($user['from_date'])) > date('Y-m-d', strtotime($date))) {
            // все покупки PRO которые были после даты которую ставим как окончани€ PRO загон€ем в прошлое
    		pg_query("UPDATE orders SET from_date='$date 00:00:00', to_date = '0 days' WHERE id = {$user['id']}");
			continue;
		}
		$days = (strtotime($date) - strtotime($user['from_date'])) / 24 / 3600;
		pg_query("UPDATE orders SET to_date = '$days days' WHERE id = {$user['id']}");
		++$updated;
	}
	pg_query('ALTER TABLE orders ENABLE TRIGGER "bI orders"');
	
	if ($updated && (date('Y-m-d') > date('Y-m-d', strtotime($date)))) {
		pg_query("UPDATE users SET is_pro = 'f', is_pro_test = 'f' WHERE LOWER(login) = LOWER('$login')");
	}
	
	if ($prolong) pg_query("UPDATE users SET is_pro_auto_prolong = '$prolong' WHERE LOWER(login) = LOWER('$login')");

	if ($updated) {
        $session = new session();
        $session->UpdateProEndingDate($login);
		echo "Done ($updated)";
	} else {
		echo "ѕользователь $login купил PRO после " . date('Y-m-d', strtotime($date));
	}
	
}

?>
