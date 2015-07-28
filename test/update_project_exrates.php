<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
project_exrates::updateCBRates();
echo 'OK';
?>