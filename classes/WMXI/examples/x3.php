<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X3
	$res = $wmxi->X3(
		PRIMARY_PURSE,  # номер кошелька для которого запрашивается операция
		0,              # номер операции (в системе WebMoney)
		0,              # номер перевода
		0,              # номер счета (в системе WebMoney) по которому выполнялась операция
		0,              # номер счета
		DATE_A,         # минимальное время и дата выполнения операции
		DATE_B          # максимальное время и дата выполнения операции
	);

	print_r($res->Sort());
#	print_r($res->Sort(false));
#	print_r($res->toArray());
#	print_r($res->toObject());
#	print_r($res->toString());


?>