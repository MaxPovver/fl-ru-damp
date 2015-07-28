<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X7
	$res = $wmxi->X7(
		ANOTHER_WMID,  # WM-идентификатор клиента, которого необходимо аутентифицировать
		'00FF',        # подпись строки, передаваемой в параметре testsign\plan, сформированная клиентом, которого необходимо аутентифицировать
		'123'          # строка, которую должен был подписать клиент
	);

	print_r($res->toObject());

?>