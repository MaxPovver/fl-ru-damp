<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X16
	$res = $wmxi->X16(
		PRIMARY_WMID,   # WMID кошелька
		'Z',            # тип кошелька
		'Ещё один WMZ'  # название кошелька
	);

	print_r($res->toObject());


?>