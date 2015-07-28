<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/callback.common.php");

function processForm($aFormValues) {
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
	global $session;
	session_start();
	get_uid(false);
	
	$objResponse = new xajaxResponse();

	//$pname = change_q($_POST['name'], true);
	$msg = change_q($aFormValues['calltext'], false, 0);
	//$mail = trim($_POST['mail']);
	//$attach = $_FILES['attach'];
	$login = ($_SESSION['login'])? $_SESSION['login'] : "Anonymous";
	$tn = 0;
    $f_name = "";
	$uid = get_uid(false);
	$usr=new users($uid);
	$pname=$_SESSION['name']." ".$_SESSION['surname'];
	$mail=$usr->GetField($uid, $error, "email");
	if (!$msg) {$alert['msg'] = "Поле заполнено некорректно"; $error_flag = 1;}
	if (!$error_flag) $error .= blogs::NewThread($uid, 3, 0, $name, $msg, $f_name, getRemoteIP(),1, $tn);
	if (!$error && !$error_flag) {
		$sm = new smail();
		$error .= $sm->NewFeedbackPost($pname, $msg, $mail, $_SESSION['login'], 4);
		$msg = $name = $mail = "";
		$info_msg = '<br><center><table class="view_info" border="0" cellpadding="2" cellspacing="0"><tbody><tr class="n_qpr"><td height="20"><img src="/images/ico_ok.gif" alt="" border="0" height="18" width="19"></td><td nowrap>Ваше сообщение отправлено</td></tr></tbody></table>';
		$objResponse->assign("calltext","value",'');
		$objResponse->assign("cbok","innerHTML",$info_msg);
		//$objResponse->assign("submitButton","disabled",false);
		$objResponse->assign("submitButton","value",'Отправить');
	}
	return $objResponse;
}
$xajax->processRequest();
?>