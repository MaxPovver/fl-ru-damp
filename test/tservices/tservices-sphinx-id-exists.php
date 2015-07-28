<?php

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);


//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' ); //???
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/sphinxapi.php");


/*
$user_obj = new users();
var_dump($user_obj->CountAll());
*/

//http://beta.free-lance.lo/tu/1124752/empiricheskiy-grafik-funktsii-predposyilki-i-razvitie.html

$time_start = microtime(true);


$sphinxClient = new SphinxClient;
$sphinxClient->SetServer(SEARCHHOST, SEARCHPORT);
$sphinxClient->SetIDRange(1124752,1124752);
$res = $sphinxClient->Query("","tservices;delta_tservices");



/*
$sphinxClient = new SphinxClient;
$sphinxClient->SetServer(SEARCHHOST, SEARCHPORT);
$sphinxClient->setFilter('created_order', array(99999)); // это поле - алиас для ID чтобы сортировать можно было как в задаче требуется
$res = $sphinxClient->query('', "tservices;delta_tservices");
*/


$time_end = microtime(true);
$time = $time_end - $time_start;



print_r($res['matches'][1124752]);

var_dump($time);

/*
 * $sphinx->SetIDRange(12345,12345);
$res = $sphinx->Query("","myindex");
if (count($res['matches']) == 1) {
      print "it's there!";
}
 */

/*
 * $time_start = microtime(true);

// Спим некоторое время
usleep(100);

$time_end = microtime(true);
$time = $time_end - $time_start;
 */


exit;