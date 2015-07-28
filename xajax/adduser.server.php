<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/adduser.common.php");

function AddUser($login){
	$objResponse = new xajaxResponse();
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
	$user = new users;
	$user->GetUser($login);
	if ($user->login && !is_emp($user->role) && !$user->is_banned && $user->active == 't') {
	$inner = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
<tr>
	<td width=\"60\">".view_avatar($user->login, $user->photo)."</td>
	<td valign=\"top\"><a href=\"/users/".$user->login."\" class=\"frlname11\" title=\"".$user->uname." ".$user->usurname."\">".$user->uname." ".$user->usurname." [".$user->login."]"."</a> 
	<a href=\"javascript:reload_form();\" class=\"blue\">изменить</a></td>
</tr>
</table>";
	$objResponse->assign("usersel","innerHTML",$inner);
	$objResponse->script("document.getElementById('next').disabled = false;document.getElementById('login').value = '".$login."';");
	} else {
		$objResponse->script("reload_form();
		 document.getElementById('usersel').innerHTML = document.getElementById('usersel').innerHTML + '".ref_scr(view_error("Такого фрилансера не существует"))."';
		 document.getElementById('elogin').value = '".$login."';");
	}
	return $objResponse;
}

$xajax->processRequest();
?>
