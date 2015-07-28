<?php

require_once '../classes/config.php';

$memcached = new Memcached;
$memcached->addServer($GLOBALS['memcachedServers'][0], 11211);

var_dump($memcached->get('DBCacheS'));