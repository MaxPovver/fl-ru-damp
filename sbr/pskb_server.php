<?php
define('NOCSRF', true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pskb.php");

$method = __paramInit('string', 'method');

$lc = new pskb_server();
$lc->serve($method, $_REQUEST);

exit();