<?php

	include('_header.php');

	$wmsigner = new WMSigner(WMID, $wmkey);
	$keys = $wmsigner->ExportKeys();
	$keys['base64'] = base64_encode(file_get_contents(KWMFILE));

	foreach($keys as $k => $v) {
		$path = KWMFILE.'.'.$k;
		if (file_put_contents($path, $v)) { echo("$k saved to: $path\n"); }
	}

	echo("Done.\n");

?>