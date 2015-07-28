<?php
//php /var/www/_beta/html/test/mail/spam-debug.php
//clear;tail -20 /var/www/_beta/html/classes/pgq/logs/spam.pgq
//cat /var/log/maillog | grep kazakov@fl.ru
//clear; tail -50 /var/log/maillog

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

$path = $_SERVER['DOCUMENT_ROOT'];

require_once($path . "/classes/config.php");
require_once($path . "/classes/smail.php");
require_once($path . "/classes/projects.php");
require_once($path . "/classes/freelancer.php");

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' ); //???
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");



//local
//$users = array(1,2);

//beta
//$users = NULL;//array(1,2);

//$mail = new smail();
//$mail->NewProj($users);
//$cnt = $mail->NewProj2($users);

//var_dump($cnt);


//print_r($GLOBALS['TESTERS_MAIL']);
//exit;


$mail = new smtp;
$mail->subject   = 'Тема письма - проверка рассылки';
$mail->message   = 'Это тело письма';
$mail->recipient = '';

$spamid = $mail->send('text/html');
if ( !$spamid ) 
{
    die("Failed!\n");
}

$mail->recipient = array();

$rows[] = array(
    'uname'     => 'Алексей',
    'usurname'  => 'Казаков',
    'email'     => 'dezinger@gmail.com'
);

$rows[] = array(
    'uname'     => 'Алексей',
    'usurname'  => 'Казаков',
    'email'     => 'kazakov@fl.ru'
);

$rows[] = array(
    'uname'     => 'Алексей',
    'usurname'  => 'Казаков',
    'email'     => 'kazakov@free-lance.ru'
);

$rows[] = array(
    'uname'     => 'Алексей',
    'usurname'  => 'Казаков',
    'email'     => 'ak_soft@list.ru'
);


$rows[] = array(
    'uname'     => 'Алексей',
    'usurname'  => 'Казаков',
    'email'     => 'ddezinger@yandex.ru'
);


/*
$rows[] = array(
    'uname'     => 'Алексей',
    'usurname'  => 'Казаков',
    'email'     => 'yabrus@mail.ru'
);
*/

foreach($rows as $row)
{
    
    $mail->recipient[] = array(
        'email' => $row['email']//,//"{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
        //'extra' => array('USER_NAME' => $row['uname'], 'USER_SURNAME' => $row['usurname'], 'USER_LOGIN' => $row['login'])
    );
    
}


$res = $mail->bind($spamid);    
var_dump($res);

exit;