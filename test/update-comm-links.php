<?php
chdir(dirname(__FILE__));
require_once('../classes/stdf.php');

$links = array();

$sql = "SELECT * FROM commune";
$communes = $DB->rows($sql);
foreach($communes as $commune) {
	$link = translit(strtolower(html_entity_decode($commune['name'], ENT_QUOTES, 'cp1251')));
	if(preg_match("/[^a-z]/",$link[0])) { $link = 'c'.$link; }
	if(in_array($link, $links)) {
		asort($links, SORT_STRING);
		$tlinks = array();
		foreach($links as $k=>$v) {
			if(preg_match("/^".$link."/", $v)) $tlinks[] = $v;
		}
		$link = ( $tlinks[0]==$link ? $tlinks[count($tlinks)-1]."1" : $link);
	}
	$links[] = $link;
	$sql = "UPDATE commune SET link = ? WHERE id = ?i";
	$DB->query($sql, $link, $commune['id']);
}
?>