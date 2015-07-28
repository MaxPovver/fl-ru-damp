<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X5
	$res = $wmxi->X5(
		1,     # уникальный номер платежа в системе учета WebMoney
		'123'  # код протекции сделки
	);

	print_r($res->toObject());

?>