<?

define('NO_CSRF', 1);

	if (!$_POST['LMI_PAYEE_PURSE']) {
		header("HTTP/1.0 404 Not Found");
		include $_SERVER["DOCUMENT_ROOT"]."/404.php";
		die;
	}
//ob_start();
	$rpath = "../";
	$allow_fp = 1;
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wmpay.php");
	$account = new wmpay;
	if ($_POST['LMI_PREREQUEST'] == 1){
		$error = $account->prepare($_POST['LMI_PAYEE_PURSE'], $_POST['PAYMENT_BILL_NO'], $_POST['LMI_PAYMENT_AMOUNT'], $_POST['OPERATION_TYPE'], $_POST['OPERATION_ID']);
	} else {
		$error = $account->checkdeposit($_POST['LMI_PAYEE_PURSE'], $_POST['LMI_PAYMENT_AMOUNT'], $_POST['LMI_PAYMENT_NO'],
			$_POST['LMI_SYS_INVS_NO'], $_POST['LMI_SYS_TRANS_NO'], $_POST['LMI_PAYER_PURSE'], $_POST['LMI_PAYER_WM'],
			$_POST['LMI_SYS_TRANS_DATE'], $_POST['LMI_HASH'], $_POST['LMI_MODE'], $_POST['PAYMENT_BILL_NO'],
			$_POST['OPERATION_TYPE'], $_POST['OPERATION_ID']);
	}
/*print_r($_POST);
print $error;
$info = ob_get_contents();
ob_end_clean();
$fp = fopen("test", 'a');
fwrite($fp, $info);*/

 if ($error) { print $error; } else { print "YES"; }
 ?>