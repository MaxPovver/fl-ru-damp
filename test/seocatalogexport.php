<?php
chdir(dirname(__FILE__));
require_once '../classes/stdf.php';
require_once '../classes/seo.php';

$seo = new seo();

$directions = $seo->getDirections($direct_id);
$res   = $seo->getSections(true, $direct_id);
$sections = array();
if($res) {
    foreach ($res as $row) {
        $sections[$row['direct_id']][] = $row;
    }
}

$subdomains = $seo->getSubdomains(true);

foreach($subdomains as $subdomain) {
	$f = fopen("./seo-data-{$subdomain['subdomain']}.csv", "w");
	foreach($directions as $direction) {
		if($direction['id']!=2 && $direction['id']!=19 && $direction['id']!=18 && $direction['id']!=20 && $direction['id']!=21 && $direction['id']!=11 && $direction['id']!=1) {
			fwrite($f, '"-'.$direction['dir_name']."\"\n");
			if($sections[$direction['id']]) {
				foreach($sections[$direction['id']] as $key=>$section) { 
					fwrite($f, '"--'.$section['name_section']."\"\n");
					if($section['subsection']) {
						foreach($section['subsection'] as $i=>$subsection) {
							if($subsection['subdomain_id']==$subdomain['id']) {
								fwrite($f, '"---'.$subsection['name_section']."\"\n");
							}
						}
					}
				}
			}
			fwrite($f, ';'."\n");
		}
	}
	fclose($f);
}


?>