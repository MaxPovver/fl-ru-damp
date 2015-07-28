<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
header('Content-type: text/xml; charset=utf-8');
header('Content-disposition: inline; filename=webprof.xml');
header('HTTP/1.1 301 Moved Permanently');
header('Location: ' . WDCPREFIX . '/upload/webprof.xml');
