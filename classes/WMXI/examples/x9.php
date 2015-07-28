<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X9
	$res = $wmxi->X9(
		PRIMARY_WMID  # WM-идентификатор
	);

	print_r($res->toObject());

?>