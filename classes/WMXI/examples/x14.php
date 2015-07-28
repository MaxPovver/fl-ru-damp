<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X14
	$res = $wmxi->X14(
		1,   # номер транзакции
		0.1  # сумма транзакции
	);

	print_r($res->toObject());


?>