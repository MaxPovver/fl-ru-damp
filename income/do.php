<?php
/*
<form method="post" action="http://www.free-lance.ru/income/do.php?src=2" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="test" value="11111" />
    <input type="hidden" name="test2" value="22222" />
    <input value="sss" type="submit"/>
</form>

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://www.free-lance.ru/income/do.php?src=2');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, array('test1' => '111', 'test2' => '2222'));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$res = curl_exec($ch);

var_dump($res);
 * 
 * 
 */

define('NO_CSRF', 1);
$request = $_POST;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/onlinedengi.php");

$src = __paramInit('int', 'src');
$inst = onlinedengi::init($src, 1, $request);
$resp = $inst->handleRequest();
echo $resp;
exit();