<?php
ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/functions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_categories.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_tags.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices.php");


//$_POST['videos'] = array('video1','video2');


$_POST = array(
    'title' => "ѕоч<ему? парал'лельно углово\"е рассто€ние<b>hello</b>"
);


$tservice = new tservices(2);

$errors = tu_validation($tservice);

var_dump($errors);

var_dump($tservice->title);


//var_dump(strtolower(htmlspecialchars_decode( $tservice->title , ENT_QUOTES )));

//var_dump(translit(strtolower(htmlspecialchars_decode( $tservice->title , ENT_QUOTES ))));

//var_dump(translit(strtolower( $tservice->title )));

exit;