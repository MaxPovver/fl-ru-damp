<?
    define('NO_CSRF', 1);
	$allow_fp = 1;
	$rpath = "../";
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/osmppay.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
	if ( !matchCIDR($_SERVER['HTTP_X_FORWARDED_FOR'],"79.142.16.0/20")
	     && !in_array($_SERVER['HTTP_X_FORWARDED_FOR'], array('91.142.84.91', '91.142.84.102', '91.142.84.103')) )
	{
	    header("HTTP/1.1 404 Not Found");
	    exit;
	}
	$account = new osmppay;
	$op_id = 0;
	$result = 0; 
	if ($_GET['command'] === "check"){
		if ($_GET['account'] && $_GET['txn_id'] && $_GET['sum'])
			$error = $account->prepare($result, $_GET['account'], $_GET['txn_id'], $_GET['sum']);
		else {
			$result = 300;
			$error = "Неполный запрос";
		}
		$comment = ($error)?$error:"Аккаунт найден";
	} elseif ($_GET['command'] === "pay") {
		$sum = $_GET['sum'];
		if($sum && $_GET['account'] && $_GET['txn_id'] && $_GET['txn_date']) {
			$error = $account->checkdeposit($op_id, $result, $sum, $_GET['account'], $_GET['txn_id'], $_GET['txn_date']);
			if ($error) {
				$result = 300;
				$error = "Неполный запрос";
			}
        } else {
			$result = 300;
			$error = "Неполный запрос";
		}
		$comment = ($error)?$error:"Пополнение счета успешно завершено";
	} else {
		$result = 300;
		$comment = "Введите запрос";
	}
	$log = new log('osmp/%d%m%Y.log');
	$log->writeln('----- ' . date('d.m.Y H:i:s'));
    $log->writeln("Result: {$result}");
    $log->writeln("Comment: {$comment}");
	$log->writevar($_GET);
	$log->writeln();


//header('Content-type: application/xml; charset="UTF-8"',true);
print("<?xml version=\"1.0\" encoding=\"UTF-8\"?>");
?>
<response>
<osmp_txn_id><?=htmlspecialchars($_GET['txn_id'])?></osmp_txn_id>
<? if ($op_id) { ?><prv_txn><?=$op_id ?></prv_txn><? } ?>
<? if ($sum) { ?><sum><?=$sum?></sum><? } ?>
<result><?=$result?></result>
<comment><?=win2utf($comment)?></comment>
</response>
