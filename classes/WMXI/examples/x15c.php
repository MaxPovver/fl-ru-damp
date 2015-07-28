<?php

	include('_header.php');

	# http://wiki.webmoney.ru/wiki/show/Interfeys_X15
	$res = $wmxi->X15c(
		ANOTHER_WMID,   # WMID
		PRIMARY_WMID,   # WMID
		PRIMARY_PURSE,  # кошелек
		1,              # атрибут inv
		1,              # атрибут trans
		1,              # атрибут purse
		1,              # атрибут transhist
		1,              # суточный лимит
		1,              # дневной лимит
		1,              # недельный лимит
		1               # месячный лимит
	);

	print_r($res->toObject());


?>