<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
session_start();
get_uid();
error_reporting(E_ALL);
if (!get_uid()) { include("../fbd.php"); exit;}
	
$action = trim($_POST['action']);
$tr_id = intval($_REQUEST['transaction_id']);

if($action=='buy') {
    require_once($_SERVER['DOCUMENT_ROOT']."/classes/paid_advices.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/exrates.php");
    $exrates = new exrates();
    $EXR     = $exrates->GetAll();
    $paid_advice = new paid_advices();
    
    $id_advice = intval($_POST['id_advice']);
    $advice = $paid_advice->getAdvice($id_advice);
    
    if((int)$advice['id'] <= 0) {
        header("Location: /bill/fail/");
        exit;
    }
    
    $sum = round($advice['comm_sum'] / $EXR[13], 2);
    
    $order_id = $paid_advice->payedAdvice($advice['id'], $advice['user_to'], $tr_id, $sum, $advice['comm_sum']);
    
    if($advice['converted_id'] > 0 && $order_id) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");
        opinions::DeleteMsg($advice['user_from'], $advice['converted_id']);
    }
}

if (!$order_id) {
   header("Location: /bill/fail/");
   exit;
} else {
   header("Location: /bill/success/");
   exit;
}

?>