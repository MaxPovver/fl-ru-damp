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

// Только всем, незабаненным (is_banned = B'0'), с включенными рассылками (substring(subscr from 8 for 1)::integer = 1)
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'"; 

if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$pMessage = "
Здравствуйте!

В руководстве проектом произошли важные изменения.

Василий Воропаев, сооснователь проекта и генеральный директор, принял решение об уходе из компании.
Новым руководителем проекта назначена Людмила Булавкина.
Директором по развитию стал сооснователь Free-lance.ru Антон Мажирин.
Владимир Тарханов возьмет на себя функции коммерческого директора.

Познакомиться с новым руководством, узнать о дальнейших планах развития Free-lance.ru, задать вопросы и поделиться своим мнением можно в {$pHttp}:/{&laquo;Блогах&raquo;}/{$pHost}/blogs/free-lanceru/725591/free-lanceru-izmeneniya-v-2013-godu.html?utm_source=newsletter4&utm_medium=email&utm_campaign=peremeny<span>.</span>

По всем возникающим вопросам вы можете обращаться в нашу {$pHttp}:/{службу поддержки}/{$pHost}/help/?all<i>.</i>
Вы можете отключить уведомления на {$pHttp}:/{странице &laquo;Уведомления/Рассылка&raquo;}/{$pHost}/users/%USER_LOGIN%/setup/mailer/ вашего аккаунта.

Приятной работы!
Команда {$pHttp}:/{Free-lance.ru}/{$pHost}/
";

$eSubject = "Большие перемены на Free-lance.ru";

$eMessage = "<p>Здравствуйте!</p>

<p>
В руководстве проектом произошли важные изменения.
</p>

<p>
Василий Воропаев, сооснователь проекта и генеральный директор, принял решение об уходе из компании.<br/>
Новым руководителем проекта назначена Людмила Булавкина.<br/>
Директором по развитию стал сооснователь Free-lance.ru Антон Мажирин.<br/>
Владимир Тарханов возьмет на себя функции коммерческого директора.
</p>

<p>
Познакомиться с новым руководством, узнать о дальнейших планах развития Free-lance.ru, задать вопросы и поделиться своим мнением можно в «<a href='https://www.free-lance.ru/blogs/free-lanceru/725591/free-lanceru-izmeneniya-v-2013-godu.html?utm_source=newsletter4&utm_medium=email&utm_campaign=peremeny' target='_blank'>Блогах</a>».
</p>

<p>
По всем возникающим вопросам вы можете обращаться в нашу <a href='{$eHost}/help/?all' target='_blank'>службу поддержки</a>.<br/>
Вы можете отключить уведомления на <a href='{$eHost}/users/%USER_LOGIN%/setup/mailer/' target='_blank'>странице «Уведомления/Рассылка»</a> вашего аккаунта.
</p>

Приятной работы!<br/>
Команда <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>";

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

echo "Send personal messages\n";

// подготавливаем рассылку
$msgid = $DB->val("SELECT masssend(?, ?, '{}', '')", $sender['uid'], $pMessage);
if (!$msgid) die('Failed!');

// допустим, мы получаем адресатов с какого-то запроса
$i = 0;
while ($users = $master->col("{$sql} LIMIT 30000 OFFSET ?", $i)) {
    $DB->query("SELECT masssend_bind(?, {$sender['uid']}, ?a)", $msgid, $users);
    $i = $i + 30000;
}
// Стартуем рассылку в личку
$DB->query("SELECT masssend_commit(?, ?)", $msgid, $sender['uid']); 
echo "Send email messages\n";

$mail = new smtp;
$mail->subject = $eSubject;  // заголовок письма
$mail->message = $eMessage; // текст письма
$mail->recipient = ''; // свойство 'получатель' оставляем пустым
$spamid = $mail->send('text/html');
if (!$spamid) die('Failed!');
// с этого момента рассылка создана, но еще никому не отправлена!
// допустим нам нужно получить список получателей с какого-либо запроса
$i = 0;
$mail->recipient = array();
$res = $master->query($sql);
while ($row = pg_fetch_assoc($res)) {
    if($row['email'] == '') continue;
    $mail->recipient[] = array(
        'email' => $row['email'],
        'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'USER_LOGIN' => $row['login'])
    );
    if (++$i >= 30000) {
        $mail->bind($spamid);
        $mail->recipient = array();
        $i = 0;
    }
    $cnt++;
}
if ($i) {
    $mail->bind($spamid);
    $mail->recipient = array();
}

echo "OK. Total: {$cnt} users\n";