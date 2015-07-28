<?

define('NO_CSRF', 1);
$request = 'ok_verify=true'; 
	
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$request .= "&$key=$value";
}
	
$fsocket = false;
$result = false;
	
if ( $fp = @fsockopen('ssl://www.okpay.com', 443, $errno, $errstr, 30) ) {
	$fsocket = true;
} elseif ($fp = @fsockopen('www.okpay.com', 80, $errno, $errstr, 30)) {
	$fsocket = true;
}
	
if ($fsocket == true) {
	$header = 'POST /ipn-verify.html HTTP/1.0' . "\r\n" .
			  'Host: www.okpay.com'."\r\n" .
			  'Content-Type: application/x-www-form-urlencoded' . "\r\n" .
			  'Content-Length: ' . strlen($request) . "\r\n" .
			  'Connection: close' . "\r\n\r\n";
		
	@fputs($fp, $header . $request);
	$string = '';
	while (!@feof($fp)) {
		$res = @fgets($fp, 1024);
		$string .= $res;
		if ( $res == 'VERIFIED' || $res == 'INVALID' || $res == 'TEST') {
			$result = $res;
			break;
		}
	}
	@fclose($fp);
}
	
if ($result == 'VERIFIED') {
	if($_POST['ok_txn_status']=='completed') {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
	    $account = new account();
    	$account->GetInfo( $_POST['ok_f_uid'] );
	    $descr = "OKPAY #".$_POST['ok_txn_id']." на кошелек ".$_POST['ok_receiver_wallet']." OKPAYID: ".$_POST['ok_payer_id']." сумма - ".$_POST['ok_item_1_price'].",";
    	$descr .= " обработан ".$_POST['ok_txn_datetime'].", счет - ".$_POST['ok_f_bill_id'];
		$account->deposit($op_id, $_POST['ok_f_bill_id'], $_POST['ok_item_1_price'], $descr, 14, $_POST['ok_item_1_price'], 12);
	}
		
		
} elseif($result == 'INVALID') {
} elseif($result == 'TEST') {
} else {
	header("HTTP/1.0 404 Not Found");
	exit;
}
	
?>