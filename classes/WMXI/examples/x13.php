<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X13
	$res = $wmxi->X13(
		1  # номер транзакции
	);

	print_r($res->toObject());


?>