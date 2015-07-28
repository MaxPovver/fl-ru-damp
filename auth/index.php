<?php

define('IS_OPAUTH', true);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opauth/OpauthHelper.php");

$config = OpauthHelper::getConfig();

$uri = '/auth/';
$param = __paramInit('string', 'param');
if ($param) {
    $uri .= $param;
}

$action = __paramInit('string', 'action');
if ($action) {
    $uri .= '/' . $action;
} else {
    OpauthHelper::setRole(__paramInit('int', 'role'));
    OpauthHelper::setMultilevel(__paramInit('int', 'multilevel'));
    OpauthHelper::saveRedirect();
}

$config['request_uri'] = $uri;

$Opauth = new Opauth($config);