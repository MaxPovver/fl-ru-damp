<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/contacts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sitemap.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stats.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/hh.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banners.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/maintenance.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search_parser.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/spam.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users_suspicious_contacts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/firstpage.php");

if ($_GET["bs"] == 1) {
   $_GET["debug"] = 1;
   $mail = new smail();
   $h  = 24;
   if ( $_GET['h'] == 72) $h = 72; 
   $mail->sendSbrReserveNotice( $h );    
   exit;
}


if ($_GET["activate"] == 1) {
   $_GET["debug"] = 1;
   $mail = new smail();
   $mail->activateAccountNotice();    
   exit;
}


$master = new DB("master");
$master->query("UPDATE users SET last_time = last_time - '24 hours' :: interval WHERE email IN ('lamzin80@mail.ru', 'jusoft@yandex.ru', 'lamzin.a.n@rambler.ru');");
//$_GET["debug"] = 1;
$mail = new smail();
$mail2 = new smail2();
$spam = new spam();

$mail2->NewProjForMissingMoreThan24h($users); die;