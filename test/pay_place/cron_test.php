<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");

$payPlace = new pay_place();

print_r($payPlace->getUserHistory());

?>