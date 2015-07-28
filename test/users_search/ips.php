<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_search.php");

$user_search = new user_search(20);

$query = $user_search->_getIpQuery('', '5.10.186.1', '65.10.188.250');
print_r($query); 
exit;