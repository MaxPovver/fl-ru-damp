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
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM freelancer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$pMessage = "
Здравствуйте!

У каждого пользователя на нашем сайте есть рейтинг, у кого-то он больше, у кого-то меньше. А зачем же он нужен? Мы ответим: чем выше ваш рейтинг, тем больше шансов найти интересный проект!

Работодатели, подыскивая исполнителей на свои проекты, ориентируются на рейтинг фрилансера и практически всегда выбирают пользователей с большим рейтингом – ведь это говорит об опыте и профессионализме кандидата. 

Рейтинг фрилансера складывается из различных параметров. К примеру, показатели будут больше, если вы будете чаще заходить на сайт, заполните http:/{свой профайл}/{$pHost}/users/%USER_LOGIN%/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating<span>,</span> добавите работы в портфолио. Рекомендуем обмениваться с заказчиками рекомендациям и по результатам сотрудничества – чем больше у вас положительных рекомендаций, тем выше рейтинг. Немаловажными факторами являются и наличие у вас аккаунта http:/{PRO}/{$pHost}/payed/?utm_source=newsleter4&utm_medium=rassilka&utm_campaign=ratingPRO<span>,</span> отклики на проекты, участие в жизни нашего портала – появление ваших работ в разделах http:/{«Статьи»}/{$pHost}/articles/?utm_source=newsleter4&utm_medium=rassilka&utm_campaign=rating<span>,</span> победа в конкурсах от работодателей и многое другое. Кроме того, вы можете покупать баллы рейтинга. Узнать обо всем более подробно можно http:/{здесь}/{$pHost}/help/?q=812&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating<span>.</span> 

http:/{Поднять себе рейтинг!}/{$pHost}/users/%USER_LOGIN%/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating

По всем возникающим вопросам вы можете обращаться в нашу http:/{службу поддержки}/$pHost/help/?all&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating<span>.</span>
Вы можете отключить уведомления на http:/{странице «Уведомления/Рассылка»}/{$pHost}/users/%USER_LOGIN%/setup/mailer/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating вашего аккаунта.

Приятной работы,
Команда Free-lance.ru";

$eSubject = "Как мериться рейтингом на Free-lance.ru";

$eMessage = "<p>Здравствуйте!</p>

<p>
У каждого пользователя на нашем сайте есть рейтинг, у кого-то он больше, у кого-то меньше. А зачем же он нужен? Мы ответим: чем выше ваш рейтинг, тем больше шансов найти интересный проект!
</p>

<p>
Работодатели, подыскивая исполнителей на свои проекты, ориентируются на рейтинг фрилансера и практически всегда выбирают пользователей с большим рейтингом – ведь это говорит об опыте и профессионализме кандидата. 
</p>

<p>
Рейтинг фрилансера складывается из различных параметров. К примеру, показатели будут больше, если вы будете чаще заходить на сайт, заполните <a href='{$GLOBALS['host']}/users/%USER_LOGIN%/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>свой профайл</a>, добавите работы в портфолио. Рекомендуем обмениваться с заказчиками рекомендациям и по результатам сотрудничества – чем больше у вас положительных рекомендаций, тем выше рейтинг. 
Немаловажными факторами являются и наличие у вас аккаунта <a href='{$GLOBALS['host']}/payed/?utm_source=newsleter4&utm_medium=rassilka&utm_campaign=rating'>PRO</a>, отклики на проекты, участие в жизни нашего портала – появление ваших работ в разделах <a href='{$GLOBALS['host']}/articles/?utm_source=newsleter4&utm_medium=rassilka&utm_campaign=rating'>«Статьи»</a>, победа в конкурсах от работодателей и многое другое. Кроме того, вы можете покупать баллы рейтинга. Узнать обо всем более подробно можно <a href='{$GLOBALS['host']}/help/?q=812&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>здесь</a>. 
</p>

<p><a href='{$GLOBALS['host']}/users/%USER_LOGIN%/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>Перейти на сайт</a> и поднять себе рейтинг!</p>

<p>
По всем возникающим вопросам вы можете обращаться в нашу <a href='{$GLOBALS['host']}/help/?all&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>службу поддержки</a>.<br/>
Вы можете отключить уведомления на <a href='{$GLOBALS['host']}/users/%USER_LOGIN%/setup/mailer/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>странице «Уведомления/Рассылка»</a> вашего аккаунта.
</p>

<p>
Приятной работы,<br/>
Команда <a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>Free-lance.ru</a>
</p>";

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