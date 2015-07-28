<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X18
	$res = $wmxi->X18(
		PRIMARY_WMID,     # ВМ-идентификатор получателя или подписи
		PRIMARY_PURSE,    # ВМ-кошелек получателя платежа
		1,                # номер платежа
		'qw3t4WQ$CTtcA',  # секретное слово
	);

	print_r($res->toObject());


?>