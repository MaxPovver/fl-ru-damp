<?php
chdir(dirname(__FILE__));
require_once ('../classes/config.php');
require_once ('../classes/log.php');

$value = "<h1></h1>";
$value = preg_replace("/\h/", " ", $value);
echo htmlspecialchars($value);
//$log = new log('test-test.log');
//$log->writeln(date('c'));
