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
global $DB;

$update = false;

$sql_3 = "SELECT DISTINCT ON (a.id) a.id as bill_id, bq.* 
    FROM bill_queue AS bq
    INNER JOIN account a ON bq.uid = a.uid
    WHERE 
    bq.create_time >= '2015-01-15'::timestamp
    AND bq.op_code = 135
    AND bq.status = 'new'
    ORDER BY a.id, bq.create_time DESC";

$orders = $DB->rows($sql_3);

$count = 0;
foreach ($orders as $order) {
    //Проверяем, была ли покупка после этой операции
    $sql_4 = 'SELECT id FROM bill_queue WHERE uid = ?i AND create_time > ?';
    $operation_after = $DB->val($sql_4, $order['uid'], $order['create_time']);
    
    if (!$operation_after) {
        $sql_5 = "SELECT * FROM account_operations WHERE billing_id = ?i AND op_date > ? AND op_code = 12";
        $ac_op = $DB->row($sql_5, $order['bill_id'], $order['create_time']);

        if ($ac_op) {
            $date = new DateTime($ac_op['op_date']);
            $date->modify('+1 minutes');
            $op_date = $date->format("Y-m-d H:i:s");

            //Пока не обновляем, а просто смотрим данные
            if ($update) {
                $id = $DB->insert('account_operations', array(
                        'billing_id'  => $ac_op['billing_id'],
                        'op_code'     => 135,
                        'ammount'     => 0,
                        'descr'       => '',
                        'comments'    => $order['comment'],
                        'payment_sys' => 0,
                        'trs_sum'     => $order['ammount'],
                        'op_date'     => $op_date
                    ), 'id');

                $DB->update('bill_queue', array('status' => 'complete'), 'id = ?i', $order['id']);
            }
            $count++;
            echo 'Operation: '.$order['id'] . '. Uid: ' . $order['uid'] . '
';
        }
    }
}

echo "
Operations for update = $count.
";