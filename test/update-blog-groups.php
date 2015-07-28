<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/*
$sql = "SELECT * FROM blogs_groups";
$groups = $DB->rows($sql);
foreach($groups as $group) {
	$sql = "UPDATE blogs_groups SET link = ? WHERE id = ?i";
	$DB->query($sql, translit(strtolower($group['t_name'])), $group['id']);
}
*/

/*
$sql = "SELECT * FROM commune_groups";
$groups = $DB->rows($sql);
foreach($groups as $group) {
	$sql = "UPDATE commune_groups SET link = ? WHERE id = ?i";
	$DB->query($sql, translit(strtolower($group['name'])), $group['id']);
}
*/

$sql = "SELECT * FROM commune";
$communes = $DB->rows($sql);
foreach($communes as $commune) {
	$sql = "UPDATE commune SET link = ? WHERE id = ?i";
	$DB->query($sql, translit(strtolower($commune['name'])), $commune['id']);
}

?>