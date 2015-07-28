<?php

require_once '../classes/stdf.php';

$d = dir($abs_path.'/sitemap');
if($d) {
	while(false!==($name=$d->read())) {
		if($name!='.' && $name!='..') {
			$f1 = fopen($abs_path.'/sitemap/'.$name, 'r');
			$f2 = fopen($abs_path.'/flru/sitemap/'.$name, 'w');
			while(!feof($f1)) {
				$s = stream_get_line( $f1, 4096, '</loc>' );
				$s1 = str_replace('https://www.free-lance.ru/', 'https://www.fl.ru/', $s);
				if($s!=$s1) {
					$s1 = $s1.'</loc>';
				}
				fwrite($f2, $s1);
			}
			fclose($f1);
			fclose($f2);
		}
	}
	$d->close();
}

$f1 = fopen($abs_path.'/sitemap.xml', 'r');
$f2 = fopen($abs_path.'/flru/sitemap.xml', 'w');
while(!feof($f1)) {
	$s = stream_get_line( $f1, 4096, '</loc>' );
	$s1 = str_replace('https://www.free-lance.ru/', 'https://www.fl.ru/', $s);
	if($s!=$s1) {
		$s1 = $s1.'</loc>';
	}
	fwrite($f2, $s1);
}
fclose($f1);
fclose($f2);


?>