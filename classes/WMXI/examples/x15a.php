<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X15
	$res = $wmxi->X15a(
		PRIMARY_WMID  # WMID
	);

	print_r($res->toObject());


?>