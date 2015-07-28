<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X4
	$res = $wmxi->X4(
		PRIMARY_PURSE,  # номер кошелька  для оплаты на который выписывался счет
		0,              # номер счета (в системе WebMoney)
		0,              # номер счета
		DATE_A,         # минимальное время и дата создания счета
		DATE_B          # максимальное время и дата создания счета
	);

	print_r($res->toObject());


?>