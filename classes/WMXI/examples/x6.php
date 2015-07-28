<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X6
	$res = $wmxi->X6(
			ANOTHER_WMID,                                      # WM-идентификатор получателя сообщения
			'Тестовый заголовок',                              # тема сообщения
			"Текст многострочного\n".
			"сообщения <b>с тегами</b>\n".
			"<q>ABCDEFGHIJKLMNOPQRSTUVWXYZ</q>\n".
			"АБВГДЕЁЄЖЗИЙІЇКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ".
			""			# текст сообщения
	);

	print_r($res->toObject());

?>