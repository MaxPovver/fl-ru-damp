<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X1
	$res = $wmxi->X1(
		1,                                # номер счета
		ANOTHER_WMID,                     # WMID покупателя
		PRIMARY_PURSE,                    # кошелек  для оплаты
		0.11,                             # сумма счета
		'Описание товара',                # описание товара или услуги
		'Мой адрес - не дом и не улица',  # адрес доставки товара
		0,                                # срок протекции сделки
		0                                 # срок оплаты счета
	);

	print_r($res->toObject());


?>