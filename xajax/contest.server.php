<?

$rpath = "../";
require_once $_SERVER['DOCUMENT_ROOT']."/xajax/contest.common.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

/**
 * Добавление комментария
 * @param   integer        id предложения
 * @param   string         комментарий
 * @param   integer        id комментария на который дается ответ или 0, если комментарий первого уровня вложенности
 * @param   integer        уровень вложенности комментария
 * @return  xajaxResponse
 */
function CreateComment($oid, $comment, $reply, $level) {
	global $contest, $stop_words;
	session_start();
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/contest.php";
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
	$objResponse = new xajaxResponse();
	if (!trim($comment)) {
		$objResponse->alert('Комментарий не может быть пустым');
		return $objResponse;
	}
	if (!($uid = $_SESSION['uid'])) {
		$objResponse->call("comment.reset");
		$objResponse->alert('Сначала вам необходимо авторизоваться');
		return $objResponse;
	}
	if (!($offer = contest::GetOfferRow($oid))) {
		$objResponse->call("comment.reset");
		$objResponse->alert('Несуществующее предложение');
		return $objResponse;
	}
	$contest = new contest($offer['project_id'], $uid, is_emp(), FALSE, hasPermissions('projects'));
    
    // если пользователь не про или не верифицирован, то есть смысл проверить, может быть конкурс только для про или только для верифицированных
    $prj = new projects();
    $project = $prj->GetPrjCust($offer['project_id']);
    if ((!is_pro() || !is_verify()) && $project['user_id'] != get_uid() && !hasPermissions('projects')) {
        if ($project['pro_only'] == 't' && !is_pro()) {
            $objResponse->call("comment.reset");
            $objResponse->alert("Данная функция доступна только пользователям с аккаунтом PRO.");
            return $objResponse;
        } elseif ($project['verify_only'] == 't' && !is_verify()) {
            $objResponse->call("comment.reset");
            $objResponse->alert("Данная функция доступна только верифицированным пользователям.");
            return $objResponse;
        }
    }
        
	$offer = $contest->GetOffer($oid);
	$comment = change_q_x(antispam(substr(rtrim(ltrim($comment, "\r\n")), 0, 5000)), false, true, 'b|br|i|p|ul|li|cut|h[1-6]{1}', false, false);
	if ($error = $contest->CreateComment($oid, $comment, $reply)) {
		$objResponse->call("comment.reset");
		$objResponse->alert($error);
	} else {
		define('FUNCTIONS_ONLY', TRUE);
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
        $stop_words = new stop_words( hasPermissions('projects') );
        
		require_once $_SERVER['DOCUMENT_ROOT']."/projects/contest.php";
		require_once $_SERVER['DOCUMENT_ROOT']."/classes/smail.php";
        
		$contest->GetOffer($oid);
		if ($contest->offer['user_id'] != $uid) {
			//$smail = new smail;
			//$smail->ContestNewComment($uid, $contest->offer['id']);
		}
		$contest->is_owner = ($offer['owner_id'] == $uid);
		$comment = $contest->GetComment($contest->new_cid);
		$comment = array($comment);
        $c_blocked = ($contest->offer['comm_blocked']=='t' && $uid!=$contest->offer['user_id'] && $uid!=$contest->offer['owner_id']);
		$objResponse->call("comment.added", $comment[0]['id'], comments($contest->offer['project_id'], "", $comment, $c_blocked, FALSE, $level));
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
	}
	return $objResponse;
}


/**
 * Редактирование комментария
 * @param   integer        id комментария
 * @param   string         комментарий
 * @return  xajaxResponse
 */
function ChangeComment($cid, $comment) {
	global $contest, $stop_words;
	session_start();
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/contest.php";
    require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
	$objResponse = new xajaxResponse();
	if (!trim($comment)) {
		$objResponse->alert('Комментарий не может быть пустым');
		return $objResponse;
	}
	if (!($uid = $_SESSION['uid'])) return $objResponse;
	$contest = new contest(0, $uid, is_emp(), FALSE, hasPermissions('projects'));
    
    // если пользователь не про или не верифицирован, то есть смысл проверить, может быть конкурс только для про или только для верифицированных
    $project = contest::getProjectByCommentID($cid);
    if ((!is_pro() || !is_verify()) && $project['user_id'] != get_uid() && !hasPermissions('projects')) {
        if ($project['pro_only'] == 't' && !is_pro()) {
            $objResponse->call("comment.reset");
            $objResponse->alert("Данная функция доступна только пользователям с аккаунтом PRO.");
            return $objResponse;
        } elseif ($project['verify_only'] == 't' && !is_verify()) {
            $objResponse->call("comment.reset");
            $objResponse->alert("Данная функция доступна только верифицированным пользователям.");
            return $objResponse;
        }
    }
    
	if (!($offer = $contest->GetOffer($oid))) return $objResponse;
	$contest->pid = $offer['project_id'];
	$comment = change_q_x(antispam(substr(rtrim(ltrim($comment, "\r\n")), 0, 5000)), false, true, 'b|br|i|p|ul|li|cut|h[1-6]{1}', false, false);
	if ($error = $contest->ChangeComment($cid, $comment)) {
		$objResponse->alert($error);
	} else {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
        $stop_words = new stop_words( hasPermissions('projects') );
        
        $html     = reformat( stripslashes($stop_words->replace($comment)), 30, 0, 0, 1 );
        $original = reformat( stripslashes($comment), 30, 0, 0, 1 );
        
		$objResponse->call("comment.changed", $html, $original, dateFormat('[изменен: d.m.Y | H:i]', $comments[$i]['modified']));
	}
	return $objResponse;
}


/**
 * Заполняет элемент на странице комментарием
 * @param   integer        id комментария
 * @param   string         DOM ID с Textarea
 * @return  xajaxResponse
 */
/*function GetComment($cid, $domId) {
	$comment = $contest->GetComment(intval($cid));
	$objResponse->assign($domId, 'disabled', '');
	$objResponse->assign($domId, 'value', $comment);
	return $objResponse;
}*/

/**
 * Удаление комментария
 * @param   integer        id комментария
 * @return  xajaxResponse
 */
function DeleteComment($cid) {
	global $contest;
	session_start();
	$cid = intval($cid);
	$objResponse = new xajaxResponse();
	if (!($uid = $_SESSION['uid'])) return $objResponse;
	define('FUNCTIONS_ONLY', TRUE);
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/contest.php";
	require_once $_SERVER['DOCUMENT_ROOT']."/projects/contest.php";
	$contest = new contest(0, $uid, is_emp(), FALSE, hasPermissions('projects'));
	if (!($offer = $contest->GetOffer($oid))) return $objResponse;
	$contest->pid = $offer['project_id'];
	if ($error = $contest->DeleteComment($cid)) {
		$objResponse->alert($error);
		return $objResponse;
	}
	$comment = $contest->GetComment($cid);
	$contest->GetOffer($comment['offer_id']);
	$objResponse->call("comment.deleted", comment_options($contest->offer['project_id'], $comment, $contest->offer['comm_blocked'] == 't', FALSE, 0, 0));
	return $objResponse;
}


/**
 * Восстановление комментария
 * @param   integer        id комментария
 * @return  xajaxResponse
 */
function RestoreComment($cid) {
	global $contest;
	session_start();
	$cid = intval($cid);
	$objResponse = new xajaxResponse();
	if (!($uid = $_SESSION['uid'])) return $objResponse;
	define('FUNCTIONS_ONLY', TRUE);
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/contest.php";
	require_once $_SERVER['DOCUMENT_ROOT']."/projects/contest.php";
	$contest = new contest(0, $uid, is_emp(), FALSE, hasPermissions('projects'));
	if ($error = $contest->RestoreComment($cid)) {
		$objResponse->alert($error);
		return $objResponse;
	}
	$comment = $contest->GetComment($cid);
	$contest->GetOffer($comment['offer_id']);
	$objResponse->call("comment.restored", comment_options($contest->offer['project_id'], $comment, $contest->offer['comm_blocked'] == 't', 0, 0));
	return $objResponse;
}


/**
 * Удаление предложения
 * @param   integer        id предложения
 * @return  xajaxResponse
 */
function DelOffer($oid) {
	session_start();
	$uid = $_SESSION['uid'];
	$objResponse = new xajaxResponse();
	if (!($is_moder = hasPermissions('projects'))) return $objResponse;
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/contest.php";
	$contest = new contest(0, $uid, is_emp(), FALSE, hasPermissions('projects'));
	if ($contest->DeleteOffer($oid)) $objResponse->assign("offer-$oid", 'style.display', '');
	return $objResponse;
}


/**
 * Удаление предложения(пометка как удаленное)
 * 
 * @param   integer $prj_id     ID проекта
 * @param   integer $offer_id   ID предложения
 * @return  xajaxResponse
 */
function RemoveOffer($prj_id, $offer_id) {
    session_start();
    $objResponse = new xajaxResponse();
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/contest.php";
	$contest = new contest(0, $uid, is_emp(), FALSE, hasPermissions('projects'));
    $contest->RemoveOffer($offer_id);
    $objResponse->script("$('comment{$offer_id}').hide()");
    return $objResponse;
}

/**
 * Восстановление предложения
 * 
 * @param   integer $prj_id     ID проекта
 * @param   integer $offer_id   ID предложения
 * @return  xajaxResponse
 */
function RestoreOffer($prj_id, $offer_id) {
    session_start();
    $objResponse = new xajaxResponse();
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/contest.php";
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
    
    // если пользователь не про или не верифицирован, то есть смысл проверить, может быть конкурс только для про или только для верифицированных
    $prj = new projects();
    $project = $prj->GetPrjCust($prj_id);
    if ((!is_pro() || !is_verify()) && $project['user_id'] != get_uid() && !hasPermissions('projects')) {
        if ($project['pro_only'] == 't' && !is_pro()) {
            $objResponse->alert("Данная функция доступна только пользователям с аккаунтом PRO.");
            return $objResponse;
        } elseif ($project['verify_only'] == 't' && !is_verify()) {
            $objResponse->alert("Данная функция доступна только верифицированным пользователям.");
            return $objResponse;
        }
    }
    
	$contest = new contest(0, $uid, is_emp(), FALSE, hasPermissions('projects'));
    $contest->RestoreOffer($offer_id);
    $objResponse->script("$('comment{$offer_id}').show()");
    return $objResponse;
}

/**
 * Установка/снятие кандидата
 * @param   integer        id предложения
 * @return  xajaxResponse
 */
function Candidate($oid) {
	global $DB;
	session_start();
	$uid = $_SESSION['uid'];
	$oid = intval($oid);
	$objResponse = new xajaxResponse();
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/contest.php";
	$row = $DB->row("
		SELECT offers.*, users.login, projects.user_id AS owner_id, projects.closed 
		FROM projects_contest_offers AS offers 
		JOIN projects ON projects.id = offers.project_id 
		JOIN users ON users.uid = offers.user_id 
		WHERE offers.id = ? AND ((SELECT COUNT(*) FROM projects_contest_offers WHERE project_id = projects.id AND (position IS NOT NULL AND position > 0)) = 0)
	", $oid);
	if (!$row) {
		$objResponse->alert('Несуществующее предложение');
		return $objResponse;
	}
	if (!is_emp() || $uid != $row['owner_id']) {
		$objResponse->alert('Несуществующее предложение!');
		return $objResponse;
	}
	if ($row['closed'] == 't') {
		$objResponse->alert('Проект закрыт!');
		return $objResponse;
	}
	$contest = new contest(0, $uid, is_emp(), TRUE, hasPermissions('projects'));;
	if ($error = $contest->Candidate($oid)) {

		$objResponse->alert($error);
		return $objResponse;
	}
	if ($row['selected'] == 't') {
		$objResponse->call("candidate.deleted", $row['user_id'], $row['login']);
	} else {
		$objResponse->call("candidate.added", $row['user_id'], $row['login']);
	}
	return $objResponse;
}

$xajax->processRequest();

?>
