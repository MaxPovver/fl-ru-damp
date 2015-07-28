<?php

define('IS_PHP_JS', true);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search_parser.php");

$type = __paramInit('string', 'type');

if (!$type || $type == 'users') {
    //header('Content-Type: text/javascript; charset=windows-1251');
    //header("Cache-Control: public, must-revalidate, max-age=0");
   // header("Etag: {$kdata['etag']}");
    return;
}

$parser = search_parser::factory();
$kdata = $parser->getAsJS($type, 1000);
//header('Content-Type: text/javascript; charset=windows-1251');
//header("Cache-Control: public, must-revalidate, max-age=0");
//header("Etag: {$kdata['etag']}");
//if($_SERVER['HTTP_IF_NONE_MATCH']==$kdata['etag']) {
//    header("HTTP/1.1 304 Not Modified");
//	exit;
//}

print($kdata['js']);
