<?php

//https://beta.free-lance.ru/mantis/view.php?id=29114

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");


$date = strtotime('- 1 hours');
$date = date('Y-m-d H:i:s', $date);



//print_r($date);
//exit;

//первый запуск
//$date = '2015-04-17 14:00:00';



$users = $DB->rows("    
	SELECT 
        DISTINCT ON(a.uid)
        a.uid,
        ao.op_date
	FROM account_operations AS ao
	INNER JOIN account AS a ON a.id = ao.billing_id
	INNER JOIN users AS u ON u.uid = a.uid
	WHERE ao.op_code = 135
	AND ao.op_date >= ? 
	AND ao.balance >= 0
", $date);


//print_r($DB->sql);
//exit;

//print_r($users);
//print_r("\n");

$cnt = 0;

if ($users) {
    foreach ($users as $user) {
	$tu_data = $DB->row("
	    SELECT
		ts.id,
		tos.id AS order_id,
		ts.tax_payed_last
	    FROM account_operations AS ao
	    INNER JOIN account AS a ON a.id = ao.billing_id
	    INNER JOIN tservices_orders AS tos ON tos.acc_op_id = ao.id
	    INNER JOIN tservices AS ts ON ts.id = tos.tu_id	    
	    WHERE 
		ao.op_code = 134
		AND a.uid = ?i
	    ORDER BY ao.id DESC
	    LIMIT 1
	", $user['uid']);
	
	
	//print_r($tu_data);
	//print_r("\n");
	//exit;
		
	if ($tu_data) {
	    
	    /*
	    $ok = $DB->update('tservices', array(
		'tax_payed_last' => $user['op_date']
	    ), 'id = ?i AND user_id = ?i', $tu_data['id'], $user['uid']);
	    */
	    
	    $res = $DB->query("
		UPDATE tservices SET
		    tax_payed_last = ?,
		    payed_tax = tservices_catalog_payed_tax(id, user_id) 
		WHERE id = ?i AND user_id = ?i
		RETURNING id
	    ", $user['op_date'], $tu_data['id'], $user['uid']);
	    
	    if (pg_num_rows($res)) {
		$cnt++;
	    }
	    
	    //print_r("tu_id = {$tu_data['id']}, user_id = {$user['uid']}");
	    //print_r("\n");    
	    
	    //$last = $DB->val("SELECT tax_payed_last FROM tservices WHERE id = ?i AND user_id = ?i", $tu_data['id'], $user['uid']);
	    
	    //print_r("date = {$last}");
	    //exit;
	}	
		
		
    }
}

print_r("
{$cnt}
");






exit;