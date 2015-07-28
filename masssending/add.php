<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/masssending.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/country.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/city.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/professions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/project_exrates.php';

session_start();
if(!($uid = get_uid())) {
	header ("Location: /fbd.php");
	exit;
}

$masssending = new masssending();

$countries = country::GetCountries(TRUE);
$prof_groups = professions::GetAllGroupsLite(TRUE);
$professions = professions::GetProfList();
$exrates = project_exrates::GetAll();



if (!empty($_POST)) {

    $frm = $_POST;

    //$params['msg'] = stripslashes($frm['msg']);
    $params['msg'] = "Здравствуйте!\n\nПриглашаю ознакомиться с проектом '".change_q_x(stripslashes($frm['title']), FALSE, FALSE, 'b|i|p|ul|li|s|h[1-6]{1}', FALSE, FALSE)."' ".str_replace('www.n.fl.ru', 'n.fl.ru',$host).$frm['link']." \n\n\n".LenghtFormatEx(change_q_x(stripslashes($frm['msg']), FALSE, FALSE, 'b|i|p|ul|li|s|h[1-6]{1}', FALSE, FALSE),300);
    $params['max_users'] = intval($_POST['max_users']);
    $params['max_cost'] = intval($_POST['max_cost']);

    $params['is_pro'] = stripslashes($frm['pro']);
    $params['favorites'] = stripslashes($frm['favorites']);
    $params['free'] = stripslashes($frm['free']);
    $params['sbr'] = stripslashes($frm['bs']);
    $params['portfolio'] = stripslashes($frm['withworks']);
    $params['inoffice'] = stripslashes($frm['office']);
    $params['opi_is_verify'] = stripslashes($frm['ver']);
    $tmp = array();
    if($frm['mass_location_columns'][0]!='0' || $frm['mass_location_columns'][1]!='0') {
        $tmp[] = intval($frm['mass_location_columns'][0]).':'.intval($frm['mass_location_columns'][1]);
        $params['locations'] = $tmp;
    }
    if($frm['f_cats']) {
        $frm['f_cats'] = preg_replace("/,$/", "", $frm['f_cats']);
        $acats = explode(",", $frm['f_cats']);
        $cats_data = array();
        foreach($acats as $v) {
            $v = preg_replace("/^mass_cat_span_/", "", $v);
            $c = explode("_", $v);
            if($c[1]==0) {
                $sql = "SELECT prof_group FROM professions WHERE id=?i";
                $p = $DB->val($sql, $c[0]);
                $cats_data[] = $p.":".$c[0];
            } else {
                $cats_data[] = $c[0].":0";
            }
        }
    }
    $params['professions'] = $cats_data;




		if ($calc = $masssending->Add($uid, $params)) {
			$masssending->ClearTempFiles(session_id());
			$masssending->Accept($calc['massid']);
			header('Location: /bill/orders/');
			exit;
		}

	}
	


?>