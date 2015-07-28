<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
get_uid();
if (!get_uid()) { include("../fbd.php"); exit;}
	
$action = __paramInit('string', null, 'action');
$tr_id  = intval($_REQUEST['transaction_id']);

if($action=='buy') {
    require_once($_SERVER['DOCUMENT_ROOT']."/classes/wizard/wizard_billing.php");
    $operation = $_POST['operation'];
    $operation = array_map("intval", $operation);
    $wizard_operation = new wizard_billing($_SESSION['uid']);
    $order_id = $wizard_operation->paymentOptions($operation, $tr_id);
}

if (!$order_id) {
   header("Location: /bill/fail/");
   exit;
} else {
   header("Location: /bill/success/");
   exit;
}

?>