<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X10
	$res = $wmxi->X10(
		ANOTHER_WMID,  # WM-идентификатор, которому был выписан счет(счета) на оплату
		0,             # номер счета (в системе WebMoney)
		DATE_A,        # минимальное время и дата создания счета
		DATE_B         # максимальное время и дата создания счета
	);

	print_r($res->toObject());


?>