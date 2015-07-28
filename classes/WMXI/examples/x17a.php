<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X17
	$res = $wmxi->X17a(
		'Тестовый контракт',  # Название контракта
		1,                    # Тип контракта
		'Текст контракта',    # Текст контракта
		array()               # Список WMID пользователей, которым разрешается акцептовывать данный контракт
	);

	print_r($res->toObject());


?>