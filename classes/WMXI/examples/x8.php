<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X8
	$res = $wmxi->X8(
		ANOTHER_WMID,  # WM-идентификатор
		ANOTHER_PURSE  # кошелек
	);

	print_r($res->toObject());


?>