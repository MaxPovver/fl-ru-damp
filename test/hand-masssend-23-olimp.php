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

// Только фрилансерам, активированным (active = true), незабаненным (is_banned = B'0'), с включенными рассылками (substring(subscr from 8 for 1)::integer = 1)
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM freelancer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND active = true"; 

$pHost = str_replace("http://", "", $GLOBALS['host']);
if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$eHost = $GLOBALS['host'];

$pMessage = "
Здравствуйте!

Мы знаем, как нелегко иногда найти новые заказы и работодателей, особенно в летний период и в праздники. И именно поэтому вновь запускаем акцию, которая приурочена к Летним Олимпийским играм, &mdash; праздник должен быть у всех! 
   
С 27 июля по 12 августа при покупке аккаунта PRO на месяц можно {$pHttp}:/{прокатиться на карусели}/{$pHost}/olimp/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp в каталоге фрилансеров всего за 1 FM. Вы сможете найти для себя новых заказчиков, интересные и денежные проекты, не прилагая никаких усилий, &mdash; все за вас сделает реклама в карусели.

{$pHttp}:/{Вперед к золоту!}/{$pHost}/olimp/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp

По всем возникающим вопросам вы можете обращаться в нашу {$pHttp}:/{службу поддержки}/{$pHost}/help/?all&utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp<i>.</i>
Вы можете отключить уведомления на {$pHttp}:/{странице «Уведомления/Рассылка»}/{$pHost}/users/%USER_LOGIN%/setup/mailer/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp вашего аккаунта.

Приятной работы!
Команда {$pHttp}:/{Free-lance.ru}/{$pHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=olimp
";

$eSubject = "Олимпийская карусель за 1FM";

$eMessage = "<p>Здравствуйте!</p>

<p>
Мы знаем, как нелегко иногда найти новые заказы и работодателей, особенно в летний период и в праздники. И именно поэтому вновь запускаем акцию, которая приурочена к Летним Олимпийским играм, &mdash; праздник должен быть у всех!
</p>

<p>
С 27 июля по 12 августа при покупке аккаунта PRO на месяц можно <a href='{$eHost}/olimp/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp'>прокатиться на карусели</a> в каталоге фрилансеров всего за 1 FM. Вы сможете найти для себя новых заказчиков, интересные и денежные проекты, не прилагая никаких усилий, &mdash; все за вас сделает реклама в карусели.
</p>

<p>
<a href='{$eHost}/olimp/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp'>Вперед к золоту!</a>
</p>

<p>
По всем возникающим вопросам вы можете обращаться в нашу <a href='{$eHost}/help/?all&utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp' target='_blank'>службу поддержки</a>.<br/>
Вы можете отключить уведомления на <a href='{$eHost}/users/%USER_LOGIN%/setup/mailer/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp' target='_blank'>странице «Уведомления/Рассылка»</a> вашего аккаунта.
</p>

Приятной работы!<br/>
Команда <a href='{$eHost}/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp' target='_blank'>Free-lance.ru</a>";

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