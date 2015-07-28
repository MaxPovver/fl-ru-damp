<?
require_once("../classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");


$a = 'ya.ru www.ya.ru http://ya.ru <br> www.ya.ru ya.ru http://ya.ru <br> http://ya.ru ya.ru www.ya.ru';
echo reformat($a);

?>
