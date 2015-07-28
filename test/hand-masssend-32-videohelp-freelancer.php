<?php
/**
 * Уведомление работодателям
 * */
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';


/**
 * Логин пользователя от кого осуществляется рассылка
 * 
 */
//$sender = 'jb_admin';
$sender = 'admin';

$sql = "
SELECT f.uid, f.email, f.login, f.uname, f.usurname, f.subscr FROM freelancer f
INNER JOIN (
SELECT uid FROM freelancer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'
EXCEPT
SELECT frl_id FROM sbr
ORDER BY uid ASC) as u ON u.uid = f.uid";

//$sql = "SELECT uid, email, login, uname, usurname FROM users WHERE email in ('jb@x13-net.ru', 'jb.x13@mail.ru', 'stoiss@yandex.ru')"; 


$pHost = str_replace(array("http://", "https://"), "", $GLOBALS['host']);
if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$eHost = $GLOBALS['host'];

$pMessage = "
Здравствуйте!

У нас появились видеоинструкции по «{$pHttp}:/{Сделке Без Риска}/{$pHost}/sbr/?utm_source=newsletter4&utm_medium=email&utm_campaign=video_free<i>»</i>. В разделе «{$pHttp}:/{Помощь}/{$pHost}/help/?c=41&utm_source=newsletter4&utm_medium=email&utm_campaign=video_free<i>»</i> находятся видеоролики, в которых просто и доступно разъясняются основные моменты сотрудничества через сервис.

Как начинается работа по проекту? Что происходит на каждом этапе сделки? Как завершить «Сделку Без Риска»? Ответы на эти и многие другие вопросы вы можете найти в новом разделе нашего сайта.

Мы покажем вам, как работать безопасно!

По всем возникающим вопросам вы можете обращаться в нашу {$pHttp}:/{службу поддержки}/{$pHost}/help/?all<i>.</i>
Вы можете отключить уведомления на {$pHttp}:/{странице &laquo;Уведомления/Рассылка&raquo;}/{$pHost}/users/%USER_LOGIN%/setup/mailer/<span> вашего</span> аккаунта.

Приятной работы,
Команда {$pHttp}:/{Free-lance.ru}/{$pHost}/";


$eSubject = "Free-lance.ru: видеоинструкции по «Сделке Без Риска»";

$eMessage = "<p>Здравствуйте!</p>
<p>У нас появились видеоинструкции по «<a href='{$eHost}/sbr/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=rating_freelancer' target='_blank'>Сделке Без Риска</a>». В разделе «<a href='{$eHost}/help/?c=41&utm_source=newsletter4&utm_medium=email&utm_campaign=video_free' target='_blank'>Помощь</a>» находятся видеоролики, в которых просто и доступно разъясняются основные моменты сотрудничества через сервис.</p>
<p>Как начинается работа по проекту? Что происходит на каждом этапе сделки? Как завершить «Сделку Без Риска»? Ответы на эти и многие другие вопросы вы можете найти в новом разделе нашего сайта.</p>
<p>Мы покажем вам, как работать безопасно!</p>
<p>По всем возникающим вопросам вы можете обращаться в нашу <a href=\"{$eHost}/help/?all\" target=\"_blank\">службу поддержки</a>.</p>
<p>Вы можете отключить уведомления на <a href=\"{$eHost}/users/%USER_LOGIN%/setup/mailer/\" target=\"_blank\">странице «Уведомления/Рассылка»</a> вашего аккаунта.</p>
<br/>
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