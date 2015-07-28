<?

$action = trim($_POST['action']);
$email = trim($_POST['email']);

if ($action == "send"){
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
	if (preg_match( "/^[-^!#$%&'*+\/=?`{|}~.\w]+@[-a-zA-Z0-9]+(\.[-a-zA-Z0-9]+)+$/", $email)){
		$sm = new smail();
		$error = $sm->remind($email);
	}
	else $error = "Поле заполнено некорректно";
}

$no_banner = 1;
$header = "header.php";
$footer = "footer.html";
$content = "wrongpass_inner.php";

include ("template.php");

?>
