<?php

//exit;

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smtp.php");


$uids = array(
956091,
916026,
1025832,
1677710,
2149160,
307071,
922949    
/*    
1646282,
 1693821,
 170380,
 2144015,
 916848,
 964972,
 1114938,
 1230112,
 916848,
 964972,
1095422,
 888695, 
1095422,
 1114938,
 1230112,
 143826,
 1646282,
 170380,
 2144015,
 888695    
 */   
 /*   
 1632535,
 1115704,
 1068594   
    */
    //703979
    /*
 2227143,
 2227143,
 1539580,
 1379363,
 1312164,
 969336,
 1960293,
 464591,
 841606,
 464591,
 475569,
 429100,
 1362822,
 1036317,
 1153941,
 981011,
 417249,
 981011,
 952306,
 1470841,
 1583665,
 994636,
 669391,
 428538,
 458182,
 83877,
 1557752,
 681479,
 375437,
 795551,
 1101082,
 221785,
 284203,
 777559,
 777559,
 858622,
 1285082,
 962994,
 1889691,
 863098,
 1661602,
 1454279,
 1454279,
 796099,
 413574,
 1020544,
 1090604,
 971447,
 1047584,
 1308404,
 23989,
 649084,
 649084,
 1278434,
 2167233,
 799480,
 1090604,
 1090604,
 150642,
 1047584,
 1666312,
 1047584,
 1270791*/
);


$subject = 'Подозрительная активность на вашем аккаунте FL.ru';

$message = '
Здравствуйте.
<br/><br/>
На вашем аккаунте была замечена подозрительная активность, вам был сброшен пароль. <br/>
Зайдите, пожалуйста, на страницу <a href="https://www.fl.ru/remind/">восстановления пароля</a> и запросите ссылку для изменения пароля.
<br/><br/>
Также, для повышения безопасности вашего аккаунта, привяжите свой аккаунт к соцсети <br/>
и воспользуйтесь двухэтапной аутентификацией - http://feedback.fl.ru/topic/683170-dvuhetapnaya-autentifikatsiya-cherez-sotsseti/
<br/><br/>
С уважением, <br/>
команда <a href="https://www.fl.ru">FL.ru</a>
';

//------------------------------------------------------------------------------

$mail = new smtp;
$mail->subject   = $subject;
$mail->message   = $message;
$mail->recipient = '';
$spamid = $mail->send('text/html');
if ( !$spamid ) {
    die("Failed!\n");
}

$mail->recipient = array();

$users = $DB->rows("
    SELECT DISTINCT uid, uname, usurname, login, email 
    FROM users WHERE uid IN(?l)   
", $uids);

if (!$users) {
    die("Users not found.");
}

foreach ($users as $user) {
    $mail->recipient[] = array(
        'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
        'extra' => array(
            'USER_NAME'         => $user['uname'],
            'USER_SURNAME'      => $user['usurname'],
            'USER_LOGIN'        => $user['login']
        )
    );    
}

$mail->bind($spamid, true);
$cnt = count($users);
echo "OK. Total: {$cnt} users\n";