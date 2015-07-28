<?php
chdir(dirname(__FILE__));
require_once '../classes/stdf.php';

$sql = "SELECT * FROM country";
$results = $DB->rows($sql);
foreach($results as $country) {
	$name = translit(strtolower($country['country_name']));
	$sql = "UPDATE country SET translit_country_name=? WHERE id=?i";
	$DB->query($sql, $name, $country['id']);
}

$sql = "SELECT * FROM city";
$results = $DB->rows($sql);
foreach($results as $city) {
	$name = translit(strtolower($city['city_name']));
	$sql = "UPDATE city SET translit_city_name=? WHERE id=?i";
	$DB->query($sql, $name, $city['id']);
}

?>