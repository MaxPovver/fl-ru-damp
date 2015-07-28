<?php

$g_page_id  = "0|32";
$stretch_page = true;
$showMainDiv  = true;
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/masssending.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/country.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/city.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/professions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/project_exrates.php';

/**
 * Форматированный вывод числа, например: 123 456,78
 *
 */
function format($number, $precition=2) {
	$number = (string) round($number, $precition);
	preg_match("/([0-9]+)(?:\.([0-9]+))?/", $number, $o);
	$piece = strlen($o[1]) % 3;
	$a = substr($o[1], 0, $piece);
	for ($i=$piece; $i<strlen($o[1]); $i=$i+3) {
		$a .= '&nbsp;'.substr($o[1], $i, 3);
	}
	if ($o[2]) $a .= ','.$o[2];
	return $a;
}

session_start();
$uid = get_uid();

if (isset($_GET['unset'])) {
	setcookie('mass-files');
	unset($_SESSION['masssending']);
}

$fromSearch = __paramInit('int', 'from_search', null, 0);
// если пришли сюда из поиска пользователей
if ($fromSearch === 2) {
    // количество найденых
    $searchCount = __paramInit('int', 'search_count', 'search_count', false);
}

$masssending = new masssending();

$countries = country::GetCountries(TRUE);
$prof_groups = professions::GetAllGroupsLite(TRUE);
$professions = professions::GetProfList();
$exrates = project_exrates::GetAll();

//print_r($_POST);
//die;

$dc = 0;
if (!empty($_GET['g'])) {
	if (preg_match("/^([0-9]+)\:([0-9]+)$/", $_GET['g'], $o)) {
		$dc = $_GET['g'];
		$dcg = $o[1];
		$dcp = $o[2];
	} else if (preg_match("/^[0-9]+$/", $_GET['g'])) {
		$dc = "{$_GET['g']}:0";
		$dcg = $_GET['g'];
		$dcp = 0;
	}
}
if (!empty($_GET['p'])) {
	if ($dcg = professions::GetProfField(intval($_GET['p']), 'prof_group')) {
		$dcp = intval($_GET['p']);
		$dc = "{$dcg}:{$dcp}";
	}
}

//echo '<pre>'; var_dump($_SESSION['r_masssending']); echo '</pre>';
$page_title = "Рассылка по каталогу - фриланс, удаленная работа на FL.ru";

if (isset($_GET['done'])) {

	$content = 'done.php';

} else {

	if (!empty($_POST)) {
		if(!$uid) {
			header('Location: /registration/?user_action=masssending');
			exit;
		}

		$params = $_POST;
		if (empty($params['locations'])) $params['locations'] = array();
		if (empty($params['professions'])) $params['professions'] = array();
		if ($params['country']) {
			$params['locations'][] = intval($params['country']).':'.intval($params['city']);
		}
		if ($params['prof_group']) {
			$params['professions'][] = intval($params['prof_group']).':'.intval($params['profession']);
		}
		// избавляемся от возможных дублей
		$tmp = array();
		foreach ($params['locations'] as $val) {
			if (!in_array($val, $tmp)) $tmp[] = $val;
		}
		$params['locations'] = $tmp;
		$tmp = array();
		foreach ($params['professions'] as $val) {
			if (!in_array($val, $tmp)) $tmp[] = $val;
		}
		$params['professions'] = $tmp;
		$params['msg'] = change_q_x(stripslashes($params['msg']), FALSE, FALSE, 'b|i|p|ul|li|s|h[1-6]{1}', FALSE, FALSE);

		if ($calc = $masssending->Add($uid, $params)) {
			$masssending->ClearTempFiles(session_id());
			setcookie('mass-files');
			unset($_SESSION['masssending']);
			header("Location: /masssending/masssending_done.php?count={$calc['count']}&cost={$calc['cost']}");
			exit;
		}

	}
	
	if (empty($_POST) || $masssending->error) {

		// восстанавливаем сессию, если она существует и ее время не вышло
		if (!empty($_SESSION['masssending'])) {
			$params = $_SESSION['masssending'];
			if (mktime() - $params['savetime'] > masssending::SESS_TTL) {
				unset($_SESSION['masssending']);
				$masssending->ClearTempFiles(session_id());
				$params = array();
			}
		} else {
			$params = array();
			$masssending->ClearTempFiles();
		}

		if ($dc) {
			$calc = $masssending->Calculate($uid, array('professions'=>array($dc)));
		} else {
			$calc = $masssending->Calculate($uid, array());
			$calc['count'] = 0;
			$calc['cost'] = 0;
            $calc['pro'] = array('count'=>0, 'cost'=>0);
		}
	
		$tariff = $masssending->GetTariff();
        $js_file = array( 'masssending.js' );
		$content = 'content.php';
		
	}
	
}
$css_file = array('masssending.css', '/css/nav.css' );
$footer = "../footer.html";
include '../template2.php';

?>