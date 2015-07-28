<?php

ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';


/**
 * Логин пользователя от кого осуществляется рассылка
 * 
 */
$sender = 'admin';

// Только фрилансерам
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND safety_phone LIKE '+77%'";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = '';

$eSubject = "Отключение привязки аккаунта к мобильному телефону на Free-lance.ru";

$eMessage = "<p>Здравствуйте!</p>

<p>
В связи с тем, что операторы сотовой связи Казахстана отключили ряд коротких номеров, мы вынуждены приостановить действие услуги «Восстанавливать пароль только на телефон». Вам доступно восстановление пароля только с использованием электронной почты. Приносим свои извинения за возможные неудобства.
</p>

<p>
Подробная инструкция по восстановлению пароля находится в <a href='{$eHost}/help/?q=807' target='_blank'>соответствующем разделе помощи</a>.
</p>

<p>
По всем возникающим вопросам вы можете обращаться в нашу <a href='{$eHost}/help/?all' target='_blank'>службу поддержки</a>.<br/>
Вы можете отключить уведомления на <a href='{$eHost}/users/%USER_LOGIN%/setup/mailer/' target='_blank'>странице «Уведомления/Рассылка»</a> вашего аккаунта.
</p>

Приятной работы!<br/>
Команда <a href='{$eHost}' target='_blank'>Free-lance.ru</a>";


// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('plproxy');
$master = new DB('master');
$cnt = 0;


$sender = $master->row("SELECT * FROM users WHERE login = ?", $sender);
if (empty($sender)) {
    die("Unknown Sender\n");
}


if ( $pMessage != '' ) {
    echo "Send personal messages\n";
    $msgid = $DB->val("SELECT masssend(?, ?, '{}', '')", $sender['uid'], $pMessage);
    if (!$msgid) {
        die("Failed!\n");
    }
    $i = 0;
    while ( $users = $master->col("{$sql} LIMIT 30000 OFFSET ?", $i) ) {
        $DB->query("SELECT masssend_bind(?, {$sender['uid']}, ?a)", $msgid, $users);
        $i = $i + 30000;
        echo "{$i} users\n";
    }
    $DB->query("SELECT masssend_commit(?, ?)", $msgid, $sender['uid']); 
    echo "Send email messages\n";
}


if ( $eMessage != '' ) {
    $mail = new smtp;
    $mail->subject   = $eSubject;
    $mail->message   = $eMessage;
    $mail->recipient = '';
    $spamid = $mail->send('text/html');
    if ( !$spamid ) {
        die("Failed!\n");
    }

    $i = 0;
    $c = 0;
    $mail->recipient = array();
    $res = $master->query($sql);
    while ($row = pg_fetch_assoc($res)) {
        $mail->recipient[] = array(
            'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
            'extra' => array('USER_NAME' => $row['uname'], 'USER_SURNAME' => $row['usurname'], 'USER_LOGIN' => $row['login'])
        );
        if (++$i >= 30000) {
            $mail->bind($spamid);
            $mail->recipient = array();
            $i = 0;
            echo "{$c} users\n";
        }
        $c++;
    }
    if ($i) {
        $mail->bind($spamid);
        $mail->recipient = array();
    }
}

echo "OK. Total: {$c} users\n";