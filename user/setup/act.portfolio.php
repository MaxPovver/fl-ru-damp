<?php
if (!$_in_setup) {
    header ("HTTP/1.0 403 Forbidden"); 
    exit;
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

$prfs = new professions();

if(!is_object($user)) {
    $user = new freelancer();
    $user->GetUser($_SESSION['login']);
}

$is_exec_action = (isset($action) && $action == 'serv_change' && ($error_serv != ''));
$frm_serv_val = array(
    'tab_name_id'     => ( $is_exec_action ? floatval($tab_name_id) : $user->tab_name_id ),
    'exp'             => ( $is_exec_action ? floatval($exp) : $user->exp ),
    'cost_hour'       => ( $is_exec_action ? $cost_hour : $user->cost_hour ),
    'cost_month'      => ( $is_exec_action ? $cost_month : $user->cost_month ),
    'cost_type_hour'  => ( $is_exec_action ? $cost_type_hour : $user->cost_type_hour ),
    'cost_type_month' => ( $is_exec_action ? $cost_type_month : $user->cost_type_month ), 
    'text'            => ( $is_exec_action ? $text : $user->spec_text ), 
    'in_office'       => ( $is_exec_action ? $in_office : $user->in_office )
);


$specs_add = array();
if ($is_pro) {
    $specs_add = $prfs->GetProfsAddSpec(get_uid());
}

if (!empty($specs_add)) {
    $specs_add_array = array();
    for ($si = 0; $si < sizeof($specs_add); $si++) {
        $specs_add_array[$si] = professions::GetProfNameWP($specs_add[$si], ' / ');
    }
    $specs_add_string = join(", ", $specs_add_array);
} else {
    $specs_add_string = "Нет";
} 