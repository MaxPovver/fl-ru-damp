<?php
//##0028607

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

//------------------------------------------------------------------------------

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//------------------------------------------------------------------------------


function fix_account_operations($user, $update)
{
    global $DB;
    
        $list = $DB->rows("
        SELECT

            a.sum,
            ao.id,
            ao.billing_id,
            ao.ammount,
            ao.balance,
            ao.op_date
            
        FROM account_operations AS ao 
        INNER JOIN account AS a ON a.id = ao.billing_id
        WHERE a.uid = ?i 
        ORDER BY /*ao.op_date,*/ ao.id
    ", $user['uid']);

    if ($list) {
        foreach ($list as $idx => $item) {
            $prev = $idx - 1;
            if (isset($list[$prev]) /*&& $list[$prev]['ammount'] < 0*/) {

                $prev_balance = round($list[$prev]['balance'],6);
                $check = round($item['balance'] - $item['ammount'],6);

                //print_r("{$item['op_date']} {$item['id']} {$prev_balance} {$check}\n");
                
                if (($prev_balance - $check) != 0) {

                    $fix =  $prev_balance - $check;
                    $last = end($list);
                    $last_balance = $last['balance'];
                    $fix_balance_check = $last_balance + $fix;
                    $sum = $item['sum'];
                    $sum_calc = $DB->val("SELECT SUM(ammount) FROM account_operations WHERE billing_id = ?i", $item['billing_id']);
                    
                    
                    print_r("Bad Acc Id: {$list[$prev]['id']},{$item['id']}\n");
                    print_r("Fix offset = {$fix}\n");
                    print_r("Fix_balance_check = {$fix_balance_check}\n");
                    print_r("Actual balance (Sum) = {$sum}\n");
                    print_r("Calc balance (Sum) = {$sum_calc}\n\n\n");
                    
                    
                    //round($item['sum'],6)
                    
                    
                    /*
                    if ($fix_balance_check == round($item['sum'],6)) {
                        print_r('fixed!');echo "\n\n";
                    }
                    */


                    //exit;


                    if ($update) {
                        $ok = $DB->query("
                            ALTER TABLE account_operations DISABLE TRIGGER \"aIUD account_operations/rating\";
                            
                            UPDATE account_operations 
                            SET balance = balance + ?f 
                            WHERE billing_id = ?i AND id >= ?i;
                            
                            ALTER TABLE account_operations ENABLE TRIGGER \"aIUD account_operations/rating\";
                            ", $fix, $item['billing_id'], $item['id']);
                        
                        if ($ok) {
                            fix_account_operations($user, $update);
                        }
                    }

                    break;
                }
            }
        }
    }
}


//------------------------------------------------------------------------------


$logins = @$_GET['login'];
$update = isset($_GET['update']) && $_GET['update'] == 1;

$logins = explode(',', $logins);


$users = $DB->rows("
SELECT

	a.uid,
    u.login

FROM account_operations AS ao 
INNER JOIN
(
	SELECT
		MAX(ao.id) AS id
	FROM account_operations AS ao 
	GROUP BY ao.billing_id
) AS q ON q.id = ao.id
INNER JOIN account AS a ON a.id = ao.billing_id
INNER JOIN users AS u ON u.uid = a.uid
WHERE 
    a.sum <> ao.balance
    AND u.login IN(?l)
ORDER BY ao.balance DESC
LIMIT 100;
", $logins);


if (!$users) {
    echo "Users not found!" . PHP_EOL;
    exit;
}


foreach ($users as $user) {
    
    echo "User: {$user['login']}" . PHP_EOL;
    fix_account_operations($user, $update);
    
}




exit;















/*
SELECT

--COUNT(*)


	'https://fl.ru/users/' || u.login || '/',
	'https://fl.ru/siteadmin/bill/?login=' || u.login || '/',

	a.uid,
	a.sum,
	ao.balance AS history
	
	
FROM account_operations AS ao 
INNER JOIN
(
	SELECT
		MAX(ao.id) AS id
	FROM account_operations AS ao 
	GROUP BY ao.billing_id
	--LIMIT 10;
) AS q ON q.id = ao.id
INNER JOIN account AS a ON a.id = ao.billing_id
INNER JOIN users AS u ON u.uid = a.uid
WHERE a.sum <> ao.balance
ORDER BY ao.balance DESC
LIMIT 10;
*/



print_r($login);
exit;









$user = new users();
$user->GetUser($login);

if ($user->uid < 0) {
    print_r('User not found!');
    exit;
}

$list = $DB->rows("
    SELECT
    
        a.sum,
        ao.id,
        ao.billing_id,
        ao.ammount,
        ao.balance
    FROM account_operations AS ao 
    INNER JOIN account AS a ON a.id = ao.billing_id
    WHERE a.uid = ?i 
    ORDER BY ao.id
", $user->uid);

if ($list) {
    foreach ($list as $idx => $item) {
        $prev = $idx - 1;
        if (isset($list[$prev]) /*&& $list[$prev]['ammount'] < 0*/) {
            
            $prev_balance = round($list[$prev]['balance'],6);
            $check = round($item['balance'] - $item['ammount'],6);
            
            if (($prev_balance - $check) != 0) {
                
                print_r("{$list[$prev]['id']}, {$item['id']},");echo "\n";

                
                $fix =  $prev_balance - $check;

                
                print_r("fix = {$fix}");echo "\n";
                
                $last = end($list);
                $last_balance = round($last['balance'],6);
                $fix_balance_check = $last_balance + $fix;
                
                print_r("fix_balance_check = {$fix_balance_check}");echo "\n";
                
                if ($fix_balance_check == round($item['sum'],6)) {
                    print_r('fixed!');echo "\n\n";
                }
                
                
                
                //exit;
                
                
                if ($update) {
                    $DB->query("
                        UPDATE account_operations 
                        SET balance = balance + ?f 
                        WHERE billing_id = ?i AND id >= ?i", $fix, $item['billing_id'], $item['id']);
                }
                
                break;
                
                
                /*
                echo "\n";
                var_dump($prev_balance);
                echo "\n";
                var_dump($check);
                echo "\n";
		var_dump($prev_balance - $check);                
                
                break;
                */
            }
        }
    }
}