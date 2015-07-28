<?php

require_once $_SERVER['DOCUMENT_ROOT']."/xajax/masssending.common.php";

function MasssendingEdit($id) {
	$response = new xajaxResponse();
	if(hasPermissions('masssending')) {
		require_once $_SERVER['DOCUMENT_ROOT']."/classes/masssending.php";
		$mass = masssending::Get($id);
		if($mass[0]['id']) {
			$mass = $mass[0];
			$response->assign('popup_masssending_edit_id', 'value', $mass['id']);
			$response->assign('popup_masssending_edit_txt', 'value', htmlspecialchars_decode($mass['msgtext'], ENT_QUOTES));
			$response->script('$("popup_masssending_edit").setStyle("display", "block");');
		}
	}
	return $response;
}

function MasssendingSave($id, $txt) {
	$response = new xajaxResponse();
	if(hasPermissions('masssending')) {
		require_once $_SERVER['DOCUMENT_ROOT']."/classes/masssending.php";
		masssending::UpdateText($id, $txt);
		$mass = masssending::Get($id);
		$response->assign('mass_txt_'.$id, 'innerHTML', reformat($mass[0]['msgtext'],30,0,0,1));
	}
	return $response;
}

function GetCities($country, $city) {
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/city.php';
	$response = new xajaxResponse();
	$cities = city::GetCities( intval($country) );
	/*$html = '<option value="0">Все города</option>';
	foreach ($cities as $id=>$val) {
		$html .= '<option value="'.$id.'">'.htmlspecialchars($val).'</option>';
	}*/
	$script  = "document.getElementById('cities').options.length = 0; \n";
	$script .= "document.getElementById('cities').options[0] = new Option('Все города', 0); \n";
	$i = 1;
	foreach ($cities as $id=>$val) {
		$script .= "document.getElementById('cities').options[{$i}] = new Option('".htmlspecialchars($val)."', {$id}); \n";
		$i++;
	}
	$response->assign('cities', 'innerHTML', $html);
	$response->assign('cities', 'disabled', FALSE);
	$script .= "document.getElementById('btnAddLocation').onclick = function() { locations.add(); return false; }; \n";
	if ($city) {
		$script .= "
			for (var i=0; i<document.getElementById('cities').options.length; i++) {
				if (document.getElementById('cities').options[i].value == {$city}) {
					document.getElementById('cities').selectedIndex = i;
					break;
				}	
			} \n
		";
	}
	$script .= "if (spam.busy > 1) { spam.busy = 0;	spam.send(); } spam.busy = 0;";
	$response->script($script);
	return $response;
}

function DelFile($fid) {
	session_start();
	$response = new xajaxResponse();
	if (!empty($_SESSION['masssending']['files'])) {
	    $size = 0;
		$tmp  = array();
		foreach ($_SESSION['masssending']['files'] as $file) {
			if ($file['id'] != $fid) $tmp[] = $file;
			if ($file['id'] == $fid) $size  = $file['size'];
		}
		$_SESSION['masssending']['files']        = $tmp;
		$_SESSION['masssending_total_filesize'] -= $size;
	}
	return $response;
}

function Calculate($params) {
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/masssending.php';
	$masssending = new masssending;
	session_start();
	$uid = get_uid(FALSE);
	$params['savetime'] = mktime();
	$params['msg'] = stripslashes($params['msg']);
	if (!empty($_SESSION['masssending']['files'])) $params['files'] = $_SESSION['masssending']['files'];
	$_SESSION['masssending'] = $params; //serialize($params);
	$response = new xajaxResponse();
	// преобразуем js массив в формат для masssending::Calculate()
	if (!empty($params['locations']) && is_array($params['locations'])) {
		$tmp = array();
		foreach ($params['locations'] as $value) {
			if (!empty($value['country']['id'])) {
				$tmp[] = intval($value['country']['id']).':'.intval($value['city']['id']);
			}
		}
		$params['locations'] = $tmp;
	}

	if (!empty($params['professions']) && is_array($params['professions'])) {
		$tmp = array();
		foreach ($params['professions'] as $value) {
			if (!empty($value['group']['id'])) {
				$tmp[] = intval($value['group']['id']).':'.intval($value['profession']['id']);
			}
		}
		$params['professions'] = $tmp;
	}
	
	if (!empty($params['costs']) && is_array($params['costs'])) {
		$params['cost_from'] = array();
		$params['cost_to'] = array();
		$params['cost_period'] = array();
		$params['cost_type'] = array();
		foreach ($params['costs'] as $value) {
			if (!empty($value['cost_from']) || !empty($value['cost_to'])) {
				$params['cost_from'][] = $value['cost_from'];
				$params['cost_to'][] = $value['cost_to'];
				$params['cost_period'][] = $value['cost_period'];
				$params['cost_type'][] = $value['cost_type'];
			}
		}
		unset($params['costs']);
	}
    $calc = $masssending->Calculate($uid, $params);
	// преобразуем обратно
	$locations = array();
	foreach ($calc['locations'] as $location) {
		$locations[] = "{ country: {$location['country']}, city: {$location['city']}, count: {$location['count']}, cost: {$location['cost']} }";
	}
	$professions = array();
	foreach ($calc['professions'] as $profession) {
		$professions[] = "{ group: {$profession['group']}, profession: {$profession['id']}, count: {$profession['count']}, cost: {$profession['cost']} }";
	}
	$response->script("
		spam.calc = { count: {$calc['count']}, cost: {$calc['cost']} };
		spam.calc.pro = { count: {$calc['pro']['count']}, cost: {$calc['pro']['cost']} };
		var tmp = [ ".implode($locations, ',')." ];
		for (var i=0; i<locations.values.length; i++) {
			locations.values[i].count = 0;
			locations.values[i].cost = 0;
			for (var j=0; j<tmp.length; j++) {
				if (locations.values[i].country.id == tmp[j].country && locations.values[i].city.id == tmp[j].city) {
					locations.values[i].count = tmp[j].count;
					locations.values[i].cost = tmp[j].cost;
					break;
				}
			}
		}
		var tmp = [ ".implode($professions, ',')." ];
		for (var i=0; i<professions.values.length; i++) {
			professions.values[i].count = 0;
			professions.values[i].cost = 0;
			for (var j=0; j<tmp.length; j++) {
				if (professions.values[i].group.id == tmp[j].group && professions.values[i].profession.id == tmp[j].profession) {
					professions.values[i].count = tmp[j].count;
					professions.values[i].cost = tmp[j].cost;
					break;
				}
			}
		}
		spam.recalculation();
	");
	return $response;
}

/**
 * подсчет получателей при переходе из поиска пользователей
 * @param string $query строка параметров из URL
 */
function CalculateFromSearch ($query) {
	$response = new xajaxResponse();
	session_start();
    // парсим строку параметров из URL
    parse_str($query, $param);
    
	$uid = get_uid(FALSE);
    
    // стоимость рассылки
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/masssending.php';
	$masssending = new masssending;
    $calc = $masssending->CalculateFromSearch($uid, $param);
    
    $response->script("
		spam.calc = { count: {$calc['count']}, cost: {$calc['cost']} };
		spam.calc.pro = { count: {$calc['pro']['count']}, cost: {$calc['pro']['cost']} };
		spam.recalculation();
	");
	return $response;
}


$xajax->processRequest();

?>