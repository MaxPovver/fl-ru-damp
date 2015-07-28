<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X15
	$res = $wmxi->X15b(
		ANOTHER_WMID  # WMID
	);

	print_r($res->toObject());


?>